<?php
/**
 * Shared Navigation Bar Partial - Modern Floating UI Design System
 */
$base_path = $base_path ?? '.';
$current_role = $_SESSION['role'] ?? null;
$current_name = $_SESSION['name'] ?? 'User';
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['member_id'] ?? null;
$nav_user_id = intval($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
$incoming_swaps_count = 0;

if ($nav_user_id > 0) {
  if (!isset($conn) || !$conn) {
    @require_once(__DIR__ . '/../config/db.php');
  }
  if (isset($conn) && $conn) {
    $swap_badge_query = mysqli_query($conn, "
            SELECT COUNT(exchange_id) AS total_pending 
            FROM exchange_agreement 
            WHERE lender_b_id = '$nav_user_id' 
              AND status = 'Pending'
        ");
    if ($swap_badge_query) {
      $badge_data = mysqli_fetch_assoc($swap_badge_query);
      $incoming_swaps_count = intval($badge_data['total_pending'] ?? 0);
    }
  } elseif (isset($pdo)) {
    try {
      $stmt = $pdo->prepare("
                SELECT COUNT(exchange_id) 
                FROM exchange_agreement 
                WHERE lender_b_id = ? 
                  AND status = 'Pending'
            ");
      $stmt->execute([$nav_user_id]);
      $incoming_swaps_count = intval($stmt->fetchColumn() ?? 0);
    } catch (Exception $e) {
      $incoming_swaps_count = 0;
    }
  }
}

// Fetch live member verification status from DB
$current_member_status = 'Pending';
if ($current_role === 'member' && $current_user_id) {
  if (!isset($pdo)) {
    @require_once(__DIR__ . '/../config/db.php');
  }
  if (isset($pdo)) {
    try {
      $stmt = $pdo->prepare("SELECT status FROM member WHERE member_id = :id LIMIT 1");
      $stmt->execute(['id' => $current_user_id]);
      $current_member_status = $stmt->fetchColumn() ?: 'Pending';
    } catch (Exception $e) {
      $current_member_status = 'Pending';
    }
  }
}

// Determine active navigation item dynamically from current script/URL
$current_script = strtolower(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''));
$current_page = basename($current_script);
$request_uri = strtolower(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');

$is_dashboard_active = ($current_page === 'dashboard.php' && strpos($current_script, '/admin/') === false);
$is_equipment_active = ($current_page === 'equipment.php' || $current_page === 'add_item.php');
$is_rentals_active = ($current_page === 'rentals.php');
$is_exchanges_active = ($current_page === 'exchanges.php');
$is_about_active = ($current_page === 'about.php');
$is_terms_active = ($current_page === 'terms.php');
$is_profile_active = ($current_page === 'profile.php');
$is_browse_active = (!$is_dashboard_active && !$is_equipment_active && !$is_rentals_active && !$is_exchanges_active && !$is_about_active && !$is_terms_active && !$is_profile_active) &&
  ($current_page === 'index.php' || $current_page === 'item-details.php' || $current_page === '' || substr($request_uri, -1) === '/' || substr($request_uri, -8) === '/rentora');
$is_admin_active = ($current_page === 'dashboard.php' && strpos($current_script, '/admin/') !== false);
$is_members_active = ($current_page === 'members.php' && strpos($current_script, '/admin/') !== false);
$is_join_active = ($current_page === 'join.php');

// Support optional manual override via $active_nav variable if set by caller
if (isset($active_nav)) {
  $is_browse_active = ($active_nav === 'browse');
  $is_about_active = ($active_nav === 'about');
  $is_join_active = ($active_nav === 'join');
  $is_terms_active = ($active_nav === 'terms');
  $is_dashboard_active = ($active_nav === 'dashboard');
  $is_equipment_active = ($active_nav === 'equipment');
  $is_rentals_active = ($active_nav === 'rentals');
  $is_exchanges_active = ($active_nav === 'exchanges');
  $is_admin_active = ($active_nav === 'admin' || $active_nav === 'admin-dashboard');
  $is_members_active = ($active_nav === 'members' || $active_nav === 'admin-members');
}

$nav_active_class = 'nav-link nav-link-active px-3 py-1.5 text-xs font-bold rounded-xl shrink-0 whitespace-nowrap flex items-center gap-1.5';
$nav_inactive_class = 'nav-link px-3 py-1.5 text-xs font-semibold rounded-xl shrink-0 whitespace-nowrap flex items-center gap-1.5';
?>
<!-- Modern Anti-Gravity Floating Navigation Header -->
<header class="floating-nav-header w-full">

  <!-- TIER 1: Brand & User Profile Control Deck -->
  <div class="tier-1-top w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-18 flex items-center justify-between gap-4">

      <!-- Tier 1 Left: Campus Identifier -->
      <div class="flex-1 flex items-center justify-start min-w-0">
        <span
          class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 dark:bg-white/5 border border-white/20 dark:border-white/10 text-[11px] font-mono text-white dark:text-slate-300">
          <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
          <span class="font-semibold text-white dark:text-slate-100">Premier University</span>
        </span>
      </div>

      <!-- Tier 1 Center: Floating Brand Logo -->
      <div class="shrink-0 flex items-center justify-center">
        <a href="<?php echo $base_path; ?>/index.php" class="flex items-center gap-2.5 group">
          <div
            class="w-9 h-9 rounded-xl bg-white/20 dark:bg-accent border border-white/25 dark:border-transparent flex items-center justify-center text-white font-extrabold shadow-md shadow-black/10 dark:shadow-accent/20 group-hover:scale-105 transition-transform shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
              </path>
            </svg>
          </div>
          <div class="text-left">
            <div class="flex items-center gap-1">
              <span class="text-xl font-extrabold tracking-tight text-white">Rentora</span>
              <span class="text-xl font-bold text-white dark:text-sky-400">Hub</span>
            </div>
            <p class="text-[9px] text-white/80 dark:text-slate-400 uppercase tracking-widest font-semibold font-mono">Campus Exchange</p>
          </div>
        </a>
      </div>

      <!-- Tier 1 Right: Controls, Theme Toggle & Profile -->
      <div class="flex-1 flex items-center justify-end gap-2.5 sm:gap-3 shrink-0">

        <!-- Theme Mode Toggle Button -->
        <button type="button" id="theme-toggle-btn"
          class="p-2 rounded-xl bg-white/15 hover:bg-white/25 dark:bg-white/5 border border-white/20 dark:border-white/10 text-white dark:text-slate-300 hover:text-white dark:hover:bg-white/10 transition-all focus:outline-none focus:ring-2 focus:ring-white/30 dark:focus:ring-sky-500/20"
          title="Toggle Light/Dark Theme" aria-label="Toggle Theme">
          <svg id="theme-icon-dark" class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
            </path>
          </svg>
          <svg id="theme-icon-light" class="w-4 h-4 block dark:hidden text-white" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
          </svg>
        </button>

        <?php if ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php"
            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-white bg-white/20 border border-white/30 dark:text-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/20 rounded-lg shrink-0">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 dark:bg-emerald-500"></span>
            <span>Admin</span>
          </a>
        <?php endif; ?>

        <?php if ($current_role === 'member'): ?>
          <?php
          if (empty($_SESSION['avatar']) && $current_user_id > 0) {
            $av_matches = glob(__DIR__ . '/../uploads/avatars/avatar_' . $current_user_id . '.*');
            if (!empty($av_matches)) {
              $_SESSION['avatar'] = 'uploads/avatars/' . basename($av_matches[0]);
              $_SESSION['avatar_v'] = filemtime($av_matches[0]);
            }
          }
          $nav_user_has_avatar = !empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../' . $_SESSION['avatar']);
          $nav_user_avatar_url = $nav_user_has_avatar ? ($base_path . '/' . $_SESSION['avatar'] . '?v=' . ($_SESSION['avatar_v'] ?? '1')) : null;
          ?>
          <!-- Profile Singularity -->
          <div id="profile-singularity" class="profile-singularity relative shrink-0" tabindex="0">
            <button type="button"
              class="profile-singularity-trigger flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-white/20 dark:border-white/10 bg-white/15 hover:bg-white/25 dark:bg-white/5 dark:hover:bg-white/10 text-left focus:outline-none transition-colors shrink-0"
              id="singularity-trigger" aria-haspopup="true" aria-expanded="false">
              <div class="relative shrink-0">
                <?php if ($nav_user_has_avatar): ?>
                  <img src="<?php echo htmlspecialchars($nav_user_avatar_url); ?>" alt="Avatar"
                    class="w-7 h-7 rounded-full object-cover shadow-sm ring-1 ring-white/20 dark:ring-white/10 shrink-0">
                <?php else: ?>
                  <div
                    class="w-7 h-7 rounded-full bg-white/25 text-white dark:bg-accent flex items-center justify-center font-bold text-xs shadow-sm shrink-0 border border-white/30 dark:border-transparent">
                    <?php echo strtoupper(substr($current_name, 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <?php if ($current_member_status === 'Verified'): ?>
                  <span
                    class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 dark:bg-emerald-500 ring-2 ring-blue-700 dark:ring-[#0B0F17]"
                    title="Verified Member"></span>
                <?php elseif ($current_member_status === 'Rejected'): ?>
                  <span
                    class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-400 dark:bg-rose-500 ring-2 ring-blue-700 dark:ring-[#0B0F17]"
                    title="Account Rejected"></span>
                <?php else: ?>
                  <span
                    class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-amber-400 dark:bg-amber-500 ring-2 ring-blue-700 dark:ring-[#0B0F17]"
                    title="Pending Verification"></span>
                <?php endif; ?>
              </div>

              <div class="text-left leading-tight hidden lg:block shrink-0">
                <div class="text-xs font-bold text-white dark:text-slate-100 truncate max-w-[120px]">
                  <?php echo htmlspecialchars($current_name); ?>
                </div>
                <div class="text-[10px] font-mono text-white/80 dark:text-slate-400">
                  <?php if ($current_member_status === 'Verified'): ?>
                    <span class="text-emerald-200 dark:text-emerald-400 font-semibold">Verified</span>
                  <?php else: ?>
                    <span class="text-amber-200 dark:text-amber-400 font-semibold">Pending</span>
                  <?php endif; ?>
                </div>
              </div>

              <svg class="singularity-chevron w-3.5 h-3.5 text-white dark:text-slate-400 shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Profile Dropdown Menu -->
            <div
              class="profile-singularity-menu absolute right-0 top-full mt-2 min-w-[17rem] max-w-sm rounded-2xl p-4 z-50 pointer-events-none opacity-0"
              id="singularity-menu">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-200 dark:border-white/10 mb-2.5 px-1">
                <?php if ($nav_user_has_avatar): ?>
                  <img src="<?php echo htmlspecialchars($nav_user_avatar_url); ?>" alt="Avatar"
                    class="w-10 h-10 rounded-full object-cover shadow-sm ring-1 ring-slate-200 dark:ring-white/10">
                <?php else: ?>
                  <div
                    class="w-10 h-10 rounded-full bg-accent text-white flex items-center justify-center font-extrabold text-sm shadow-sm">
                    <?php echo strtoupper(substr($current_name, 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate"><?php echo htmlspecialchars($current_name); ?></p>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate">
                    @<?php echo htmlspecialchars($_SESSION['username'] ?? 'member'); ?></p>
                  <div class="mt-1 flex items-center gap-1.5">
                    <span
                      class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold <?php echo $current_member_status === 'Verified' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'; ?>">
                      <?php echo htmlspecialchars($current_member_status); ?>
                    </span>
                    <?php if (!empty($_SESSION['student_id'])): ?>
                      <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 truncate">ID:
                        <?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="space-y-1">
                <a href="<?php echo $base_path; ?>/user/profile.php"
                  class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors">
                  <svg class="w-4 h-4 text-blue-700 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  <span>Edit Profile</span>
                </a>
                <a href="<?php echo $base_path; ?>/auth/change_password.php"
                  class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors">
                  <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                  </svg>
                  <span>Account Security</span>
                </a>
              </div>

              <div class="pt-2 mt-2 border-t border-slate-200 dark:border-white/10">
                <a href="<?php echo $base_path; ?>/auth/logout.php"
                  class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-xs font-semibold transition-colors">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                  </svg>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>

        <?php elseif ($current_role === 'admin'): ?>
          <!-- Admin Singularity -->
          <div id="profile-singularity" class="profile-singularity relative shrink-0" tabindex="0">
            <button type="button"
              class="profile-singularity-trigger flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-white/20 dark:border-white/10 bg-white/15 hover:bg-white/25 dark:bg-white/5 dark:hover:bg-white/10 text-left focus:outline-none transition-colors shrink-0"
              id="singularity-trigger" aria-haspopup="true" aria-expanded="false">
              <div
                class="w-7 h-7 rounded-full bg-white/25 text-white dark:bg-emerald-600 flex items-center justify-center font-bold text-xs shadow-sm shrink-0 border border-white/30 dark:border-transparent">
                A
              </div>
              <div class="text-left leading-tight hidden lg:block shrink-0">
                <div class="text-xs font-bold text-white dark:text-slate-100 truncate max-w-[120px]">
                  <?php echo htmlspecialchars($current_name); ?></div>
                <div class="text-[10px] font-mono text-emerald-200 dark:text-emerald-400">Admin</div>
              </div>
              <svg class="singularity-chevron w-3.5 h-3.5 text-white dark:text-slate-400 shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <div
              class="profile-singularity-menu absolute right-0 top-full mt-2 min-w-[17rem] max-w-sm rounded-2xl p-4 z-50 pointer-events-none opacity-0"
              id="singularity-menu">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-200 dark:border-white/10 mb-2 px-1">
                <div
                  class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                  A</div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate"><?php echo htmlspecialchars($current_name); ?></p>
                  <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">System Administrator</p>
                </div>
              </div>
              <div class="space-y-1">
                <a href="<?php echo $base_path; ?>/admin/dashboard.php"
                  class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors">
                  <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                  </svg>
                  <span>Operations Console</span>
                </a>
                <a href="<?php echo $base_path; ?>/admin/members.php"
                  class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors">
                  <svg class="w-4 h-4 text-blue-700 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                  </svg>
                  <span>Member Directory</span>
                </a>
              </div>
              <div class="pt-2 mt-2 border-t border-slate-200 dark:border-white/10">
                <a href="<?php echo $base_path; ?>/auth/logout.php"
                  class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-xs font-semibold transition-colors">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                  </svg>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Guest Auth Actions -->
          <a href="<?php echo $base_path; ?>/auth/login.php"
            class="px-3 py-1.5 text-xs font-bold text-white hover:bg-white/15 dark:text-slate-300 dark:hover:text-white rounded-xl transition-colors shrink-0 whitespace-nowrap">
            Sign In
          </a>
          <a href="<?php echo $base_path; ?>/auth/register.php"
            class="px-3.5 py-1.5 text-xs font-bold rounded-xl shrink-0 whitespace-nowrap shadow-sm transition-all bg-white text-blue-900 hover:bg-blue-50 dark:bg-accent dark:text-white dark:hover:bg-accent-hover">
            Register
          </a>
        <?php endif; ?>

      </div>

    </div>
  </div>

  <!-- TIER 2: Dedicated Horizontal Floating Nav Ribbon -->
  <div class="horizontal-nav-bar w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-11 flex items-center justify-center">
      <nav id="main-nav"
        class="flex items-center justify-center space-x-1 sm:space-x-1.5 md:space-x-2 overflow-x-auto no-scrollbar py-0.5 w-full whitespace-nowrap">

        <a href="<?php echo $base_path; ?>/index.php"
          class="<?php echo $is_browse_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="browse" <?php if ($is_browse_active): ?>aria-current="page" <?php endif; ?>>
          <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <span>Browse Equipment</span>
        </a>

        <?php if ($current_role === 'member'): ?>
          <a href="<?php echo $base_path; ?>/user/dashboard.php"
            class="<?php echo $is_dashboard_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="dashboard"
            <?php if ($is_dashboard_active): ?>aria-current="page" <?php endif; ?>>
            <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
              </path>
            </svg>
            <span>Dashboard</span>
          </a>

          <a href="<?php echo $base_path; ?>/user/equipment.php"
            class="<?php echo $is_equipment_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="equipment"
            <?php if ($is_equipment_active): ?>aria-current="page" <?php endif; ?>>
            <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
              </path>
            </svg>
            <span>My Equipment</span>
          </a>

          <a href="<?php echo $base_path; ?>/user/rentals.php"
            class="<?php echo $is_rentals_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="rentals"
            <?php if ($is_rentals_active): ?>aria-current="page" <?php endif; ?>>
            <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>Rentals</span>
          </a>

          <a href="<?php echo $base_path; ?>/user/exchanges.php"
            class="<?php echo $is_exchanges_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="exchanges"
            <?php if ($is_exchanges_active): ?>aria-current="page" <?php endif; ?>>
            <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            <span>Exchanges</span>
            <?php if ($incoming_swaps_count > 0): ?>
              <span
                class="inline-flex items-center justify-center px-1.5 py-0.2 text-[9px] font-extrabold bg-white text-blue-900 dark:bg-accent dark:text-white rounded-full min-w-[15px]">
                <?php echo $incoming_swaps_count; ?>
              </span>
            <?php endif; ?>
          </a>
        <?php elseif ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php"
            class="<?php echo $is_admin_active ? $nav_active_class : $nav_inactive_class; ?>"
            data-nav-key="admin-dashboard" <?php if ($is_admin_active): ?>aria-current="page" <?php endif; ?>>
            <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
              </path>
            </svg>
            <span>Operations</span>
          </a>
          <a href="<?php echo $base_path; ?>/admin/members.php"
            class="<?php echo $is_members_active ? $nav_active_class : $nav_inactive_class; ?>"
            data-nav-key="admin-members" <?php if ($is_members_active): ?>aria-current="page" <?php endif; ?>>
            <svg class="w-3.5 h-3.5 shrink-0 text-white dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
              </path>
            </svg>
            <span>Members</span>
          </a>
        <?php endif; ?>

        <span class="inline-block w-px h-3.5 bg-white/10 mx-1"></span>

        <a href="<?php echo $base_path; ?>/about.php"
          class="<?php echo $is_about_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="about" <?php if ($is_about_active): ?>aria-current="page" <?php endif; ?>>
          <span>About</span>
        </a>

        <a href="<?php echo $base_path; ?>/join.php"
          class="<?php echo $is_join_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="join" <?php if ($is_join_active): ?>aria-current="page" <?php endif; ?>>
          <span class="text-white dark:text-sky-400 font-bold">Join Team</span>
        </a>

        <a href="<?php echo $base_path; ?>/terms.php"
          class="<?php echo $is_terms_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="terms" <?php if ($is_terms_active): ?>aria-current="page" <?php endif; ?>>
          <span>Terms</span>
        </a>

      </nav>
    </div>
  </div>

</header>

<script>
  (function () {
    'use strict';

    // Theme Switcher Logic
    var themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) {
      themeBtn.addEventListener('click', function () {
        var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        var newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        if (newTheme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
        try {
          localStorage.setItem('rentora_theme', newTheme);
        } catch (e) { }
      });
    }

    // Profile Singularity Dropdown Toggle
    var singularity = document.getElementById('profile-singularity');
    var trigger = document.getElementById('singularity-trigger');
    var menu = document.getElementById('singularity-menu');
    if (singularity && trigger && menu) {
      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isDilated = singularity.classList.contains('dilated');
        if (isDilated) {
          singularity.classList.remove('dilated');
          trigger.setAttribute('aria-expanded', 'false');
        } else {
          singularity.classList.add('dilated');
          trigger.setAttribute('aria-expanded', 'true');
        }
      });

      document.addEventListener('click', function (e) {
        if (!singularity.contains(e.target)) {
          singularity.classList.remove('dilated');
          trigger.setAttribute('aria-expanded', 'false');
        }
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          singularity.classList.remove('dilated');
          trigger.setAttribute('aria-expanded', 'false');
        }
      });
    }
  })();
</script>
<?php $has_page_container = true; ?>
<!-- Page Content Container for Smooth Sliding Transitions -->
<div id="page-container" class="page-container flex-1 flex flex-col relative w-full overflow-x-hidden">