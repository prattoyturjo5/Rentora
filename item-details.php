<?php
session_start();
require_once(__DIR__ . '/config/db.php');
require_once(__DIR__ . '/includes/auth_guard.php');

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$error = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'self_rent_forbidden') {
        $error = "You cannot rent your own equipment listing.";
    } elseif ($_GET['error'] === 'dates_taken') {
        $error = "This equipment is already booked or rented for the selected dates. Please choose different dates.";
    } else {
        $error = htmlspecialchars($_GET['error']);
    }
}

// Handle Rental Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    if (!is_member()) {
        header("Location: auth/login.php?msg=login_required");
        exit();
    }

    $renter_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
    $member_status = get_member_status($pdo, $renter_id);

    if ($member_status !== 'Verified') {
        $error = ($member_status === 'Rejected') 
            ? "Your account was rejected, contact an admin." 
            : "Your account is pending verification. Equipment rental requests are disabled until verified.";
    } else {
        $req_item_id = (int)$_POST['item_id'];
        $start_date = trim($_POST['rental_start_date'] ?? date('Y-m-d'));
        $end_date = trim($_POST['rental_end_date'] ?? date('Y-m-d', strtotime('+1 day')));

        // Retrieve equipment details
        $eqStmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = :id LIMIT 1");
        $eqStmt->execute(['id' => $req_item_id]);
        $eqItem = $eqStmt->fetch();

        if ($eqItem) {
            // Guard 1: Self-renting prevention
            if ((int)$eqItem['owner_id'] === $renter_id) {
                header("Location: item-details.php?id=" . $req_item_id . "&error=self_rent_forbidden");
                exit();
            }

            // Guard 2: Date Overlap / Collision Prevention
            $collision_check = mysqli_query($conn, "
                SELECT rental_id 
                FROM rental_agreement 
                WHERE equipment_id = '$req_item_id' 
                  AND status IN ('Approved', 'Active', 'Pending') 
                  AND ('$start_date' <= expected_end_date AND '$end_date' >= start_date)
                LIMIT 1
            ");

            if ($collision_check && mysqli_num_rows($collision_check) > 0) {
                header("Location: item-details.php?id=" . $req_item_id . "&error=dates_taken");
                exit();
            }

            $start_ts = strtotime($start_date);
            $end_ts = strtotime($end_date);
            $diff_days = max(1, (int)round(($end_ts - $start_ts) / 86400));

            $total_cost = $diff_days * floatval($eqItem['rental_rate'] ?? 0);
            $deposit_amount = floatval($eqItem['security_deposit'] ?? 0);
            $pickup_spot = trim($_POST['pickup_spot'] ?? $eqItem['campus_spot'] ?? 'Hazari Lane');
            $handover_token = 'TRX-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            try {
                $ins = $pdo->prepare("
                    INSERT INTO rental_agreement (equipment_id, renter_id, start_date, expected_end_date, total_cost, deposit_amount, handover_token, pickup_spot, fine, status)
                    VALUES (:eq_id, :renter_id, :start_date, :end_date, :total_cost, :deposit_amount, :handover_token, :pickup_spot, 0.00, 'Pending')
                ");
                $ins->execute([
                    'eq_id'          => $req_item_id,
                    'renter_id'      => $renter_id,
                    'start_date'     => $start_date,
                    'end_date'       => $end_date,
                    'total_cost'     => $total_cost,
                    'deposit_amount' => $deposit_amount,
                    'handover_token' => $handover_token,
                    'pickup_spot'    => $pickup_spot
                ]);

                header("Location: user/dashboard.php?msg=requested&token=" . urlencode($handover_token));
                exit();
            } catch (PDOException $e) {
                $error = "Booking error: " . htmlspecialchars($e->getMessage());
            }
        } else {
            $error = "Item not found.";
        }
    }
}

// Fetch Item Data
$item = null;
$item_stats = ['total_rentals' => 0, 'active_bookings' => 0];
try {
    $stmt = $pdo->prepare("
        SELECT e.*, e.equipment_name AS title, e.rental_rate AS daily_rate, 
               e.condition_status AS item_condition,
               c.category_name, 
               CONCAT(m.first_name, ' ', m.last_name) AS owner_name, 
               m.username AS owner_student_id, 
               m.phone_number AS owner_phone,
               m.university_email AS owner_email,
               m.status AS owner_status,
               COALESCE(NULLIF(e.campus_spot, ''), m.campus_address) AS campus_spot
        FROM equipment e 
        LEFT JOIN category c ON e.category_id = c.category_id
        LEFT JOIN member m ON e.owner_id = m.member_id 
        WHERE e.equipment_id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $item_id]);
    $item = $stmt->fetch();

    if ($item) {
        $statsStmt = $pdo->prepare("
            SELECT COUNT(*) AS total_rentals,
                   COUNT(CASE WHEN status IN ('Active', 'Pending') THEN 1 END) AS active_bookings
            FROM rental_agreement 
            WHERE equipment_id = :id
        ");
        $statsStmt->execute(['id' => $item_id]);
        $item_stats = $statsStmt->fetch() ?: $item_stats;
    }
} catch (Exception $e) {
    $item = null;
}

if (!$item) {
    header("Location: index.php");
    exit();
}

$base_path = '.';
$raw_img = $item['image_url'] ?? '';
if (!empty($raw_img)) {
    if (strpos($raw_img, 'http://') === 0 || strpos($raw_img, 'https://') === 0) {
        $image_url = $raw_img;
    } else {
        $image_url = $base_path . '/' . ltrim($raw_img, '/');
    }
} else {
    $image_url = 'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80';
}
$page_title = ($item['title'] ?? 'Equipment') . ' - Rentora';
require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500 mb-6 gap-2 items-center">
      <a href="index.php" class="hover:text-primary-600">Marketplace</a>
      <span>/</span>
      <span><?php echo htmlspecialchars($item['category_name'] ?? 'Equipment'); ?></span>
      <span>/</span>
      <span class="text-navy-900 font-semibold truncate"><?php echo htmlspecialchars($item['title'] ?? ''); ?></span>
    </nav>

    <?php if (!empty($error)): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      
      <!-- Left 7 Cols: Image & Description -->
      <div class="lg:col-span-7 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
          <div class="h-96 w-full bg-slate-100 relative">
            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" class="w-full h-full object-cover">
            <div class="absolute top-4 left-4 flex gap-2">
              <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500 text-white shadow-md">
                <?php echo htmlspecialchars($item['item_condition'] ?? 'Good'); ?>
              </span>
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-navy-900/80 text-white backdrop-blur-sm">
                <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>
              </span>
            </div>
          </div>

          <div class="p-6">
            <h1 class="text-2xl font-extrabold text-navy-900"><?php echo htmlspecialchars($item['title'] ?? ''); ?></h1>
            
            <?php
            // Zero Schema Change: Calculate aggregate rentals dynamically from rental_agreement
            $item_eq_id = intval($_GET['id'] ?? $item['equipment_id'] ?? 0);
            $analytics_res = mysqli_query($conn, "
                SELECT 
                    COUNT(rental_id) AS total_rent_count,
                    COALESCE(SUM(DATEDIFF(expected_end_date, start_date)), 0) AS total_rented_days
                FROM rental_agreement 
                WHERE equipment_id = '$item_eq_id' 
                  AND status IN ('Active', 'Completed')
            ");
            $analytics = ($analytics_res) ? mysqli_fetch_assoc($analytics_res) : [];
            $total_rent_count = intval($analytics['total_rent_count'] ?? 0);
            $total_rented_days = intval($analytics['total_rented_days'] ?? 0);
            ?>

            <div class="flex flex-wrap items-center gap-2.5 my-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Rented: <?php echo $total_rent_count; ?> <?php echo ($total_rent_count === 1) ? 'time' : 'times'; ?>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Total Duration: <?php echo $total_rented_days; ?> days
                </span>
            </div>

            <p class="text-xs text-slate-500 mt-1">Campus Handover Spot: <strong class="text-slate-700"><?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus Spot'); ?></strong></p>

            <div class="mt-6 pt-6 border-t border-slate-100">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Item Description & Specifications</h3>
              <p class="text-sm text-slate-600 leading-relaxed">
                <?php echo nl2br(htmlspecialchars($item['description'] ?? 'No extra description provided by owner.')); ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Lender Profile Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                <?php echo strtoupper(substr($item['owner_name'] ?? 'L', 0, 1)); ?>
              </div>
              <div>
                <h4 class="text-sm font-bold text-navy-900"><?php echo htmlspecialchars($item['owner_name'] ?? 'Lender'); ?></h4>
                <p class="text-xs text-slate-500">Student ID / Roll: <?php echo htmlspecialchars($item['owner_student_id'] ?? 'Student'); ?></p>
              </div>
            </div>
            <?php if (($item['owner_status'] ?? '') === 'Verified'): ?>
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Verified Peer</span>
            <?php elseif (($item['owner_status'] ?? '') === 'Rejected'): ?>
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">Unverified</span>
            <?php else: ?>
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Verification Pending</span>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs">
            <div class="flex items-center gap-2 text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
              <svg class="w-4 h-4 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              <span class="font-medium"><?php echo htmlspecialchars(!empty($item['owner_phone']) ? $item['owner_phone'] : 'Phone on Booking'); ?></span>
            </div>
            <div class="flex items-center gap-2 text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 truncate">
              <svg class="w-4 h-4 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span class="font-medium truncate"><?php echo htmlspecialchars(!empty($item['owner_email']) ? $item['owner_email'] : 'Email on Booking'); ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 5 Cols: Rental Calculation & Booking Form -->
      <div class="lg:col-span-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden sticky top-24">
          
          <div class="bg-navy-900 p-6 text-white">
            <div class="flex justify-between items-baseline">
              <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-bold">Daily Rent</span>
                <div class="text-3xl font-extrabold text-white">৳<?php echo number_format($item['daily_rate'] ?? 0, 2); ?></div>
              </div>
              <div class="text-right">
                <span class="text-xs text-slate-400 block font-bold">Security Deposit</span>
                <span class="text-xl font-bold text-emerald-400">৳<?php echo number_format($item['security_deposit'] ?? 0, 2); ?></span>
                <span class="text-[10px] text-slate-400 block">(100% Refundable)</span>
              </div>
            </div>
          </div>

          <?php 
            $current_user_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
            $is_owner = (is_member() && $current_user_id > 0 && $current_user_id === (int)($item['owner_id'] ?? 0));
          ?>

          <?php if ($is_owner): ?>
            <div class="p-6">
              <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-center">
                  <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center mx-auto mb-3">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <h4 class="text-sm font-bold text-slate-800">You Own This Equipment</h4>
                  <p class="text-xs text-slate-600 mt-1 mb-4">You cannot rent an item you have listed. You can manage or unlist this gear from your dashboard.</p>
                  <a href="user/equipment.php" class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition shadow-sm">
                      Manage in My Equipment &rarr;
                  </a>
              </div>
            </div>
          <?php else: ?>
            <form action="item-details.php?id=<?php echo $item_id; ?>" method="POST" id="rentalForm" class="p-6 space-y-4">
              <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label for="pickup_date" class="block text-xs font-bold text-slate-700 mb-1">Pickup Date *</label>
                  <input type="date" id="pickup_date" name="rental_start_date" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
                </div>
                <div>
                  <label for="return_date" class="block text-xs font-bold text-slate-700 mb-1">Return Date *</label>
                  <input type="date" id="return_date" name="rental_end_date" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label for="pickup_spot" class="block text-xs font-bold text-slate-700 mb-1">Campus Spot *</label>
                  <select id="pickup_spot" name="pickup_spot" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
                    <option value="Hazari Lane">Hazari Lane</option>
                    <option value="Wasa">Wasa</option>
                    <option value="GEC Campus">GEC Campus</option>
                  </select>
                </div>
                <div>
                  <label for="pickup_time" class="block text-xs font-bold text-slate-700 mb-1">Pickup Time</label>
                  <input type="time" id="pickup_time" name="pickup_time" value="10:00" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
                </div>
              </div>

              <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2" id="price_breakdown_card" data-daily-rate="<?php echo htmlspecialchars($item['daily_rate'] ?? 0); ?>" data-deposit="<?php echo htmlspecialchars($item['security_deposit'] ?? 0); ?>">
                <div class="flex justify-between text-slate-600">
                  <span id="rental_rate_label">Rent (1 day × ৳<?php echo number_format($item['daily_rate'] ?? 0, 2); ?>):</span>
                  <span class="font-bold text-slate-800" id="rental_total_display">৳<?php echo number_format($item['daily_rate'] ?? 0, 2); ?></span>
                </div>
                <div class="flex justify-between text-slate-600">
                  <span>Refundable Deposit:</span>
                  <span class="font-bold text-emerald-600" id="deposit_total_display">৳<?php echo number_format($item['security_deposit'] ?? 0, 2); ?></span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-navy-900">
                  <span>Estimated Total:</span>
                  <span id="grand_total_display">৳<?php echo number_format(($item['daily_rate'] ?? 0) + ($item['security_deposit'] ?? 0), 2); ?></span>
                </div>
                <div id="date_validation_msg" class="hidden text-[11px] text-amber-600 font-medium pt-1"></div>
              </div>

              <?php 
                $viewer_member_status = 'Pending';
                if (is_member()) {
                    $viewer_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
                    $viewer_member_status = get_member_status($pdo, $viewer_id);
                }
              ?>

              <?php if (is_member()): ?>
                <?php if ($viewer_member_status === 'Verified'): ?>
                  <button type="submit" name="submit_request" class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                    <span>Request Equipment Rental</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                  </button>
                <?php else: ?>
                  <button type="button" disabled class="w-full py-3.5 px-4 bg-slate-200 text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Verification Required to Rent</span>
                  </button>
                  <p class="text-[11px] text-center text-amber-600 font-medium mt-1">
                    <?php echo ($viewer_member_status === 'Rejected') ? 'Your account was rejected. Please contact an admin.' : 'Your account is pending verification. Rental requests will unlock once approved.'; ?>
                  </p>
                <?php endif; ?>
              <?php else: ?>
                <a href="auth/login.php?msg=login_required" class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider text-center block shadow-lg shadow-blue-600/30">
                  Sign In as Member to Rent
                </a>
              <?php endif; ?>

              <p class="text-[11px] text-center text-slate-400">
                🔒 Handover token is required for physical exchange. Safe in-person handover with a refundable security deposit.
              </p>
            </form>
          <?php endif; ?>

        </div>
      </div>

    </div>

  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const pickupInput = document.getElementById('pickup_date') || document.getElementById('rental_start_date');
    const returnInput = document.getElementById('return_date') || document.getElementById('rental_end_date');
    const breakdownCard = document.getElementById('price_breakdown_card');
    const rateLabel = document.getElementById('rental_rate_label');
    const rentTotalDisplay = document.getElementById('rental_total_display');
    const depositTotalDisplay = document.getElementById('deposit_total_display');
    const grandTotalDisplay = document.getElementById('grand_total_display');
    const validationMsg = document.getElementById('date_validation_msg');

    if (!pickupInput || !returnInput) return;

    const dailyRate = parseFloat(breakdownCard ? breakdownCard.dataset.dailyRate : <?php echo json_encode((float)($item['daily_rate'] ?? 0)); ?>) || 0;
    const securityDeposit = parseFloat(breakdownCard ? breakdownCard.dataset.deposit : <?php echo json_encode((float)($item['security_deposit'] ?? 0)); ?>) || 0;

    // Set today's date formatted as YYYY-MM-DD
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const todayStr = `${year}-${month}-${day}`;

    pickupInput.min = todayStr;
    if (!pickupInput.value) {
      pickupInput.value = todayStr;
    }

    // Default return date (tomorrow or 1 day after pickup)
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tYear = tomorrow.getFullYear();
    const tMonth = String(tomorrow.getMonth() + 1).padStart(2, '0');
    const tDay = String(tomorrow.getDate()).padStart(2, '0');
    const tomorrowStr = `${tYear}-${tMonth}-${tDay}`;

    if (!returnInput.value) {
      returnInput.value = tomorrowStr;
    }

    function updatePricing() {
      if (pickupInput.value) {
        returnInput.min = pickupInput.value;
      }

      if (!pickupInput.value || !returnInput.value) {
        return;
      }

      const start = new Date(pickupInput.value + 'T00:00:00');
      const end = new Date(returnInput.value + 'T00:00:00');

      // Calculate difference in whole days
      const diffTime = end.getTime() - start.getTime();
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffTime < 0) {
        if (validationMsg) {
          validationMsg.textContent = '⚠️ Return date must be after pickup date.';
          validationMsg.classList.remove('hidden');
        }
      } else {
        if (validationMsg) {
          validationMsg.textContent = '';
          validationMsg.classList.add('hidden');
        }
      }

      const rentalDays = Math.max(1, isNaN(diffDays) || diffDays <= 0 ? 1 : diffDays);
      const totalRent = rentalDays * dailyRate;
      const grandTotal = totalRent + securityDeposit;

      const dayText = rentalDays === 1 ? '1 day' : `${rentalDays} days`;
      if (rateLabel) {
        rateLabel.textContent = `Rent (${dayText} × ৳${dailyRate.toFixed(2)}):`;
      }
      if (rentTotalDisplay) {
        rentTotalDisplay.textContent = `৳${totalRent.toFixed(2)}`;
      }
      if (depositTotalDisplay) {
        depositTotalDisplay.textContent = `৳${securityDeposit.toFixed(2)}`;
      }
      if (grandTotalDisplay) {
        grandTotalDisplay.textContent = `৳${grandTotal.toFixed(2)}`;
      }
    }

    ['change', 'input'].forEach(evt => {
      pickupInput.addEventListener(evt, function() {
        if (returnInput.value && new Date(returnInput.value) < new Date(pickupInput.value)) {
          returnInput.value = pickupInput.value;
        }
        returnInput.min = pickupInput.value;
        updatePricing();
      });
      returnInput.addEventListener(evt, updatePricing);
    });

    // Initial calculation on page load
    updatePricing();
  });
  </script>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
