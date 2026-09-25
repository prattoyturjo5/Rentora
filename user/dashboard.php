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
    WHERE member_id = :uid1 OR member_id = :uid2
", ['uid1' => $user_id, 'uid2' => $user_id]);

// 2. My Active Rentals Count
$my_active_rentals_count = safe_user_scalar($pdo, "
    SELECT COUNT(*) FROM rental_agreement 
    WHERE (renter_id = :uid1 OR renter_id = :uid2) AND status IN ('Approved', 'Active')
", ['uid1' => $user_id, 'uid2' => $user_id]);

// 3. My Exchanges Count
$my_exchanges_count = safe_user_scalar($pdo, "
    SELECT COUNT(*) FROM exchange_agreement 
    WHERE requester_id = :uid1 OR owner_id = :uid2
", ['uid1' => $user_id, 'uid2' => $user_id]);

// 4. Security Deposit Total
$security_deposit_total = safe_user_scalar($pdo, "
    SELECT SUM(deposit) FROM rental_agreement 
    WHERE (renter_id = :uid1 OR renter_id = :uid2) AND status IN ('Approved', 'Active')
", ['uid1' => $user_id, 'uid2' => $user_id]);

// Active Handover Token Card (latest approved or active rental)
$active_tokens = safe_user_query($pdo, "
    SELECT r.*, e.title, e.campus_spot, e.image_url, m.name as owner_name, m.phone as owner_phone
    FROM rental_agreement r
    LEFT JOIN equipment e ON r.equipment_id = e.equipment_id OR r.equipment_id = e.id
    LEFT JOIN member m ON e.member_id = m.member_id OR e.member_id = m.id
    WHERE (r.renter_id = :uid1 OR r.renter_id = :uid2) AND r.status IN ('Approved', 'Active')
    ORDER BY r.rental_id DESC LIMIT 1
", ['uid1' => $user_id, 'uid2' => $user_id]);

$latest_token_rental = !empty($active_tokens) ? $active_tokens[0] : null;

// Recent Rental Agreements
$my_recent_rentals = safe_user_query($pdo, "
    SELECT r.*, e.title, e.daily_rate, e.campus_spot, m.name as owner_name
    FROM rental_agreement r
    LEFT JOIN equipment e ON r.equipment_id = e.equipment_id OR r.equipment_id = e.id
    LEFT JOIN member m ON e.member_id = m.member_id OR e.member_id = m.id
    WHERE r.renter_id = :uid1 OR r.renter_id = :uid2
    ORDER BY r.rental_id DESC LIMIT 5
", ['uid1' => $user_id, 'uid2' => $user_id]);

// Recent Equipment Listed by Member
$my_recent_equipment = safe_user_query($pdo, "
    SELECT e.*, c.name as category_name
    FROM equipment e
    LEFT JOIN category c ON e.category_id = c.category_id OR e.category_id = c.id
    WHERE e.member_id = :uid1 OR e.member_id = :uid2
    ORDER BY e.equipment_id DESC LIMIT 5
", ['uid1' => $user_id, 'uid2' => $user_id]);

$base_path = '..';
$page_title = 'Member Dashboard - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    
    <!-- Flash Messages & Notices -->
    <?php if (!empty($dashboard_error) && ($errCode ?? '') !== 'rejected_verification' && ($errCode ?? '') !== 'account_rejected'): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-medium"><?php echo htmlspecialchars($dashboard_error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($dashboard_msg)): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="font-medium"><?php echo htmlspecialchars($dashboard_msg); ?></span>
      </div>
    <?php endif; ?>

    <?php if ($member_status === 'Pending' && empty($dashboard_error)): ?>
      <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3 shadow-sm">
        <div class="p-2 rounded-xl bg-amber-100 text-amber-700 shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
          <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800">Account Verification in Progress</h4>
          <p class="text-xs text-amber-700 mt-0.5">Your student account is awaiting verification by the campus administrator. You can browse the marketplace catalog, but listing equipment, requesting rentals, and swap agreements are locked until verified.</p>
        </div>
      </div>
    <?php elseif ($member_status === 'Rejected'): ?>
      <!-- Verification Support & Appeal Desk Card -->
      <div class="mb-8 p-6 rounded-2xl bg-red-50/60 border border-red-200 shadow-sm text-slate-800 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-start gap-3.5">
          <div class="p-2.5 rounded-xl bg-red-100/90 text-red-700 border border-red-200 shrink-0 shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-red-950">Account Verification Notice: Action Required</h3>
            <p class="text-xs text-red-900/90 mt-1 leading-relaxed">
              Your student registration was declined during administrative audit. Listing equipment, booking borrowings, and campus handovers remain restricted until your student profile is re-verified by platform admins.
            </p>
          </div>
        </div>

        <!-- Single-Channel Resolution Panel -->
        <div class="mt-5 p-4 rounded-xl bg-white/90 border border-red-100 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">
              <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              <span>Platform Support &amp; Appeals</span>
            </div>
            <a href="mailto:support.rentora@bscse.puc.ac.bd?subject=Verification%20Appeal%20-%20Student%20ID%3A%20<?php echo urlencode($member_student_id); ?>&body=Dear%20Rentora%20Admins%2C%0A%0AMy%20student%20verification%20was%20declined.%20Please%20re-audit%20my%20account.%0A%0AStudent%20Name%3A%20<?php echo urlencode($member_name); ?>%0AStudent%20ID%3A%20<?php echo urlencode($member_student_id); ?>%0AUniversity%20Email%3A%20<?php echo urlencode($member_email); ?>" class="text-blue-600 font-semibold text-xs hover:underline inline-flex items-center gap-1 mt-1">
              <span>support.rentora@bscse.puc.ac.bd</span>
            </a>
            <p class="text-[11px] text-slate-600 mt-1.5">
              To request a re-audit, email the platform admins with your Student ID and an updated photo of your university ID card.
            </p>
          </div>
        </div>

        <!-- Action Controls -->
        <div class="mt-5 pt-4 border-t border-red-200/80 flex flex-wrap items-center gap-2.5">
          <a href="mailto:support.rentora@bscse.puc.ac.bd?subject=Verification%20Appeal%20-%20Student%20ID%3A%20<?php echo urlencode($member_student_id); ?>&body=Dear%20Rentora%20Admins%2C%0A%0AMy%20student%20verification%20was%20declined.%20Please%20re-audit%20my%20account.%0A%0AStudent%20Name%3A%20<?php echo urlencode($member_name); ?>%0AStudent%20ID%3A%20<?php echo urlencode($member_student_id); ?>%0AUniversity%20Email%3A%20<?php echo urlencode($member_email); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
            <svg class="w-4 h-4 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <span>Email Platform Admin</span>
          </a>
          <button type="button" onclick="document.getElementById('guidelines-modal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl shadow-xs transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Read Verification Guidelines</span>
          </button>
        </div>

      </div>

      <!-- Verification Guidelines Modal -->
      <div id="guidelines-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-navy-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 border border-slate-200 shadow-2xl relative">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-blue-50 text-primary-600 flex items-center justify-center font-bold text-sm">
                📋
              </div>
              <div>
                <h3 class="text-sm font-bold text-navy-900">Platform Verification Guidelines</h3>
                <p class="text-[11px] text-slate-500">Requirements for valid student member clearance</p>
              </div>
            </div>
            <button type="button" onclick="document.getElementById('guidelines-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>

          <div class="mt-4 space-y-3.5 text-xs text-slate-600">
            <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
              <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">✓</span>
              <div>
                <strong class="text-slate-800">Clear Roll Number &amp; Profile:</strong>
                <p class="text-slate-500 mt-0.5">Both front and back of your Student ID card must be clearly legible, showing your full name, student ID roll number, and department.</p>
              </div>
            </div>
            <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
              <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">✓</span>
              <div>
                <strong class="text-slate-800">Valid Semester Sticker / Seal:</strong>
                <p class="text-slate-500 mt-0.5">Your physical student ID should exhibit an active semester sticker or current academic term verification stamp.</p>
              </div>
            </div>
            <div class="flex items-start gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
              <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">✓</span>
              <div>
                <strong class="text-slate-800">Institutional Email Address:</strong>
                <p class="text-slate-500 mt-0.5">Your account must be registered with your active university email (<code class="text-primary-700 font-semibold">@bscse.puc.ac.bd</code> or <code class="text-primary-700 font-semibold">@puc.ac.bd</code>).</p>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('guidelines-modal').classList.add('hidden')" class="px-4 py-2 bg-navy-900 hover:bg-navy-800 text-white font-bold rounded-xl text-xs transition-colors">
              Close Guidelines
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- User Welcome Banner -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-blue-500/20">
          <?php echo strtoupper(substr($member_name, 0, 1)); ?>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-extrabold text-navy-900">Welcome, <?php echo htmlspecialchars($member_name); ?></h1>
            <?php if ($member_status === 'Verified'): ?>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Verified Student</span>
            <?php elseif ($member_status === 'Rejected'): ?>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Verification Rejected</span>
            <?php else: ?>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Verification Pending</span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">
            Roll: <strong class="text-slate-700"><?php echo htmlspecialchars($member_student_id); ?></strong> &bull;
            Email: <span class="text-slate-700"><?php echo htmlspecialchars($member_email); ?></span>
          </p>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="equipment.php" class="px-4 py-2 bg-navy-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
          <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          <span>List Equipment</span>
        </a>
        <a href="../index.php" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition-all flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <span>Browse Marketplace</span>
        </a>
      </div>
    </div>

    <!-- Quick KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">My Listings</span>
          <span class="w-8 h-8 rounded-xl bg-blue-50 text-primary-600 flex items-center justify-center font-bold text-sm">📦</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900"><?php echo $my_equipment_count; ?></div>
        <a href="equipment.php" class="text-[11px] text-primary-600 font-semibold hover:underline">Manage equipment &rarr;</a>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Rentals</span>
          <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">⏱️</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900"><?php echo $my_active_rentals_count; ?></div>
        <a href="rentals.php" class="text-[11px] text-emerald-600 font-semibold hover:underline">View rental passes &rarr;</a>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Exchanges</span>
          <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">🔄</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900"><?php echo $my_exchanges_count; ?></div>
        <a href="exchanges.php" class="text-[11px] text-indigo-600 font-semibold hover:underline">Swap agreements &rarr;</a>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Security Deposits</span>
          <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">৳</span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900">৳<?php echo number_format($security_deposit_total, 2); ?></div>
        <span class="text-[11px] text-slate-400">Refundable deposit guarantee</span>
      </div>

    </div>

    <!-- Active Handover Token Card -->
    <?php if ($latest_token_rental): ?>
      <div class="bg-gradient-to-br from-navy-900 via-navy-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800 mb-8">
        <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            <span>Handover Token Ready</span>
          </div>
          <span class="text-xs text-slate-400 font-medium">Pickup Spot: <?php echo htmlspecialchars($latest_token_rental['pickup_spot'] ?? 'Hazari Lane'); ?></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
          <div class="md:col-span-7 space-y-2">
            <h3 class="text-lg font-bold text-white"><?php echo htmlspecialchars($latest_token_rental['title'] ?? 'Equipment Rental'); ?></h3>
            <p class="text-xs text-slate-300">
              Lender: <strong class="text-white"><?php echo htmlspecialchars($latest_token_rental['owner_name'] ?? 'Peer Student'); ?></strong> 
              <?php if (!empty($latest_token_rental['owner_phone'])): ?>
                (<?php echo htmlspecialchars($latest_token_rental['owner_phone']); ?>)
              <?php endif; ?>
            </p>
            <p class="text-xs text-slate-400">Present this one-time code to the lender upon in-person equipment inspection.</p>
          </div>

          <div class="md:col-span-5 bg-navy-800/80 p-4 rounded-xl border border-slate-700 text-center">
            <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Secret Verification Token</span>
            <div class="text-3xl font-mono font-extrabold tracking-wider text-emerald-400 mt-1">
              <?php echo htmlspecialchars($latest_token_rental['handover_token'] ?? 'TRX-8291'); ?>
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Status: <?php echo htmlspecialchars($latest_token_rental['status']); ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Recent Rentals Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
      <div class="p-5 border-b border-slate-200 flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-navy-900">Recent Rental Agreements</h3>
          <p class="text-xs text-slate-500">Track equipment you are renting from peers</p>
        </div>
        <a href="rentals.php" class="text-xs font-bold text-primary-600 hover:underline">View All &rarr;</a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
            <tr>
              <th class="py-3 px-4">Equipment</th>
              <th class="py-3 px-4">Owner</th>
              <th class="py-3 px-4">Dates</th>
              <th class="py-3 px-4">Total</th>
              <th class="py-3 px-4">Deposit</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($my_recent_rentals)): ?>
              <tr>
                <td colspan="6" class="py-6 text-center text-slate-400 font-medium">
                  You haven't rented any equipment yet. 
                  <a href="../index.php" class="text-primary-600 font-bold hover:underline">Browse the catalog</a>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($my_recent_rentals as $r): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4 font-semibold text-navy-900"><?php echo htmlspecialchars($r['title'] ?? 'Equipment'); ?></td>
                  <td class="py-3 px-4 text-slate-700"><?php echo htmlspecialchars($r['owner_name'] ?? 'Lender'); ?></td>
                  <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($r['start_date'] ?? ''); ?> &rarr; <?php echo htmlspecialchars($r['end_date'] ?? ''); ?></td>
                  <td class="py-3 px-4 font-bold text-navy-900">৳<?php echo number_format($r['total_rent'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4 text-slate-600">৳<?php echo number_format($r['deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                      <?php echo ($r['status'] === 'Active') ? 'bg-emerald-100 text-emerald-800' : (($r['status'] === 'Approved') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'); ?>">
                      <?php echo htmlspecialchars($r['status'] ?? 'Pending'); ?>
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
