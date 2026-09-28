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

// Support optional manual override via $active_nav variable if set by caller
if (isset($active_nav)) {
    $is_browse_active = ($active_nav === 'browse');
    $is_about_active = ($active_nav === 'about');
    $is_terms_active = ($active_nav === 'terms');
    $is_dashboard_active = ($active_nav === 'dashboard');
    $is_equipment_active = ($active_nav === 'equipment');
    $is_rentals_active = ($active_nav === 'rentals');
    $is_exchanges_active = ($active_nav === 'exchanges');
}

$nav_active_class   = 'nav-link nav-link-active px-3.5 py-2 text-sm font-semibold text-white rounded-lg relative z-10';
$nav_inactive_class = 'nav-link px-3.5 py-2 text-sm font-medium text-slate-400 hover:text-white rounded-lg relative z-10';
?>
<!-- Top Campus Notice Bar (Dark-Matter Vacuum Horizon) -->
<div class="bg-[#060A14] text-slate-300 text-xs py-2 px-4 border-b border-white/5 relative z-50">
  <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
    <div class="flex items-center gap-2">
      <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
      <span class="font-medium text-slate-200">Premier University Marketplace —</span> 
      <span class="text-slate-400">Exchange &amp; rent academic gear with verified PU students. Zero platform fee.</span>
    </div>
    <div class="flex items-center gap-3 text-slate-400">

      <span class="hidden sm:flex items-center gap-1 text-slate-400">
        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
        <span>Hazari Lane, Wasa &amp; GEC Campus</span>
      </span>
      <span class="hidden sm:inline text-slate-700">|</span>
      <?php if ($current_role === 'admin'): ?>
        <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="hover:text-white transition-colors flex items-center gap-1 text-emerald-300 font-semibold">
          <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Admin Console
        </a>
      <?php else: ?>
        <a href="<?php echo $base_path; ?>/admin/login.php" class="hover:text-white transition-colors flex items-center gap-1 text-slate-300 hover:text-white">
          <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Admin Portal
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Global Header Navigation with Absolute Dark-Matter Vacuum Matrix (#0A0F1D) -->
<header class="quantum-header sticky top-0 z-40 bg-[#0A0F1D]/95 backdrop-blur-2xl border-b border-white/10 shadow-[0_4px_30px_rgba(0,0,0,0.5)]">
  <div class="laser-guide-conduit"></div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      
      <!-- Brand Logo -->
      <a href="<?php echo $base_path; ?>/index.php" class="flex items-center gap-3 group">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-navy-950 border border-blue-400/30 flex items-center justify-center text-white font-extrabold shadow-md shadow-blue-500/25 group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div>
          <div class="flex items-center gap-1.5">
            <span class="text-xl font-extrabold tracking-tight text-white">Rentora</span>
            <span class="text-xl font-bold text-sky-400">Hub</span>
          </div>
          <p class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold font-mono">Campus Equipment Exchange</p>
        </div>
      </a>

      <!-- Primary Core Marketplace Cluster (Frictionless Wave of Energy) -->
      <nav id="main-nav" class="hidden md:flex items-center space-x-1 lg:space-x-2 relative py-1">
        <!-- Sliding active pill indicator -->
        <div id="nav-pill" aria-hidden="true"></div>

        <a href="<?php echo $base_path; ?>/index.php" class="<?php echo $is_browse_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="browse" <?php if ($is_browse_active): ?>aria-current="page"<?php endif; ?>>
          Browse Equipment
        </a>

        <?php if ($current_role === 'member'): ?>
          <a href="<?php echo $base_path; ?>/user/dashboard.php" class="<?php echo $is_dashboard_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="dashboard" <?php if ($is_dashboard_active): ?>aria-current="page"<?php endif; ?>>
            Dashboard
          </a>
          <a href="<?php echo $base_path; ?>/user/equipment.php" class="<?php echo ($is_equipment_active ? $nav_active_class : $nav_inactive_class); ?> flex items-center gap-1.5" data-nav-key="equipment" <?php if ($is_equipment_active): ?>aria-current="page"<?php endif; ?>>
            <span>My Equipment</span>
            <span class="text-[10px] bg-white/10 text-slate-300 border border-white/10 px-1.5 py-0.5 rounded font-bold">Lender</span>
          </a>
          <a href="<?php echo $base_path; ?>/user/rentals.php" class="<?php echo ($is_rentals_active ? $nav_active_class : $nav_inactive_class); ?> flex items-center gap-1.5" data-nav-key="rentals" <?php if ($is_rentals_active): ?>aria-current="page"<?php endif; ?>>
            <span>Rentals</span>
            <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.5 rounded font-bold">Active</span>
          </a>
          <a href="<?php echo $base_path; ?>/user/exchanges.php" class="<?php echo ($is_exchanges_active ? $nav_active_class : $nav_inactive_class); ?> flex items-center gap-1.5" data-nav-key="exchanges" <?php if ($is_exchanges_active): ?>aria-current="page"<?php endif; ?>>
            <span>Exchanges</span>
            <?php if ($incoming_swaps_count > 0): ?>
              <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold bg-purple-600 text-white rounded-full animate-pulse shadow-sm min-w-[18px]">
                <?php echo $incoming_swaps_count; ?>
              </span>
            <?php else: ?>
              <span class="text-[10px] bg-blue-500/20 text-sky-300 border border-blue-500/30 px-1.5 py-0.5 rounded font-bold">Swap</span>
            <?php endif; ?>
          </a>
        <?php elseif ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="px-3.5 py-2 text-sm font-semibold text-emerald-300 bg-emerald-950/60 border border-emerald-500/30 rounded-lg transition-colors" <?php if ($is_admin_active): ?>aria-current="page"<?php endif; ?>>
            Operations Console
          </a>
        <?php endif; ?>
      </nav>

      <!-- Outer Orbital Rim & User Singularity Controls -->
      <div class="hidden sm:flex items-center gap-3">
        
        <!-- Phase 1: Outer Orbital Rim Anchors (About & Terms Extracted from Center) -->
        <div class="orbital-rim flex items-center gap-1 border-r border-white/10 pr-3 mr-1">
          <a href="<?php echo $base_path; ?>/about.php" class="nav-orbit-link <?php echo $is_about_active ? 'nav-orbit-active' : ''; ?>" data-nav-key="about" <?php if ($is_about_active): ?>aria-current="page"<?php endif; ?>>
            About
          </a>
          <a href="<?php echo $base_path; ?>/terms.php" class="nav-orbit-link <?php echo $is_terms_active ? 'nav-orbit-active' : ''; ?>" data-nav-key="terms" <?php if ($is_terms_active): ?>aria-current="page"<?php endif; ?>>
            Terms
          </a>
        </div>

        <?php if ($current_role === 'member'): ?>
          <?php
            $nav_user_has_avatar = !empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../' . $_SESSION['avatar']);
            $nav_user_avatar_url = $nav_user_has_avatar ? ($base_path . '/' . $_SESSION['avatar'] . '?v=' . ($_SESSION['avatar_v'] ?? '1')) : null;
          ?>
          <!-- Phase 3: The Profile Singularity (Interactive Dilating Pocket) -->
          <div id="profile-singularity" class="profile-singularity group/singularity relative" tabindex="0">
            <!-- Singularity Interactive Trigger -->
            <button type="button" class="profile-singularity-trigger flex items-center gap-2.5 px-3 py-1.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all duration-150 text-left focus:outline-none focus:ring-2 focus:ring-blue-400/40" id="singularity-trigger" aria-haspopup="true" aria-expanded="false">
              <!-- Dynamic Gravitational Avatar Orb -->
              <div class="relative">
                <?php if ($nav_user_has_avatar): ?>
                  <img src="<?php echo htmlspecialchars($nav_user_avatar_url); ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover shadow-md ring-1 ring-white/30">
                <?php else: ?>
                  <div class="w-8 h-8 rounded-full bg-[#151B54] text-white flex items-center justify-center font-bold text-xs shadow-md border border-white/20 ring-1 ring-white/30">
                    <?php echo strtoupper(substr($current_name, 0, 1)); ?>
                  </div>
                <?php endif; ?>
                <!-- Status Beacon -->
                <?php if ($current_member_status === 'Verified'): ?>
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#0A0F1D] animate-pulse" title="Verified Member"></span>
                <?php elseif ($current_member_status === 'Rejected'): ?>
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-[#0A0F1D]" title="Account Rejected"></span>
                <?php else: ?>
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-amber-400 ring-2 ring-[#0A0F1D]" title="Pending Verification"></span>
                <?php endif; ?>
              </div>

              <!-- The Interactive User Name Singularity -->
              <div class="text-left leading-tight hidden lg:block pr-1">
                <div class="text-xs font-bold text-white group-hover/singularity:text-sky-300 transition-colors flex items-center gap-1.5">
                  <span><?php echo htmlspecialchars($current_name); ?></span>
                </div>
                <div class="text-[10px] font-mono text-slate-400 flex items-center gap-1">
                  <?php if ($current_member_status === 'Verified'): ?>
                    <span class="text-emerald-400 font-semibold">Verified</span>
                  <?php elseif ($current_member_status === 'Rejected'): ?>
                    <span class="text-rose-400 font-semibold">Rejected</span>
                  <?php else: ?>
                    <span class="text-amber-400 font-semibold">Pending</span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Dilation Chevron with Smooth Rotation -->
              <svg class="singularity-chevron w-3.5 h-3.5 text-slate-400 group-hover/singularity:text-white transition-transform duration-200 group-hover/singularity:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Localized Dimensional Pocket (Profile Management Menu) -->
            <div class="profile-singularity-menu absolute right-0 top-full mt-2 w-80 rounded-2xl bg-[#0A0F1D]/95 backdrop-blur-2xl border border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.85)] p-3.5 z-50 pointer-events-none opacity-0 -translate-y-2 scale-95 transition-all duration-200" id="singularity-menu">
              <!-- Singularity Core User Identity Header -->
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
                  <p class="text-[11px] text-slate-400 font-mono truncate">@<?php echo htmlspecialchars($_SESSION['username'] ?? 'member'); ?></p>
                  <div class="mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold <?php echo $current_member_status === 'Verified' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($current_member_status === 'Rejected' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'); ?>">
                      <span class="w-1.5 h-1.5 rounded-full <?php echo $current_member_status === 'Verified' ? 'bg-emerald-400' : ($current_member_status === 'Rejected' ? 'bg-rose-400' : 'bg-amber-400'); ?>"></span>
                      <?php echo htmlspecialchars($current_member_status); ?>
                    </span>
                    <?php if (!empty($_SESSION['student_id'])): ?>
                      <span class="text-[10px] font-mono text-slate-400">ID: <?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Immediate Singularity Pathways -->
              <div class="space-y-1">
                <!-- Pathway 1: Name Modifications -->
                <a href="<?php echo $base_path; ?>/user/profile.php" class="singularity-pocket-link group/item flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-white/10 transition-colors">
                  <div class="w-8 h-8 rounded-lg bg-blue-500/15 border border-blue-400/30 flex items-center justify-center text-blue-400 group-hover/item:text-white group-hover/item:bg-blue-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                  </div>
                  <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-200 group-hover/item:text-white flex items-center justify-between">
                      <span>Name &amp; Identity</span>
                      <span class="text-[10px] font-mono text-slate-500 group-hover/item:text-blue-300">Modify &rarr;</span>
                    </div>
                    <p class="text-[10px] text-slate-400">Update display name, phone &amp; campus address</p>
                  </div>
                </a>

                <!-- Pathway 2: Avatar Uploads -->
                <a href="<?php echo $base_path; ?>/user/profile.php#avatar" class="singularity-pocket-link group/item flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-white/10 transition-colors">
                  <div class="w-8 h-8 rounded-lg bg-purple-500/15 border border-purple-400/30 flex items-center justify-center text-purple-400 group-hover/item:text-white group-hover/item:bg-purple-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  </div>
                  <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-200 group-hover/item:text-white flex items-center justify-between">
                      <span>Avatar Uploads</span>
                      <span class="text-[10px] font-mono text-slate-500 group-hover/item:text-purple-300">Upload &rarr;</span>
                    </div>
                    <p class="text-[10px] text-slate-400">Upload persona image or custom avatar</p>
                  </div>
                </a>

                <!-- Pathway 3: Account & Security Settings -->
                <a href="<?php echo $base_path; ?>/auth/change_password.php" class="singularity-pocket-link group/item flex items-center gap-3 px-2.5 py-2 rounded-xl hover:bg-white/10 transition-colors">
                  <div class="w-8 h-8 rounded-lg bg-emerald-500/15 border border-emerald-400/30 flex items-center justify-center text-emerald-400 group-hover/item:text-white group-hover/item:bg-emerald-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                  </div>
                  <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-200 group-hover/item:text-white flex items-center justify-between">
                      <span>Account &amp; Security</span>
                      <span class="text-[10px] font-mono text-slate-500 group-hover/item:text-emerald-300">Settings &rarr;</span>
                    </div>
                    <p class="text-[10px] text-slate-400">Change password, credentials &amp; safety</p>
                  </div>
                </a>
              </div>

              <!-- Secondary Quick Pathways -->
              <div class="pt-2 mt-2 border-t border-white/10 grid grid-cols-3 gap-1 text-center">
                <a href="<?php echo $base_path; ?>/user/equipment.php" class="p-1.5 rounded-lg hover:bg-white/5 transition-colors">
                  <div class="text-[10px] font-semibold text-slate-300">Gear</div>
                  <div class="text-[9px] text-slate-500 font-mono">Listings</div>
                </a>
                <a href="<?php echo $base_path; ?>/user/rentals.php" class="p-1.5 rounded-lg hover:bg-white/5 transition-colors">
                  <div class="text-[10px] font-semibold text-slate-300">Rentals</div>
                  <div class="text-[9px] text-slate-500 font-mono">Active</div>
                </a>
                <a href="<?php echo $base_path; ?>/user/exchanges.php" class="p-1.5 rounded-lg hover:bg-white/5 transition-colors">
                  <div class="text-[10px] font-semibold text-slate-300">Swaps</div>
                  <div class="text-[9px] text-slate-500 font-mono">Status</div>
                </a>
              </div>

              <!-- Terminate Session Action -->
              <div class="pt-2 mt-2 border-t border-white/10">
                <a href="<?php echo $base_path; ?>/auth/logout.php" class="flex items-center justify-center gap-2 w-full py-1.5 px-3 rounded-lg text-rose-400 hover:text-white hover:bg-rose-600/20 text-xs font-medium transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>

          <!-- + List Equipment CTA -->
          <a href="<?php echo $base_path; ?>/user/equipment.php" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-600/25 border border-blue-400/30 transition-all hover:scale-[1.02]">
            <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ List Equipment</span>
          </a>

        <?php elseif ($current_role === 'admin'): ?>
          <!-- Admin Singularity Matrix -->
          <div id="profile-singularity" class="profile-singularity group/singularity relative" tabindex="0">
            <button type="button" class="profile-singularity-trigger flex items-center gap-2.5 px-3 py-1.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all duration-150 text-left focus:outline-none focus:ring-2 focus:ring-emerald-400/40" id="singularity-trigger" aria-haspopup="true" aria-expanded="false">
              <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-md ring-1 ring-emerald-400/40">
                A
              </div>
              <div class="text-left leading-tight hidden lg:block pr-1">
                <div class="text-xs font-bold text-white group-hover/singularity:text-emerald-300 transition-colors"><?php echo htmlspecialchars($current_name); ?></div>
                <div class="text-[10px] font-mono text-emerald-400 flex items-center gap-1">
                  <span>Administrator</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
              </div>
              <svg class="singularity-chevron w-3.5 h-3.5 text-slate-400 group-hover/singularity:text-white transition-transform duration-200 group-hover/singularity:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Admin Dimensional Pocket -->
            <div class="profile-singularity-menu absolute right-0 top-full mt-2 w-72 rounded-2xl bg-[#0A0F1D]/95 backdrop-blur-2xl border border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.85)] p-3.5 z-50 pointer-events-none opacity-0 -translate-y-2 scale-95 transition-all duration-200" id="singularity-menu">
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
                <a href="<?php echo $base_path; ?>/admin/change_password.php" class="singularity-pocket-link flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-white/10 text-xs font-semibold text-slate-200 hover:text-white">
                  <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                  <span>Change Password</span>
                </a>
              </div>
              <div class="pt-2 mt-2 border-t border-white/10">
                <a href="<?php echo $base_path; ?>/auth/logout.php" class="flex items-center justify-center gap-2 w-full py-1.5 px-3 rounded-lg text-rose-400 hover:text-white hover:bg-rose-600/20 text-xs font-medium transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- Guest Links -->
          <a href="<?php echo $base_path; ?>/auth/login.php" class="px-3.5 py-1.5 text-xs font-bold text-slate-300 hover:text-white rounded-lg hover:bg-white/10 transition-colors">
            Sign In
          </a>
          <a href="<?php echo $base_path; ?>/auth/register.php" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/25 border border-blue-400/30 transition-all hover:scale-[1.02]">
            Register Member
          </a>
        <?php endif; ?>
      </div>

      <!-- Mobile Menu Button -->
      <div class="flex md:hidden items-center gap-2">
        <a href="<?php echo $current_role === 'member' ? $base_path . '/user/dashboard.php' : $base_path . '/auth/login.php'; ?>" class="p-2 text-slate-300 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </a>
      </div>

    </div>
  </div>
</header>

<script>
(function () {
  'use strict';
  /* Synchronous Zero-Latency Quantum Snap on initial parse */
  var nav = document.getElementById('main-nav');
  var pill = document.getElementById('nav-pill');
  if (nav && pill) {
    var activeLink = nav.querySelector('a[aria-current="page"]');
    if (activeLink) {
      var nR = nav.getBoundingClientRect();
      var lR = activeLink.getBoundingClientRect();
      pill.style.left = (lR.left - nR.left) + 'px';
      pill.style.top = (lR.top - nR.top) + 'px';
      pill.style.width = lR.width + 'px';
      pill.style.height = lR.height + 'px';
      pill.style.opacity = '1';
    } else {
      pill.style.opacity = '0';
    }
  }

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
