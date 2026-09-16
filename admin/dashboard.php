<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

// Enforce admin guard
require_admin('login.php');

// Helper query function with error swallowing for clean rendering prior to schema import
function safe_query($pdo, $sql, $params = [], $default = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return $default;
    }
}

function safe_scalar($pdo, $sql, $params = [], $default = 0) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// KPI 1: Total Registered Members
$total_students = safe_scalar($pdo, "SELECT COUNT(*) FROM member");

// KPI 2: Active Rental Agreements
$active_rentals = safe_scalar($pdo, "SELECT COUNT(*) FROM rental_agreement WHERE status = 'Active'");

// KPI 3: Exchange Agreements Count
$total_exchanges = safe_scalar($pdo, "SELECT COUNT(*) FROM exchange_agreement");

// KPI 4: Escrow Locked Balance
$escrow_locked = safe_scalar($pdo, "SELECT SUM(deposit) FROM rental_agreement WHERE status IN ('Approved', 'Active')");

// Member Verification Queue
$pending_members = safe_query($pdo, "SELECT * FROM member WHERE status = 'Pending' ORDER BY 1 DESC");
$pending_verif_count = count($pending_members);

// Equipment Moderation List
$equipment_list = safe_query($pdo, "
    SELECT e.*, c.name as category_name, m.name as owner_name, m.student_id as owner_student_id 
    FROM equipment e 
    LEFT JOIN category c ON e.category_id = c.category_id OR e.category_id = c.id 
    LEFT JOIN member m ON e.member_id = m.member_id OR e.member_id = m.id 
    ORDER BY 1 DESC
");

// Active Agreements
$rental_agreements = safe_query($pdo, "
    SELECT r.*, e.title as item_title, m.name as renter_name, m.student_id as renter_student_id
    FROM rental_agreement r
    LEFT JOIN equipment e ON r.equipment_id = e.equipment_id OR r.equipment_id = e.id
    LEFT JOIN member m ON r.renter_id = m.member_id OR r.renter_id = m.id
    ORDER BY 1 DESC
");

$base_path = '..';
$page_title = 'Admin Operations Console - Rentora';
require_once(__DIR__ . '/../includes/header.php');
?>

  <!-- Top Admin Notice Bar -->
  <div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
      <div class="flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
        <span class="font-medium text-slate-200">University Administration Clearance Level:</span> 
        <span>Student ID verification, equipment moderation, and agreement records.</span>
      </div>
      <div class="flex items-center gap-4 text-slate-400">
        <a href="../index.php" class="hover:text-white transition-colors flex items-center gap-1">
          Exit to Marketplace &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- Admin Navigation Header -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        
        <a href="../index.php" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-amber-600 flex items-center justify-center text-white shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="text-xl font-extrabold tracking-tight text-navy-900">Rentora</span>
              <span class="text-xl font-bold text-amber-600">Admin</span>
            </div>
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Campus Operations Console</p>
          </div>
        </a>

        <div class="flex items-center gap-3">
          <div class="flex items-center pl-3 border-l border-slate-200 gap-2.5">
            <div class="w-8 h-8 rounded-full bg-amber-600 text-white flex items-center justify-center font-bold text-xs">
              A
            </div>
            <div class="text-left leading-tight hidden sm:block">
              <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Administrator'); ?></div>
              <div class="text-[11px] font-semibold text-amber-600 flex items-center gap-1">
                <span>Staff Level 4</span>
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
              </div>
            </div>
            <a href="change_password.php" title="Change Admin Password" class="text-slate-400 hover:text-amber-600 transition-colors p-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </a>
            <a href="../auth/logout.php" title="Sign Out" class="text-slate-400 hover:text-red-600 transition-colors p-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
          </div>
        </div>

      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
    
    <!-- Action Feedback Messages -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>Action executed successfully: <?php echo htmlspecialchars($_GET['msg']); ?></span>
      </div>
    <?php endif; ?>

    <!-- Overview KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Verified Members</span>
          <span class="w-8 h-8 rounded-xl bg-blue-50 text-primary-600 flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900"><?php echo number_format($total_students); ?></div>
        <span class="text-[11px] text-slate-400">Total registered campus students</span>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Rentals</span>
          <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900"><?php echo number_format($active_rentals); ?></div>
        <span class="text-[11px] text-emerald-600 font-semibold">Equipment currently checked out</span>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Exchanges</span>
          <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900"><?php echo number_format($total_exchanges); ?></div>
        <span class="text-[11px] text-slate-400">Total swap agreements</span>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Escrow Security</span>
          <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
            ৳
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-navy-900">৳<?php echo number_format($escrow_locked, 2); ?></div>
        <span class="text-[11px] text-slate-400">Locked in escrow trust</span>
      </div>

    </div>

    <!-- Student Verification Queue -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
      <div class="p-5 border-b border-slate-200 flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-navy-900">Student Verification Queue</h3>
          <p class="text-xs text-slate-500">Moderate new member registrations before they can borrow or list items</p>
        </div>
        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
          <?php echo $pending_verif_count; ?> Pending
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
            <tr>
              <th class="py-3 px-4">Member ID</th>
              <th class="py-3 px-4">Student ID</th>
              <th class="py-3 px-4">Name</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Phone</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($pending_members)): ?>
              <tr>
                <td colspan="6" class="py-6 text-center text-slate-400 font-medium">
                  No members currently pending verification. All registered students are verified.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($pending_members as $m): ?>
                <?php $mid = $m['member_id'] ?? $m['id'] ?? 0; ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4 font-mono font-semibold">#<?php echo $mid; ?></td>
                  <td class="py-3 px-4 font-bold text-navy-900"><?php echo htmlspecialchars($m['student_id'] ?? 'N/A'); ?></td>
                  <td class="py-3 px-4 font-semibold text-slate-800"><?php echo htmlspecialchars($m['name'] ?? ''); ?></td>
                  <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($m['email'] ?? ''); ?></td>
                  <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($m['phone'] ?? ''); ?></td>
                  <td class="py-3 px-4 text-right space-x-1.5">
                    <a href="verify_user.php?id=<?php echo $mid; ?>&action=approve" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm">
                      Approve
                    </a>
                    <a href="verify_user.php?id=<?php echo $mid; ?>&action=reject" class="px-2.5 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 font-bold text-[11px]">
                      Reject
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Equipment Moderation Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
      <div class="p-5 border-b border-slate-200">
        <h3 class="text-base font-extrabold text-navy-900">Campus Equipment Moderation</h3>
        <p class="text-xs text-slate-500">Live listings across universities</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
            <tr>
              <th class="py-3 px-4">Equipment</th>
              <th class="py-3 px-4">Category</th>
              <th class="py-3 px-4">Owner</th>
              <th class="py-3 px-4">Daily Rate</th>
              <th class="py-3 px-4">Deposit</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($equipment_list)): ?>
              <tr>
                <td colspan="7" class="py-6 text-center text-slate-400 font-medium">
                  No equipment listings recorded in the database yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($equipment_list as $eq): ?>
                <?php $eqId = $eq['equipment_id'] ?? $eq['id'] ?? 0; ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4 font-semibold text-navy-900">
                    <?php echo htmlspecialchars($eq['title'] ?? 'Untitled'); ?>
                  </td>
                  <td class="py-3 px-4 text-slate-600">
                    <?php echo htmlspecialchars($eq['category_name'] ?? 'General'); ?>
                  </td>
                  <td class="py-3 px-4">
                    <span class="font-medium text-slate-800"><?php echo htmlspecialchars($eq['owner_name'] ?? 'Member'); ?></span>
                  </td>
                  <td class="py-3 px-4 font-bold text-navy-900">৳<?php echo number_format($eq['daily_rate'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4 text-slate-600">৳<?php echo number_format($eq['security_deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <?php if (!empty($eq['is_available'])): ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Available</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Rented Out</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <a href="delete_item.php?id=<?php echo $eqId; ?>" onclick="return confirm('Remove this equipment listing?')" class="text-red-600 hover:text-red-800 font-bold text-[11px]">
                      Delete Listing
                    </a>
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
