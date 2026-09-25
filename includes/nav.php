<?php
/**
 * Shared Navigation Bar Partial
 */
$base_path = $base_path ?? '.';
$current_role = $_SESSION['role'] ?? null;
$current_name = $_SESSION['name'] ?? 'User';
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['member_id'] ?? null;

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
?>
<!-- Top Campus Notice Bar -->
<div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
  <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
    <div class="flex items-center gap-2">
      <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
      <span class="font-medium text-slate-200">Premier University Marketplace —</span> 
      <span>Exchange and rent gear with verified PU students. Cash handover only, no online payments.</span>
    </div>
    <div class="flex items-center gap-4 text-slate-400">
      <span class="flex items-center gap-1">
        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
        Pickup Points: Hazari Lane, Wasa & GEC Campus
      </span>
      <span class="hidden sm:inline">|</span>
      <?php if ($current_role === 'admin'): ?>
        <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="hover:text-white transition-colors flex items-center gap-1 text-emerald-300 font-semibold">
          <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Admin Console
        </a>
      <?php else: ?>
        <a href="<?php echo $base_path; ?>/admin/login.php" class="hover:text-white transition-colors flex items-center gap-1 text-slate-300">
          <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Admin Portal
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Global Header Navigation -->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      
      <!-- Brand Logo -->
      <a href="<?php echo $base_path; ?>/index.php" class="flex items-center gap-3 group">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-primary-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div>
          <div class="flex items-center gap-1.5">
            <span class="text-xl font-extrabold tracking-tight text-navy-900">Rentora</span>
            <span class="text-xl font-bold text-primary-600">Hub</span>
          </div>
          <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Campus Equipment Exchange</p>
        </div>
      </a>

      <!-- Desktop Navigation Links -->
      <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
        <a href="<?php echo $base_path; ?>/index.php" class="px-3.5 py-2 text-sm font-semibold text-primary-600 rounded-lg hover:bg-blue-50/80 transition-colors">
          Browse Equipment
        </a>

        <?php if ($current_role === 'member'): ?>
          <a href="<?php echo $base_path; ?>/user/dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors">
            Dashboard
          </a>
          <a href="<?php echo $base_path; ?>/user/equipment.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1.5">
            <span>My Equipment</span>
            <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-bold">Lender</span>
          </a>
          <a href="<?php echo $base_path; ?>/user/rentals.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1.5">
            <span>Rentals</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold">Active</span>
          </a>
          <a href="<?php echo $base_path; ?>/user/exchanges.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1.5">
            <span>Exchanges</span>
            <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">Swap</span>
          </a>
        <?php elseif ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="px-3.5 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 rounded-lg transition-colors">
            Operations Console
          </a>
        <?php endif; ?>
      </nav>

      <!-- Right Side Controls & Profile Switcher -->
      <div class="hidden sm:flex items-center gap-3">
        <?php if ($current_role === 'member'): ?>
          <!-- Authenticated Member Badge -->
          <div class="flex items-center pl-3 border-l border-slate-200 gap-2.5">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-xs">
              <?php echo strtoupper(substr($current_name, 0, 1)); ?>
            </div>
            <div class="text-left leading-tight hidden lg:block">
              <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($current_name); ?></div>
              <?php if ($current_member_status === 'Verified'): ?>
                <div class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                  <span>Verified</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </div>
              <?php elseif ($current_member_status === 'Rejected'): ?>
                <div class="text-[11px] font-semibold text-red-600 flex items-center gap-1">
                  <span>Rejected</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                </div>
              <?php else: ?>
                <div class="text-[11px] font-semibold text-amber-600 flex items-center gap-1">
                  <span>Pending</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                </div>
              <?php endif; ?>
            </div>
            <a href="<?php echo $base_path; ?>/auth/change_password.php" title="Change Password" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </a>
            <a href="<?php echo $base_path; ?>/auth/logout.php" title="Sign Out" class="text-slate-400 hover:text-red-600 transition-colors p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
          </div>

          <a href="<?php echo $base_path; ?>/user/equipment.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-navy-900 hover:bg-navy-800 text-white text-xs font-semibold shadow-sm transition-all hover:shadow-md">
            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ List Equipment</span>
          </a>

        <?php elseif ($current_role === 'admin'): ?>
          <!-- Authenticated Admin Badge -->
          <div class="flex items-center pl-3 border-l border-slate-200 gap-2.5">
            <div class="w-8 h-8 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">
              A
            </div>
            <div class="text-left leading-tight hidden lg:block">
              <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($current_name); ?></div>
              <div class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                <span>Administrator</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              </div>
            </div>
            <a href="<?php echo $base_path; ?>/admin/change_password.php" title="Change Password" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </a>
            <a href="<?php echo $base_path; ?>/auth/logout.php" title="Sign Out" class="text-slate-400 hover:text-red-600 transition-colors p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
          </div>

        <?php else: ?>
          <!-- Guest Links -->
          <a href="<?php echo $base_path; ?>/auth/login.php" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-primary-600 transition-colors">
            Sign In
          </a>
          <a href="<?php echo $base_path; ?>/auth/register.php" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all">
            Register Member
          </a>
        <?php endif; ?>
      </div>

      <!-- Mobile Menu Button -->
      <div class="flex md:hidden items-center gap-2">
        <a href="<?php echo $current_role === 'member' ? $base_path . '/user/dashboard.php' : $base_path . '/auth/login.php'; ?>" class="p-2 text-slate-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </a>
      </div>

    </div>
  </div>
</header>
