<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['role'] ?? '') !== 'member') {
    header("Location: ../auth/login.php");
    exit();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

$user_id = $_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0;

// Fetch member profile & actual status from database
$member_status = 'Pending';
$member_name = $_SESSION['name'] ?? 'Member';
$member_student_id = $_SESSION['student_id'] ?? 'Student';
$member_email = $_SESSION['email'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, username, student_id, university_email, status FROM member WHERE member_id = :uid LIMIT 1");
    $stmt->execute(['uid' => $user_id]);
    $member_row = $stmt->fetch();
    if ($member_row) {
        $member_status = $member_row['status'] ?: 'Pending';
        if (!empty($member_row['first_name'])) {
            $member_name = trim($member_row['first_name'] . ' ' . $member_row['last_name']);
        }
        if (!empty($member_row['student_id'])) {
            $member_student_id = $member_row['student_id'];
        }
        if (!empty($member_row['university_email'])) {
            $member_email = $member_row['university_email'];
        }
    }
} catch (Exception $e) {
    $member_status = 'Pending';
}

$dashboard_error = "";
$dashboard_msg = "";

if (isset($_GET['msg']) && $_GET['msg'] === 'requested') {
    $dashboard_msg = "Rental request submitted successfully! Your handover token is generated below.";
}
if (isset($_GET['verify_status'])) {
    if ($_GET['verify_status'] === 'success') {
        $dashboard_msg = "Token verified successfully! The rental is now Active, and both parties are verified.";
    } elseif ($_GET['verify_status'] === 'invalid') {
        $dashboard_error = "Invalid or expired handover token. Please confirm the code with the renter.";
    }
}
if (isset($_GET['error'])) {
    $errCode = $_GET['error'];
    if ($errCode === 'pending_verification' || $errCode === 'account_pending') {
        $dashboard_error = "Your account is pending verification. Equipment listing, rentals, and exchanges are restricted until approved.";
    } elseif ($errCode === 'rejected_verification' || $errCode === 'account_rejected') {
        $dashboard_error = "Your account verification was rejected. Equipment listing, rentals, and exchanges are disabled. Please contact an administrator.";
    } else {
        $dashboard_error = htmlspecialchars($errCode);
    }
}

// Defensive helper queries
function safe_user_query($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function safe_user_scalar($pdo, $sql, $params = [], $default = 0) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// 1. My Equipment Listings Count
$my_equipment_count = safe_user_scalar($pdo, "
    SELECT COUNT(*) FROM equipment 
    WHERE owner_id = :uid
", ['uid' => $user_id]);

// 2. My Active Rentals Count
$my_active_rentals_count = safe_user_scalar($pdo, "
    SELECT COUNT(*) FROM rental_agreement 
    WHERE renter_id = :uid AND status IN ('Pending', 'Active')
", ['uid' => $user_id]);

// 3. My Exchanges Count
$my_exchanges_count = safe_user_scalar($pdo, "
    SELECT COUNT(*) FROM exchange_agreement 
    WHERE lender_a_id = :uid1 OR lender_b_id = :uid2
", ['uid1' => $user_id, 'uid2' => $user_id]);

// 4. Security Deposit Total
$security_deposit_total = safe_user_scalar($pdo, "
    SELECT SUM(deposit_amount) FROM rental_agreement 
    WHERE renter_id = :uid AND status IN ('Pending', 'Active')
", ['uid' => $user_id]);

// 5. Incoming Pending Rental Requests (where member is owner)
$incoming_rentals_count = safe_user_scalar($pdo, "
    SELECT COUNT(r.rental_id) 
    FROM rental_agreement r
    JOIN equipment e ON r.equipment_id = e.equipment_id
    WHERE e.owner_id = :uid 
      AND r.status = 'Pending'
", ['uid' => $user_id]);

// Active Handover Token Card (latest pending or active rental)
$active_tokens = safe_user_query($pdo, "
    SELECT r.*, e.equipment_name AS title, e.campus_spot, e.image_url, 
           CONCAT(m.first_name, ' ', m.last_name) AS owner_name, 
           m.phone_number AS owner_phone
    FROM rental_agreement r
    LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
    LEFT JOIN member m ON e.owner_id = m.member_id
    WHERE r.renter_id = :uid AND r.status IN ('Pending', 'Active')
    ORDER BY r.rental_id DESC LIMIT 1
", ['uid' => $user_id]);

$latest_token_rental = !empty($active_tokens) ? $active_tokens[0] : null;

// Recent Rental Agreements
$my_recent_rentals = safe_user_query($pdo, "
    SELECT r.*, r.expected_end_date AS end_date, r.total_cost AS total_rent, r.deposit_amount AS deposit,
           e.equipment_name AS title, e.rental_rate AS daily_rate, e.campus_spot, 
           CONCAT(m.first_name, ' ', m.last_name) AS owner_name
    FROM rental_agreement r
    LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
    LEFT JOIN member m ON e.owner_id = m.member_id
    WHERE r.renter_id = :uid
    ORDER BY r.rental_id DESC LIMIT 5
", ['uid' => $user_id]);

$base_path = '..';
$page_title = 'Member Dashboard - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full bg-canvas">
    
    <!-- Flash Messages & Notices -->
    <?php if (!empty($dashboard_error) && ($errCode ?? '') !== 'rejected_verification' && ($errCode ?? '') !== 'account_rejected'): ?>
      <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-medium"><?php echo htmlspecialchars($dashboard_error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'requested' && isset($_GET['token']) && !empty($_GET['token'])): ?>
      <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-5 mb-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-base">
                      ✓
                  </div>
                  <div>
                      <h4 class="text-sm font-bold text-primary">Rental Request Submitted Successfully</h4>
                      <p class="text-xs text-muted">Present this token to the owner during physical gear handover on campus.</p>
                  </div>
              </div>
              <div class="flex items-center gap-3 bg-surface px-4 py-2 rounded-xl border border-emerald-500/30 shadow-sm">
                  <span class="text-xs text-muted font-medium">YOUR TOKEN:</span>
                  <span class="font-mono text-base font-bold text-emerald-600 dark:text-emerald-400 tracking-wider">
                      <?php echo htmlspecialchars($_GET['token']); ?>
                  </span>
              </div>
          </div>
      </div>
    <?php elseif (!empty($dashboard_msg)): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="font-medium"><?php echo htmlspecialchars($dashboard_msg); ?></span>
      </div>
    <?php endif; ?>

    <?php if ($member_status === 'Pending' && empty($dashboard_error)): ?>
      <div class="mb-6 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-start gap-3 shadow-sm">
        <div class="p-2 rounded-xl bg-amber-500/20 text-amber-500 shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
          <h4 class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Account Verification in Progress</h4>
          <p class="text-xs text-muted mt-0.5">Your student account is awaiting verification by the campus administrator. You can browse the marketplace catalog, but listing equipment, requesting rentals, and swap agreements are restricted until verified.</p>
        </div>
      </div>
    <?php elseif ($member_status === 'Rejected'): ?>
      <!-- Verification Support Card -->
      <div class="mb-8 p-6 rounded-2xl bg-rose-500/10 border border-rose-500/20 shadow-sm text-primary relative overflow-hidden">
        <div class="flex items-start gap-3.5">
          <div class="p-2.5 rounded-xl bg-rose-500/20 text-rose-500 border border-rose-500/30 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-primary">Account Verification Notice: Action Required</h3>
            <p class="text-xs text-muted mt-1 leading-relaxed">
              Your student registration was declined during audit. Listing equipment and booking borrowings remain restricted until re-verified by platform administrators.
            </p>
          </div>
        </div>

        <div class="mt-5 p-4 rounded-xl bg-surface border border-subtle shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-muted">
              <svg class="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              <span>Platform Support &amp; Appeals</span>
            </div>
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=support.rentora@bscse.puc.ac.bd&su=Verification+Appeal+-+Student+ID:+<?php echo urlencode($member_student_id); ?>" target="_blank" rel="noopener noreferrer" class="text-accent font-semibold text-xs hover:underline inline-flex items-center gap-1 mt-1">
              <span>support.rentora@bscse.puc.ac.bd</span>
            </a>
            <p class="text-[11px] text-muted mt-1.5">
              To request a re-audit, email the platform administrators with your Student ID and an updated photo of your university ID card.
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- User Welcome Banner -->
    <div class="bg-surface rounded-2xl p-6 border border-subtle shadow-float mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-colors">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-accent text-white flex items-center justify-center font-bold text-xl shadow-md shadow-accent/20">
          <?php echo strtoupper(substr($member_name, 0, 1)); ?>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-extrabold text-primary">Welcome, <?php echo htmlspecialchars($member_name); ?></h1>
            <?php if ($member_status === 'Verified'): ?>
              <span class="badge-subtle badge-verified text-[10px]">Verified Student</span>
            <?php elseif ($member_status === 'Rejected'): ?>
              <span class="badge-subtle badge-rejected text-[10px]">Verification Rejected</span>
            <?php else: ?>
              <span class="badge-subtle badge-pending text-[10px]">Verification Pending</span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-muted mt-0.5">
            ID: <strong class="text-primary font-mono"><?php echo htmlspecialchars($member_student_id); ?></strong> &bull;
            Email: <span class="text-primary"><?php echo htmlspecialchars($member_email); ?></span>
          </p>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="equipment.php" class="btn-secondary text-xs">
          <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          <span>List Equipment</span>
        </a>
        <a href="../index.php" class="btn-accent text-xs">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <span>Browse Catalog</span>
        </a>
      </div>
    </div>

    <?php if ($incoming_rentals_count > 0): ?>
      <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-primary flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
          <div class="relative w-10 h-10 rounded-xl bg-rose-500/20 text-rose-500 flex items-center justify-center font-bold text-base shrink-0 border border-rose-500/30">
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="absolute -top-1 -right-1 flex h-4 w-4">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600 text-[9px] text-white font-black items-center justify-center"><?php echo $incoming_rentals_count; ?></span>
            </span>
          </div>
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-2 font-mono">
              <span>New Rental Proposal<?php echo $incoming_rentals_count > 1 ? 's' : ''; ?> Received</span>
              <span class="px-1.5 py-0.5 rounded text-[9px] bg-rose-500 text-white font-extrabold uppercase"><?php echo $incoming_rentals_count; ?> Pending</span>
            </h4>
            <p class="text-xs text-muted mt-0.5">A campus peer has requested to rent equipment you listed. Approve or reject it in your Rentals Hub.</p>
          </div>
        </div>
        <a href="rentals.php" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shrink-0 transition-colors shadow-sm flex items-center gap-1.5 self-start sm:self-center">
          <span>Review Proposals (<?php echo $incoming_rentals_count; ?>) &rarr;</span>
        </a>
      </div>
    <?php endif; ?>

    <!-- Quick KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      
      <div class="bg-surface p-5 rounded-2xl border border-subtle shadow-float transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted font-mono">My Listings</span>
          <span class="w-8 h-8 rounded-xl bg-surface-subtle text-accent flex items-center justify-center font-bold text-sm">📦</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary"><?php echo $my_equipment_count; ?></div>
        <a href="equipment.php" class="text-[11px] text-accent font-semibold hover:underline">Manage equipment &rarr;</a>
      </div>

      <div class="bg-surface p-5 rounded-2xl border border-subtle shadow-float transition-colors relative overflow-hidden">
        <?php if ($incoming_rentals_count > 0): ?>
          <span class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white animate-pulse">
            <?php echo $incoming_rentals_count; ?> new request<?php echo $incoming_rentals_count > 1 ? 's' : ''; ?>
          </span>
        <?php endif; ?>
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted font-mono">Active Rentals</span>
          <span class="w-8 h-8 rounded-xl bg-surface-subtle text-emerald-500 flex items-center justify-center font-bold text-sm">⏱️</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary"><?php echo $my_active_rentals_count; ?></div>
        <a href="rentals.php" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
          View rental passes <?php if ($incoming_rentals_count > 0): ?><strong class="text-rose-500">(<?php echo $incoming_rentals_count; ?> pending)</strong><?php endif; ?> &rarr;
        </a>
      </div>

      <div class="bg-surface p-5 rounded-2xl border border-subtle shadow-float transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted font-mono">Exchanges</span>
          <span class="w-8 h-8 rounded-xl bg-surface-subtle text-sky-500 flex items-center justify-center font-bold text-sm">🔄</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary"><?php echo $my_exchanges_count; ?></div>
        <a href="exchanges.php" class="text-[11px] text-sky-600 dark:text-sky-400 font-semibold hover:underline">Swap agreements &rarr;</a>
      </div>

      <div class="bg-surface p-5 rounded-2xl border border-subtle shadow-float transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted font-mono">Security Deposits</span>
          <span class="w-8 h-8 rounded-xl bg-surface-subtle text-amber-500 flex items-center justify-center font-bold text-sm">৳</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary font-mono">৳<?php echo number_format($security_deposit_total, 2); ?></div>
        <span class="text-[11px] text-muted">Refundable deposit trust</span>
      </div>

    </div>

    <!-- Active Handover Token Card -->
    <?php if ($latest_token_rental): ?>
      <div class="bg-surface rounded-2xl p-6 sm:p-8 text-primary shadow-float border border-subtle mb-8 transition-colors">
        <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
          <div class="badge-subtle badge-verified text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Handover Token Active</span>
          </div>
          <span class="text-xs text-muted font-mono">Pickup Zone: <?php echo htmlspecialchars($latest_token_rental['pickup_spot'] ?? 'Hazari Lane'); ?></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
          <div class="md:col-span-7 space-y-2">
            <h3 class="text-lg font-bold text-primary"><?php echo htmlspecialchars($latest_token_rental['title'] ?? 'Equipment Rental'); ?></h3>
            <p class="text-xs text-muted">
              Lender: <strong class="text-primary"><?php echo htmlspecialchars($latest_token_rental['owner_name'] ?? 'Peer Student'); ?></strong> 
              <?php if (!empty($latest_token_rental['owner_phone'])): ?>
                (<?php echo htmlspecialchars($latest_token_rental['owner_phone']); ?>)
              <?php endif; ?>
            </p>
            <p class="text-xs text-muted">Present this verification token to the lender upon in-person equipment handover.</p>
          </div>

          <div class="md:col-span-5 bg-surface-subtle p-4 rounded-xl border border-subtle text-center">
            <span class="text-[10px] text-muted uppercase tracking-widest font-bold font-mono">Verification Token</span>
            <div class="text-3xl font-mono font-extrabold tracking-wider text-accent mt-1">
              <?php echo htmlspecialchars($latest_token_rental['handover_token'] ?? 'TRX-8291'); ?>
            </div>
            <span class="text-[11px] text-muted mt-1 block">Status: <?php echo htmlspecialchars($latest_token_rental['status']); ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Recent Rentals Table -->
    <div class="bg-surface rounded-2xl border border-subtle shadow-float mb-8 overflow-hidden transition-colors">
      <div class="p-5 border-b border-subtle flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-primary">Recent Rental Agreements</h3>
          <p class="text-xs text-muted">Track equipment you are renting from peers</p>
        </div>
        <a href="rentals.php" class="text-xs font-bold text-accent hover:underline">View All &rarr;</a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle border-b border-subtle text-muted font-bold uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Equipment</th>
              <th class="py-3 px-4">Owner</th>
              <th class="py-3 px-4">Dates</th>
              <th class="py-3 px-4">Total</th>
              <th class="py-3 px-4">Deposit</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle text-primary">
            <?php if (empty($my_recent_rentals)): ?>
              <tr>
                <td colspan="6" class="py-6 text-center text-muted font-medium">
                  You haven't rented any equipment yet. 
                  <a href="../index.php" class="text-accent font-bold hover:underline">Browse the catalog</a>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($my_recent_rentals as $r): ?>
                <tr class="hover:bg-surface-subtle transition-colors">
                  <td class="py-3 px-4 font-semibold text-primary"><?php echo htmlspecialchars($r['title'] ?? 'Equipment'); ?></td>
                  <td class="py-3 px-4 text-muted"><?php echo htmlspecialchars($r['owner_name'] ?? 'Lender'); ?></td>
                  <td class="py-3 px-4 text-muted font-mono"><?php echo htmlspecialchars($r['start_date'] ?? ''); ?> &rarr; <?php echo htmlspecialchars($r['end_date'] ?? ''); ?></td>
                  <td class="py-3 px-4 font-bold text-primary font-mono">৳<?php echo number_format($r['total_rent'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4 text-muted font-mono">৳<?php echo number_format($r['deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <?php
                      $st = $r['status'] ?? 'Pending';
                      $badge = match($st) {
                        'Active' => 'badge-verified',
                        'Rejected' => 'badge-rejected',
                        default => 'badge-pending'
                      };
                    ?>
                    <span class="badge-subtle <?php echo $badge; ?>">
                      <?php echo htmlspecialchars($st); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
