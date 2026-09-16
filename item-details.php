<?php
session_start();
require_once(__DIR__ . '/config/db.php');
require_once(__DIR__ . '/includes/auth_guard.php');

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$error = "";

// Handle Rental Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    if (!is_member()) {
        header("Location: auth/login.php?msg=login_required");
        exit();
    }

    $req_item_id = (int)$_POST['item_id'];
    $renter_id = $_SESSION['user_id'];
    $start_date = trim($_POST['rental_start_date'] ?? '');
    $end_date = trim($_POST['rental_end_date'] ?? '');
    $pickup_spot = trim($_POST['pickup_spot'] ?? 'Central Library Front Gate');

    // Retrieve equipment details
    $eqStmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = :id OR id = :id2 LIMIT 1");
    $eqStmt->execute(['id' => $req_item_id, 'id2' => $req_item_id]);
    $item = $eqStmt->fetch();

    if ($item) {
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $diff_days = max(1, round(($end_ts - $start_ts) / 86400));

        $total_rent = $diff_days * $item['daily_rate'];
        $deposit = $item['security_deposit'];
        $handover_token = 'TRX-' . rand(1000, 9999);

        try {
            $ins = $pdo->prepare("
                INSERT INTO rental_agreement (equipment_id, renter_id, start_date, end_date, total_rent, deposit, handover_token, pickup_spot, status)
                VALUES (:eq_id, :renter_id, :start_date, :end_date, :total_rent, :deposit, :token, :spot, 'Pending')
            ");
            $ins->execute([
                'eq_id'      => $req_item_id,
                'renter_id'  => $renter_id,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'total_rent' => $total_rent,
                'deposit'    => $deposit,
                'token'      => $handover_token,
                'spot'       => $pickup_spot
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

// Fetch Item Data
$item = null;
try {
    $stmt = $pdo->prepare("
        SELECT e.*, c.name AS category_name, m.name AS owner_name, m.student_id AS owner_student_id, m.phone AS owner_phone 
        FROM equipment e 
        LEFT JOIN category c ON e.category_id = c.category_id OR e.category_id = c.id
        LEFT JOIN member m ON e.member_id = m.member_id OR e.member_id = m.id 
        WHERE e.equipment_id = :id OR e.id = :id2
        LIMIT 1
    ");
    $stmt->execute(['id' => $item_id, 'id2' => $item_id]);
    $item = $stmt->fetch();
} catch (Exception $e) {
    $item = null;
}

if (!$item) {
    header("Location: index.php");
    exit();
}

$image_url = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80';

$base_path = '.';
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
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-lg">
              <?php echo strtoupper(substr($item['owner_name'] ?? 'L', 0, 1)); ?>
            </div>
            <div>
              <h4 class="text-sm font-bold text-navy-900"><?php echo htmlspecialchars($item['owner_name'] ?? 'Lender'); ?></h4>
              <p class="text-xs text-slate-500">Student Roll: <?php echo htmlspecialchars($item['owner_student_id'] ?? 'Verified'); ?></p>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Verified Peer</span>
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

          <form action="item-details.php?id=<?php echo $item_id; ?>" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label for="rental_start_date" class="block text-xs font-bold text-slate-700 mb-1">Pickup Date *</label>
                <input type="date" id="rental_start_date" name="rental_start_date" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
              </div>
              <div>
                <label for="rental_end_date" class="block text-xs font-bold text-slate-700 mb-1">Return Date *</label>
                <input type="date" id="rental_end_date" name="rental_end_date" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
              </div>
            </div>

            <div>
              <label for="pickup_spot" class="block text-xs font-bold text-slate-700 mb-1">Campus Handover Spot *</label>
              <select id="pickup_spot" name="pickup_spot" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
                <option value="Central Library Front Gate">Central Library Front Gate</option>
                <option value="Campus Cafeteria Entrance">Campus Cafeteria Entrance</option>
                <option value="Academic Building-1 Gate">Academic Building-1 Gate</option>
                <option value="Engineering Lab Complex">Engineering Lab Complex</option>
                <option value="TSC Ground / Student Union">TSC Ground / Student Union</option>
              </select>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2">
              <div class="flex justify-between text-slate-600">
                <span>Daily Rate:</span>
                <span class="font-bold text-slate-800">৳<?php echo number_format($item['daily_rate'] ?? 0, 2); ?></span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Refundable Deposit:</span>
                <span class="font-bold text-emerald-600">৳<?php echo number_format($item['security_deposit'] ?? 0, 2); ?></span>
              </div>
              <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-navy-900">
                <span>Estimated Total:</span>
                <span>৳<?php echo number_format(($item['daily_rate'] ?? 0) + ($item['security_deposit'] ?? 0), 2); ?></span>
              </div>
            </div>

            <?php if (is_member()): ?>
              <button type="submit" name="submit_request" class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                <span>Request Equipment Rental</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </button>
            <?php else: ?>
              <a href="auth/login.php?msg=login_required" class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider text-center block shadow-lg shadow-blue-600/30">
                Sign In as Member to Rent
              </a>
            <?php endif; ?>

            <p class="text-[11px] text-center text-slate-400">
              🔒 Handover token is required for physical exchange. 100% escrow protection.
            </p>
          </form>

        </div>
      </div>

    </div>

  </main>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
