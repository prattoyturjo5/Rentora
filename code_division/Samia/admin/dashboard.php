<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');


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

// KPI 4: Security Deposit Balance
$deposit_locked = safe_scalar($pdo, "SELECT SUM(deposit_amount) FROM rental_agreement WHERE status IN ('Approved', 'Active')");

// Member Verification Queue & Management
$members = safe_query($pdo, "SELECT * FROM member ORDER BY member_id DESC");
$pending_verif_count = safe_scalar($pdo, "SELECT COUNT(*) FROM member WHERE status = 'Pending'");

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

// Team Recruitment Telemetry Applications
$total_team_apps = safe_scalar($pdo, "SELECT COUNT(*) FROM team_applications");
$pending_team_apps = safe_scalar($pdo, "SELECT COUNT(*) FROM team_applications WHERE status = 'Pending'");
$team_applications_list = safe_query($pdo, "SELECT * FROM team_applications ORDER BY application_id DESC");

$base_path = '..';
$page_title = 'Admin Operations Console - Rentora';
require_once(__DIR__ . '/../includes/header.php');
?>

  <!-- Top Admin Notice Bar -->
  <div class="bg-surface-elevated text-muted text-xs py-2 border-b border-subtle">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap justify-between items-center gap-2">
      <div class="flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span class="font-medium text-primary">University Administration Clearance Level:</span> 
        <span class="text-muted">Student ID verification, equipment moderation, and agreement records.</span>
      </div>
      <div class="flex items-center gap-4 text-muted">
        <a href="../index.php" class="hover:text-primary transition-colors flex items-center gap-1 font-medium">
          Exit to Marketplace &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- Admin Navigation Header -->
  <header class="static z-40 bg-surface/80 backdrop-blur-md border-b border-subtle shadow-float">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        
        <a href="../index.php" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-accent to-sky-700 flex items-center justify-center text-white shadow-md shadow-accent/20 transition-transform group-hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="text-xl font-extrabold tracking-tight text-primary">Rentora</span>
              <span class="text-xl font-bold text-accent">Admin</span>
            </div>
            <p class="text-[10px] text-muted uppercase tracking-widest font-semibold">Campus Operations Console</p>
          </div>
        </a>

        <div class="flex items-center gap-3">
          <nav class="hidden md:flex items-center gap-1.5 text-xs font-semibold mr-2">
            <a href="dashboard.php" class="px-3.5 py-1.5 rounded-xl bg-accent text-white font-bold shadow-sm">Dashboard</a>
            <a href="members.php" class="px-3.5 py-1.5 rounded-xl text-muted hover:text-primary hover:bg-surface-subtle transition font-medium">Member Directory</a>
          </nav>

          <!-- Theme Toggle Button -->
          <button id="themeToggleBtnAdmin" type="button" aria-label="Toggle Dark/Light Mode" class="p-2 rounded-xl text-muted hover:text-primary hover:bg-surface-subtle border border-subtle transition-all shadow-sm">
            <svg id="themeIconSunAdmin" class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg id="themeIconMoonAdmin" class="w-4 h-4 block dark:hidden text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
          </button>
          
          <script>
            (function() {
              var btn = document.getElementById('themeToggleBtnAdmin');
              if (btn) {
                btn.addEventListener('click', function() {
                  var cur = document.documentElement.getAttribute('data-theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                  var next = cur === 'dark' ? 'light' : 'dark';
                  if (next === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.classList.add('dark');
                  } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                    document.documentElement.classList.remove('dark');
                  }
                  localStorage.setItem('rentora_theme', next);
                });
              }
            })();
          </script>

          <div class="flex items-center pl-3 border-l border-subtle gap-2.5">
            <div class="w-8 h-8 rounded-full bg-accent text-white flex items-center justify-center font-bold text-xs shadow-sm">
              A
            </div>
            <div class="text-left leading-tight hidden sm:block">
              <div class="text-xs font-bold text-primary"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Administrator'); ?></div>
              <div class="text-[11px] font-semibold text-accent flex items-center gap-1">
                <span>Staff Level 4</span>
                <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
              </div>
            </div>
            <a href="change_password.php" title="Change Admin Password" class="text-muted hover:text-accent transition-colors p-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </a>
            <a href="../auth/logout.php" title="Sign Out" class="text-muted hover:text-red-500 transition-colors p-1.5">
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
      <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>Action executed successfully: <?php echo htmlspecialchars($_GET['msg']); ?></span>
      </div>
    <?php endif; ?>

    <!-- Overview KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      
      <div class="card-floating p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted">Verified Members</span>
          <span class="w-8 h-8 rounded-xl bg-sky-500/10 text-accent border border-sky-500/20 flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary"><?php echo number_format($total_students); ?></div>
        <span class="text-[11px] text-muted">Total registered campus students</span>
      </div>

      <div class="card-floating p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted">Active Rentals</span>
          <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary"><?php echo number_format($active_rentals); ?></div>
        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">Equipment currently checked out</span>
      </div>

      <div class="card-floating p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted">Exchanges</span>
          <span class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 border border-blue-500/20 flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary"><?php echo number_format($total_exchanges); ?></div>
        <span class="text-[11px] text-muted">Total swap agreements</span>
      </div>

      <div class="card-floating p-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-muted">Security Deposits</span>
          <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 flex items-center justify-center font-bold text-sm">
            ৳
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-primary">৳<?php echo number_format($deposit_locked, 2); ?></div>
        <span class="text-[11px] text-muted">Locked in deposit trust</span>
      </div>

    </div>

    <!-- Student Verification Queue -->
    <div class="card-floating mb-8 overflow-hidden">
      <div class="p-5 border-b border-subtle flex justify-between items-center bg-surface">
        <div>
          <h3 class="text-base font-extrabold text-primary">Student Verification Queue</h3>
          <p class="text-xs text-muted">Moderate new member registrations before they can borrow or list items</p>
        </div>
        <div class="flex items-center gap-2">
          <a href="members.php" class="text-xs font-bold text-accent hover:underline bg-accent/10 border border-accent/20 px-3 py-1.5 rounded-lg transition">Full Directory &rarr;</a>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
            <?php echo $pending_verif_count; ?> Pending
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle border-b border-subtle text-muted font-bold uppercase">
            <tr>
              <th class="py-3 px-4">Member ID</th>
              <th class="py-3 px-4">Student ID</th>
              <th class="py-3 px-4">Name</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Phone</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-subtle">
            <?php if (empty($members)): ?>
              <tr>
                <td colspan="7" class="py-6 text-center text-muted font-medium">
                  No members currently registered.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($members as $m): ?>
                <?php $mid = $m['member_id'] ?? $m['id'] ?? 0; ?>
                <tr class="hover:bg-surface-subtle/50 transition-colors">
                  <td class="py-3 px-4 font-mono font-semibold text-primary">#<?php echo $mid; ?></td>
                  <td class="py-3 px-4 font-bold text-primary"><?php echo htmlspecialchars($m['username'] ?? $m['student_id'] ?? 'N/A'); ?></td>
                  <td class="py-3 px-4 font-semibold text-primary"><?php echo htmlspecialchars(trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')) ?: ($m['name'] ?? '')); ?></td>
                  <td class="py-3 px-4 text-muted"><?php echo htmlspecialchars($m['university_email'] ?? $m['email'] ?? ''); ?></td>
                  <td class="py-3 px-4 text-muted"><?php echo htmlspecialchars($m['phone_number'] ?? $m['phone'] ?? ''); ?></td>
                  <td class="py-3 px-4">
                    <?php 
                      $statusVal = $m['status'] ?? 'Pending';
                      if ($statusVal === 'Verified'): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Verified</span>
                      <?php elseif ($statusVal === 'Rejected'): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">Rejected</span>
                      <?php else: ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">Pending</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-right space-x-1.5">
                    <?php if (($m['status'] ?? '') !== 'Verified'): ?>
                      <a href="verify_user.php?id=<?php echo $mid; ?>&action=approve" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm transition">
                        Approve
                      </a>
                    <?php endif; ?>
                    <?php if (($m['status'] ?? '') !== 'Rejected'): ?>
                      <a href="verify_user.php?id=<?php echo $mid; ?>&action=reject" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-bold text-[11px] transition">
                        Reject
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Equipment Moderation Table -->
    <div class="card-floating mb-8 overflow-hidden">
      <div class="p-5 border-b border-subtle bg-surface">
        <h3 class="text-base font-extrabold text-primary">Campus Equipment Moderation</h3>
        <p class="text-xs text-muted">Live listings across universities</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle border-b border-subtle text-muted font-bold uppercase">
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
          <tbody class="divide-y divide-subtle">
            <?php if (empty($equipment_list)): ?>
              <tr>
                <td colspan="7" class="py-6 text-center text-muted font-medium">
                  No equipment listings recorded in the database yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($equipment_list as $eq): ?>
                <?php $eqId = $eq['equipment_id'] ?? $eq['id'] ?? 0; ?>
                <tr class="hover:bg-surface-subtle/50 transition-colors">
                  <td class="py-3 px-4 font-semibold text-primary">
                    <?php echo htmlspecialchars($eq['title'] ?? 'Untitled'); ?>
                  </td>
                  <td class="py-3 px-4 text-muted">
                    <?php echo htmlspecialchars($eq['category_name'] ?? 'General'); ?>
                  </td>
                  <td class="py-3 px-4">
                    <span class="font-medium text-primary"><?php echo htmlspecialchars($eq['owner_name'] ?? 'Member'); ?></span>
                  </td>
                  <td class="py-3 px-4 font-bold text-primary">৳<?php echo number_format($eq['daily_rate'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4 text-muted">৳<?php echo number_format($eq['security_deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <?php if (!empty($eq['is_available'])): ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Available</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-surface-subtle text-muted border border-subtle">Rented Out</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <a href="delete_item.php?id=<?php echo $eqId; ?>" onclick="return confirm('Remove this equipment listing?')" class="text-red-500 hover:text-red-700 font-bold text-[11px] transition">
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

    <!-- Team Recruitment Telemetry Monolith (team_applications Sector) -->
    <div id="team-applications-section" class="card-floating overflow-hidden mb-8">
      <div class="px-6 py-4 border-b border-subtle bg-surface flex flex-wrap items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <h3 class="text-sm font-bold text-primary">Developer Recruitment Telemetry</h3>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-accent/10 text-accent border border-accent/20">
              rentora_db.team_applications
            </span>
          </div>
          <p class="text-xs text-muted mt-0.5">Incoming application vectors submitted via join.php</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs font-mono text-muted">
            Total Telemetry: <strong class="text-primary"><?php echo count($team_applications_list); ?></strong>
          </span>
          <a href="../join.php" target="_blank" class="px-3 py-1.5 rounded-lg bg-surface-subtle hover:bg-surface border border-subtle text-primary text-xs font-semibold flex items-center gap-1 transition">
            <span>View Public Terminal</span> &rarr;
          </a>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle text-muted uppercase tracking-wider font-semibold border-b border-subtle text-[11px]">
            <tr>
              <th class="py-3 px-4">Ref ID</th>
              <th class="py-3 px-4">Applicant Telemetry</th>
              <th class="py-3 px-4">Target Role</th>
              <th class="py-3 px-4">Department &amp; Skills</th>
              <th class="py-3 px-4">Portfolio</th>
              <th class="py-3 px-4">Statement of Purpose</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Timestamp</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-subtle">
            <?php if (empty($team_applications_list)): ?>
              <tr>
                <td colspan="8" class="py-8 text-center text-muted font-medium">
                  No recruitment telemetry captured yet. Awaiting transmissions from join.php.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($team_applications_list as $app): ?>
                <tr class="hover:bg-surface-subtle/50 transition-colors">
                  <td class="py-3 px-4 font-mono font-bold text-accent whitespace-nowrap">
                    #<?php echo htmlspecialchars((string)$app['application_id']); ?>
                  </td>
                  <td class="py-3 px-4">
                    <div class="font-bold text-primary"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                    <div class="text-[11px] font-mono text-muted">ID: <?php echo htmlspecialchars($app['student_id']); ?></div>
                    <div class="text-[11px] text-muted"><?php echo htmlspecialchars($app['university_email']); ?></div>
                    <div class="text-[10px] text-muted font-mono"><?php echo htmlspecialchars($app['phone_number']); ?></div>
                  </td>
                  <td class="py-3 px-4">
                    <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold bg-accent text-white">
                      <?php echo htmlspecialchars($app['role_applied']); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 max-w-xs">
                    <div class="text-primary font-medium"><?php echo htmlspecialchars($app['department']); ?></div>
                    <?php if (!empty($app['technical_skills'])): ?>
                      <div class="text-[10px] font-mono text-muted mt-0.5 truncate">
                        Skills: <?php echo htmlspecialchars($app['technical_skills']); ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4">
                    <?php if (!empty($app['portfolio_link'])): ?>
                      <a href="<?php echo htmlspecialchars($app['portfolio_link']); ?>" target="_blank" rel="noopener" class="text-accent hover:underline font-mono text-[11px] truncate block max-w-[120px]">
                        Link &rarr;
                      </a>
                    <?php else: ?>
                      <span class="text-muted text-[11px]">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 max-w-sm">
                    <div class="text-muted text-xs leading-relaxed line-clamp-2" title="<?php echo htmlspecialchars($app['statement_of_purpose']); ?>">
                      <?php echo htmlspecialchars($app['statement_of_purpose']); ?>
                    </div>
                  </td>
                  <td class="py-3 px-4 whitespace-nowrap">
                    <?php
                      $st = $app['status'] ?? 'Pending';
                      $st_class = match($st) {
                        'Accepted'    => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                        'Shortlisted' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
                        'Under_Review'=> 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                        'Archived'    => 'bg-surface-subtle text-muted border-subtle',
                        default       => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20'
                      };
                    ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo $st_class; ?>">
                      <?php echo htmlspecialchars($st); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-[11px] font-mono text-muted whitespace-nowrap">
                    <?php echo htmlspecialchars(substr($app['created_at'], 0, 16)); ?>
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
