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
    
    <!-- User Welcome Banner -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-blue-500/20">
          <?php echo strtoupper(substr($_SESSION['name'] ?? 'M', 0, 1)); ?>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-extrabold text-navy-900">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Member'); ?></h1>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Verified Student</span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">
            Roll: <strong class="text-slate-700"><?php echo htmlspecialchars($_SESSION['student_id'] ?? 'Student'); ?></strong> &bull;
            Email: <span class="text-slate-700"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></span>
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
