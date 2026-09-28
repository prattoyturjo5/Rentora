<?php
/**
 * Shared Navigation Bar Partial
 */
$base_path = $base_path ?? '.';
$current_role = $_SESSION['role'] ?? null;
$current_name = $_SESSION['name'] ?? 'User';
// MERGED: also check member_id as fallback (from origin/main)
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['member_id'] ?? null;
$nav_user_id = intval($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
$incoming_swaps_count = 0;

if ($nav_user_id > 0) {
    if (!isset($conn) || !$conn) {
        @require_once(__DIR__ . '/../config/db.php');
    }
    if (isset($conn) && $conn) {
        // Count only Pending requests where the user is the recipient (lender_b_id)
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

// MERGED: fetch live member verification status from DB (from origin/main)
$current_member_status = 'Pending';
if ($current_role === 'member' && $current_user_id) {
    if (!isset($pdo)) {
        @require_once(__DIR__ . '/../config/db.php');
    }
    if (isset($pdo)) {
        if (function_exists('get_member_status')) {
            $current_member_status = get_member_status($pdo, $current_user_id);
        } else {
            try {
                $stmt = $pdo->prepare("SELECT status FROM member WHERE member_id = :id LIMIT 1");
                $stmt->execute(['id' => $current_user_id]);
                $current_member_status = $stmt->fetchColumn() ?: 'Pending';
            } catch (Exception $e) {
                $current_member_status = 'Pending';
            }
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
$is_join_active  = ($current_page === 'join.php');

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

$nav_active_class   = 'nav-link nav-link-active px-2.5 py-1.5 text-[13px] font-bold text-white bg-white/15 rounded-xl border border-white/20 shadow-sm shrink-0 whitespace-nowrap flex items-center gap-1.5';
$nav_inactive_class = 'nav-link px-2.5 py-1.5 text-[13px] font-semibold text-slate-200 hover:text-white hover:bg-white/10 rounded-xl transition-none shrink-0 whitespace-nowrap flex items-center gap-1.5';
?>
<!-- Monolith Header: Logo on Top (Centered) + Horizontal Navigation Bar in One Line Right Below -->
<header class="quantum-header static z-40 w-full bg-[#151B54] text-white border-b border-white/10 shadow-md">
  
  <!-- TIER 1: Top Bar with Logo Centered -->
  <div class="tier-1-top w-full border-b border-white/10 bg-[#151B54]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
      
      <!-- Tier 1 Left: Campus Identifier / Status (flex-1) -->
      <div class="flex-1 flex items-center justify-start min-w-0">
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-mono text-slate-300">
          <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
          <span class="hidden sm:inline font-semibold">Premier University</span>
          <span class="text-slate-400 hidden md:inline">&bull; CSE Hub</span>
        </span>
      </div>

      <!-- Tier 1 Center: Brand Logo on Top (Centered) (shrink-0) -->
      <div class="shrink-0 flex items-center justify-center">
        <a href="<?php echo $base_path; ?>/index.php" class="flex items-center gap-2.5 sm:gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-navy-950 border border-blue-400/30 flex items-center justify-center text-white font-extrabold shadow-md shadow-blue-500/25 transition-none shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          </div>
          <div class="text-left">
            <div class="flex items-center gap-1.5">
              <span class="text-xl sm:text-2xl font-black tracking-tight text-white">Rentora</span>
              <span class="text-xl sm:text-2xl font-extrabold text-sky-400">Hub</span>
            </div>
            <p class="text-[9px] text-slate-300 uppercase tracking-widest font-semibold font-mono">Campus Exchange</p>
          </div>
        </a>
      </div>

      <!-- Tier 1 Right: Profile Node / Auth Actions (flex-1 flex justify-end) -->
      <div class="flex-1 flex items-center justify-end gap-2 sm:gap-3 shrink-0">
        
        <?php if ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:text-white bg-emerald-950/50 border border-emerald-500/30 rounded-lg shrink-0 transition-none">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
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
          <div id="profile-singularity" class="profile-singularity group/singularity relative shrink-0" tabindex="0">
            <button type="button" class="profile-singularity-trigger flex items-center gap-2 px-2.5 py-2 rounded-xl border border-white/15 bg-white/10 hover:bg-white/15 text-left focus:outline-none shrink-0" id="singularity-trigger" aria-haspopup="true" aria-expanded="false">
              <div class="relative shrink-0">
                <?php if ($nav_user_has_avatar): ?>
                  <img src="<?php echo htmlspecialchars($nav_user_avatar_url); ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover shadow-md ring-1 ring-white/30 shrink-0">
                <?php else: ?>
                  <div class="w-8 h-8 rounded-full bg-[#1E2570] text-white flex items-center justify-center font-bold text-xs shadow-md border border-white/20 ring-1 ring-white/30 shrink-0">
                    <?php echo strtoupper(substr($current_name, 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <?php if ($current_member_status === 'Verified'): ?>
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#151B54]" title="Verified Member"></span>
                <?php elseif ($current_member_status === 'Rejected'): ?>
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-[#151B54]" title="Account Rejected"></span>
                <?php else: ?>
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-amber-400 ring-2 ring-[#151B54]" title="Pending Verification"></span>
                <?php endif; ?>
              </div>

              <div class="text-left leading-tight hidden lg:block shrink-0">
                <div class="text-xs font-bold text-white flex items-center gap-1.5 whitespace-nowrap">
                  <span><?php echo htmlspecialchars($current_name); ?></span>
                </div>
                <div class="text-[10px] font-mono text-slate-300">
                  <?php if ($current_member_status === 'Verified'): ?>
                    <span class="text-emerald-400 font-semibold">Verified</span>
                  <?php else: ?>
                    <span class="text-amber-400 font-semibold">Pending</span>
                  <?php endif; ?>
                </div>
              </div>

              <svg class="singularity-chevron w-3.5 h-3.5 text-slate-400 group-hover/singularity:text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Profile Dropdown Menu -->
            <div class="profile-singularity-menu absolute right-0 top-full mt-2 min-w-[18rem] max-w-sm rounded-2xl bg-[#0A0F1D]/98 backdrop-blur-2xl border border-white/15 shadow-2xl p-4 z-50 pointer-events-none opacity-0" id="singularity-menu">
              <div class="flex items-center gap-3 pb-3 border-b border-white/10 mb-2.5 px-1">
                <?php if ($nav_user_has_avatar): ?>
                  <img src="<?php echo htmlspecialchars($nav_user_avatar_url); ?>" alt="Avatar" class="w-11 h-11 rounded-full object-cover shadow-md ring-2 ring-white/20">
                <?php else: ?>
                  <div class="w-11 h-11 rounded-full bg-[#151B54] text-white flex items-center justify-center font-extrabold text-sm shadow-md border border-white/20 ring-2 ring-white/20">
                    <?php echo strtoupper(substr($current_name, 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($current_name); ?></p>
                  <p class="text-[11px] text-slate-400 font-mono truncate">@<?php echo htmlspecialchars($_SESSION['username'] ?? 'member'); ?></p>
                  <div class="mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold <?php echo $current_member_status === 'Verified' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'; ?>">
                      <?php echo htmlspecialchars($current_member_status); ?>
                    </span>
                    <?php if (!empty($_SESSION['student_id'])): ?>
                      <span class="text-[10px] font-mono text-slate-400">ID: <?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="space-y-1">
                <a href="<?php echo $base_path; ?>/user/profile.php" class="singularity-pocket-link group/item flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/10 transition-none">
                  <div class="w-8 h-8 rounded-lg bg-blue-500/15 border border-blue-400/30 flex items-center justify-center text-blue-400 group-hover/item:text-white group-hover/item:bg-blue-600 transition-none shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                  </div>
                  <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-200 group-hover/item:text-white flex items-center justify-between">
                      <span>Name &amp; Identity</span>
                      <span class="text-[10px] font-mono text-slate-500 group-hover/item:text-blue-300">Modify &rarr;</span>
                    </div>
                    <p class="text-[10px] text-slate-400">Update display name &amp; address</p>
                  </div>
                </a>

                <a href="<?php echo $base_path; ?>/user/profile.php#avatar" class="singularity-pocket-link group/item flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/10 transition-none">
                  <div class="w-8 h-8 rounded-lg bg-purple-500/15 border border-purple-400/30 flex items-center justify-center text-purple-400 group-hover/item:text-white group-hover/item:bg-purple-600 transition-none shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  </div>
                  <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-200 group-hover/item:text-white flex items-center justify-between">
                      <span>Avatar Hologram Studio</span>
                      <span class="text-[10px] font-mono text-slate-500 group-hover/item:text-purple-300">Upload &rarr;</span>
                    </div>
                    <p class="text-[10px] text-slate-400">Casual SVG preset compiler</p>
                  </div>
                </a>

                <a href="<?php echo $base_path; ?>/auth/change_password.php" class="singularity-pocket-link group/item flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-white/10 transition-none">
                  <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-400/30 flex items-center justify-center text-emerald-400 group-hover/item:text-white group-hover/item:bg-emerald-600 transition-none shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                  </div>
                  <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-200 group-hover/item:text-white flex items-center justify-between">
                      <span>Account Security</span>
                      <span class="text-[10px] font-mono text-slate-500 group-hover/item:text-emerald-300">Settings &rarr;</span>
                    </div>
                    <p class="text-[10px] text-slate-400">Password &amp; safety</p>
                  </div>
                </a>
              </div>

              <div class="pt-2 mt-2 border-t border-white/10">
                <a href="<?php echo $base_path; ?>/auth/logout.php" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl text-rose-400 hover:text-white hover:bg-rose-600/20 text-xs font-medium transition-none">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>

        <?php elseif ($current_role === 'admin'): ?>
          <!-- Admin Singularity -->
          <div id="profile-singularity" class="profile-singularity group/singularity relative shrink-0" tabindex="0">
            <button type="button" class="profile-singularity-trigger flex items-center gap-2 px-2.5 py-2 rounded-xl border border-white/15 bg-white/10 hover:bg-white/15 text-left focus:outline-none shrink-0" id="singularity-trigger" aria-haspopup="true" aria-expanded="false">
              <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-md ring-1 ring-emerald-400/40 shrink-0">
                A
              </div>
              <div class="text-left leading-tight hidden lg:block shrink-0">
                <div class="text-xs font-bold text-white whitespace-nowrap"><?php echo htmlspecialchars($current_name); ?></div>
                <div class="text-[10px] font-mono text-emerald-400">Admin</div>
              </div>
              <svg class="singularity-chevron w-3.5 h-3.5 text-slate-400 group-hover/singularity:text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <div class="profile-singularity-menu absolute right-0 top-full mt-2 min-w-[17rem] max-w-sm rounded-2xl bg-[#0A0F1D]/98 backdrop-blur-2xl border border-white/15 shadow-2xl p-4 z-50 pointer-events-none opacity-0" id="singularity-menu">
              <div class="flex items-center gap-3 pb-3 border-b border-white/10 mb-2 px-1">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm ring-2 ring-emerald-400/40">A</div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($current_name); ?></p>
                  <p class="text-[11px] text-emerald-400 font-mono">System Administrator</p>
                </div>
              </div>
              <div class="space-y-1">
                <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="singularity-pocket-link flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-white/10 text-xs font-semibold text-slate-200 hover:text-white">
                  <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  <span>Operations Console</span>
                </a>
                <a href="<?php echo $base_path; ?>/admin/members.php" class="singularity-pocket-link flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-white/10 text-xs font-semibold text-slate-200 hover:text-white">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                  <span>Member Directory</span>
                </a>
              </div>
              <div class="pt-2 mt-2 border-t border-white/10">
                <a href="<?php echo $base_path; ?>/auth/logout.php" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl text-rose-400 hover:text-white hover:bg-rose-600/20 text-xs font-medium transition-none">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Guest Auth Actions -->
          <a href="<?php echo $base_path; ?>/auth/login.php" class="px-3 py-2 text-xs font-bold text-slate-200 hover:text-white hover:bg-white/10 rounded-xl transition-none shrink-0 whitespace-nowrap">
            Sign In
          </a>
          <a href="<?php echo $base_path; ?>/auth/register.php" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/25 border border-blue-400/30 transition-none shrink-0 whitespace-nowrap">
            Register
          </a>
        <?php endif; ?>

        <!-- Mobile Hamburger Menu Button -->
        <div class="flex md:hidden items-center shrink-0">
          <a href="<?php echo $current_role === 'member' ? $base_path . '/user/dashboard.php' : $base_path . '/auth/login.php'; ?>" class="p-2 text-slate-300 hover:text-white rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </a>
        </div>

      </div>

    </div>
  </div>

  <!-- TIER 2: Dedicated Horizontal Navigation Bar Right Below In One Line -->
  <div class="horizontal-nav-bar w-full bg-[#0E133E] border-t border-white/10 border-b border-white/10 shadow-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-center">
      <nav id="main-nav" class="flex items-center justify-center space-x-1 sm:space-x-1.5 md:space-x-2 overflow-x-auto no-scrollbar py-1 w-full whitespace-nowrap">
        
        <a href="<?php echo $base_path; ?>/index.php" class="<?php echo $is_browse_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="browse" <?php if ($is_browse_active): ?>aria-current="page"<?php endif; ?>>
          <svg class="w-4 h-4 shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <span>Browse Equipment</span>
        </a>

        <?php if ($current_role === 'member'): ?>
          <a href="<?php echo $base_path; ?>/user/dashboard.php" class="<?php echo $is_dashboard_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="dashboard" <?php if ($is_dashboard_active): ?>aria-current="page"<?php endif; ?>>
            <svg class="w-4 h-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span>Dashboard</span>
          </a>

          <a href="<?php echo $base_path; ?>/user/equipment.php" class="<?php echo $is_equipment_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="equipment" <?php if ($is_equipment_active): ?>aria-current="page"<?php endif; ?>>
            <svg class="w-4 h-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span>My Equipment</span>
          </a>

          <a href="<?php echo $base_path; ?>/user/rentals.php" class="<?php echo $is_rentals_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="rentals" <?php if ($is_rentals_active): ?>aria-current="page"<?php endif; ?>>
            <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span>Rentals</span>
          </a>

          <a href="<?php echo $base_path; ?>/user/exchanges.php" class="<?php echo $is_exchanges_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="exchanges" <?php if ($is_exchanges_active): ?>aria-current="page"<?php endif; ?>>
            <svg class="w-4 h-4 shrink-0 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            <span>Exchanges</span>
            <?php if ($incoming_swaps_count > 0): ?>
              <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold bg-purple-500 text-white rounded-full min-w-[16px]">
                <?php echo $incoming_swaps_count; ?>
              </span>
            <?php endif; ?>
          </a>
        <?php elseif ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="<?php echo $is_admin_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="admin-dashboard" <?php if ($is_admin_active): ?>aria-current="page"<?php endif; ?>>
            <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span>Operations</span>
          </a>
          <a href="<?php echo $base_path; ?>/admin/members.php" class="<?php echo $is_members_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="admin-members" <?php if ($is_members_active): ?>aria-current="page"<?php endif; ?>>
            <svg class="w-4 h-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span>Members</span>
          </a>
        <?php endif; ?>

        <span class="inline-block w-px h-4 bg-white/20 mx-1"></span>

        <a href="<?php echo $base_path; ?>/about.php" class="<?php echo $is_about_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="about" <?php if ($is_about_active): ?>aria-current="page"<?php endif; ?>>
          <span>About</span>
        </a>

        <a href="<?php echo $base_path; ?>/join.php" class="<?php echo $is_join_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="join" <?php if ($is_join_active): ?>aria-current="page"<?php endif; ?>>
          <span class="text-sky-300 font-bold">Join Team</span>
        </a>

        <a href="<?php echo $base_path; ?>/terms.php" class="<?php echo $is_terms_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="terms" <?php if ($is_terms_active): ?>aria-current="page"<?php endif; ?>>
          <span>Terms</span>
        </a>

      </nav>
    </div>
  </div>

</header>

<script>
(function () {
  'use strict';
  // Profile Singularity Proximity & Click Toggle Continuity
  var singularity = document.getElementById('profile-singularity');
  var trigger = document.getElementById('singularity-trigger');
  var menu = document.getElementById('singularity-menu');
  if (singularity && trigger && menu) {
    trigger.addEventListener('click', function(e) {
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

    document.addEventListener('click', function(e) {
      if (!singularity.contains(e.target)) {
        singularity.classList.remove('dilated');
        trigger.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', function(e) {
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
