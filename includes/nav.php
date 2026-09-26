<?php
/**
 * Shared Navigation Bar Partial
 */
$base_path = $base_path ?? '.';
$current_role = $_SESSION['role'] ?? null;
$current_name = $_SESSION['name'] ?? 'User';
// MERGED: also check member_id as fallback (from origin/main)
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['member_id'] ?? null;

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
$is_browse_active = (!$is_dashboard_active && !$is_equipment_active && !$is_rentals_active && !$is_exchanges_active && !$is_about_active && !$is_terms_active) &&
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

$nav_active_class   = 'nav-link nav-link-active px-3.5 py-2 text-sm font-semibold text-primary-700 rounded-lg relative z-10';
$nav_inactive_class = 'nav-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg relative z-10';
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
        Pickup Points: Hazari Lane, Wasa &amp; GEC Campus
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
      <nav id="main-nav" class="hidden md:flex items-center space-x-1 lg:space-x-2 relative">
        <!-- Sliding active pill indicator -->
        <div id="nav-pill" aria-hidden="true"></div>

        <a href="<?php echo $base_path; ?>/index.php" class="<?php echo $is_browse_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="browse" <?php if ($is_browse_active): ?>aria-current="page"<?php endif; ?>>
          Browse Equipment
        </a>

        <a href="<?php echo $base_path; ?>/about.php" class="<?php echo $is_about_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="about" <?php if ($is_about_active): ?>aria-current="page"<?php endif; ?>>
          About
        </a>

        <a href="<?php echo $base_path; ?>/terms.php" class="<?php echo $is_terms_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="terms" <?php if ($is_terms_active): ?>aria-current="page"<?php endif; ?>>
          Terms
        </a>

        <?php if ($current_role === 'member'): ?>
          <a href="<?php echo $base_path; ?>/user/dashboard.php" class="<?php echo $is_dashboard_active ? $nav_active_class : $nav_inactive_class; ?>" data-nav-key="dashboard" <?php if ($is_dashboard_active): ?>aria-current="page"<?php endif; ?>>
            Dashboard
          </a>
          <a href="<?php echo $base_path; ?>/user/equipment.php" class="<?php echo ($is_equipment_active ? $nav_active_class : $nav_inactive_class); ?> flex items-center gap-1.5" data-nav-key="equipment" <?php if ($is_equipment_active): ?>aria-current="page"<?php endif; ?>>
            <span>My Equipment</span>
            <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-bold">Lender</span>
          </a>
          <a href="<?php echo $base_path; ?>/user/rentals.php" class="<?php echo ($is_rentals_active ? $nav_active_class : $nav_inactive_class); ?> flex items-center gap-1.5" data-nav-key="rentals" <?php if ($is_rentals_active): ?>aria-current="page"<?php endif; ?>>
            <span>Rentals</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold">Active</span>
          </a>
          <a href="<?php echo $base_path; ?>/user/exchanges.php" class="<?php echo ($is_exchanges_active ? $nav_active_class : $nav_inactive_class); ?> flex items-center gap-1.5" data-nav-key="exchanges" <?php if ($is_exchanges_active): ?>aria-current="page"<?php endif; ?>>
            <span>Exchanges</span>
            <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">Swap</span>
          </a>
        <?php elseif ($current_role === 'admin'): ?>
          <a href="<?php echo $base_path; ?>/admin/dashboard.php" class="px-3.5 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 rounded-lg transition-colors" <?php if ($is_admin_active): ?>aria-current="page"<?php endif; ?>>
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
              <!-- MERGED: show live verification status (from origin/main) -->
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

<script>
(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     Config
  ───────────────────────────────────────────────────────────── */
  var SK         = 'rentora_nav_state';   // sessionStorage key
  var DURATION   = 300;                   // slide ms
  var EASE       = 'cubic-bezier(0.4,0,0.2,1)';
  var TRANSITION = [
    'left '   + DURATION + 'ms ' + EASE,
    'width '  + DURATION + 'ms ' + EASE,
    'top '    + DURATION + 'ms ' + EASE,
    'height ' + DURATION + 'ms ' + EASE
  ].join(', ');

  /* Respect prefers-reduced-motion */
  var reducedMotion = (
    window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches
  );

  /* ─────────────────────────────────────────────────────────────
     sessionStorage helpers (wrapped so SSR/private-mode never throws)
  ───────────────────────────────────────────────────────────── */
  function readState() {
    try { return JSON.parse(sessionStorage.getItem(SK)) || null; }
    catch (_) { return null; }
  }
  function writeState(obj) {
    try { sessionStorage.setItem(SK, JSON.stringify(obj)); }
    catch (_) {}
  }
  function clearState() {
    try { sessionStorage.removeItem(SK); }
    catch (_) {}
  }

  /* ─────────────────────────────────────────────────────────────
     Geometry helpers
  ───────────────────────────────────────────────────────────── */
  function measure(link, nav) {
    var nR = nav.getBoundingClientRect();
    var lR = link.getBoundingClientRect();
    return { left: lR.left - nR.left, top: lR.top - nR.top,
             width: lR.width, height: lR.height };
  }

  function applyPos(pill, pos, animate) {
    pill.style.transition = animate ? TRANSITION : 'none';
    pill.style.left    = pos.left   + 'px';
    pill.style.top     = pos.top    + 'px';
    pill.style.width   = pos.width  + 'px';
    pill.style.height  = pos.height + 'px';
    pill.style.opacity = '1';
  }

  /* ─────────────────────────────────────────────────────────────
     Detect if a native View Transition is currently in progress.
     If yes, the browser is already animating nav-pill via the
     @view-transition CSS — skip the JS animation to avoid doubling.
  ───────────────────────────────────────────────────────────── */
  function isViewTransitionActive() {
    try {
      /* Chrome 126+ sets :root::view-transition when a VT is running */
      return document.documentElement.classList.contains('vt-active') ||
             document.getAnimations().some(function (a) {
               return a.effect &&
                      a.effect.target &&
                      /view-transition/.test(a.effect.target.nodeName || '');
             });
    } catch (_) { return false; }
  }

  /* ─────────────────────────────────────────────────────────────
     PHASE 1 — Synchronous snap (runs before first browser paint)
     ─────────────────────────────────────────────────────────────
     The <script> tag lives right after </header>, so the nav DOM
     is already parsed and getBoundingClientRect() is usable.
     Positioning the pill here means the VERY FIRST PAINT shows
     it already correctly placed — zero visible pop / flash.
  ───────────────────────────────────────────────────────────── */
  var nav        = document.getElementById('main-nav');
  var pill       = document.getElementById('nav-pill');
  var activeLink = nav ? nav.querySelector('a[aria-current="page"]') : null;
  var currentKey = activeLink ? activeLink.getAttribute('data-nav-key') : null;

  var prevState  = readState();
  var prevLink   = null;
  var doAnimate  = false;

  if (nav && pill && activeLink) {
    if (!reducedMotion && prevState && prevState.key && prevState.key !== currentKey) {
      var vwDelta = Math.abs((prevState.viewportWidth || 0) - window.innerWidth);
      if (vwDelta <= 120) {
        prevLink  = nav.querySelector('a[data-nav-key="' + prevState.key + '"]');
        doAnimate = !!prevLink;
      }
    }

    if (doAnimate) {
      /* Snap pill to the FROM position — no transition, runs synchronously */
      applyPos(pill, measure(prevLink, nav), false);
    } else {
      /* No animation (first visit / direct URL / refresh / large resize) */
      applyPos(pill, measure(activeLink, nav), false);
      clearState();
    }
  } else if (pill) {
    pill.style.opacity = '0'; /* no active item — hide pill */
  }

  /* ─────────────────────────────────────────────────────────────
     PHASE 2 — Slide to the CURRENT position
     ─────────────────────────────────────────────────────────────
     We need one layout flush between the snap and the slide.
     `void pill.offsetWidth` inside rAF forces synchronous reflow,
     committing the "from" geometry before the animation starts.
     This is more reliable than double-rAF.
  ───────────────────────────────────────────────────────────── */
  if (doAnimate && pill && nav && activeLink) {
    requestAnimationFrame(function () {

      /* Skip JS animation if the browser's native View Transition
         is already morphing the pill (Chrome 126+ with navigation: auto) */
      if (isViewTransitionActive()) {
        clearState();
        return;
      }

      /* Force layout: commits the "from" styles to the render tree */
      void pill.offsetWidth;

      /* Slide to destination */
      applyPos(pill, measure(activeLink, nav), true);

      /* Clean up sessionStorage exactly when the slide finishes */
      function onEnd(e) {
        if (e.propertyName === 'left' || e.propertyName === 'width') {
          pill.removeEventListener('transitionend', onEnd);
          clearState();
        }
      }
      pill.addEventListener('transitionend', onEnd);

      /* Safety-net: clear state after 600ms regardless (e.g. tab hidden) */
      setTimeout(clearState, DURATION * 2);
    });
  }

  /* ─────────────────────────────────────────────────────────────
     PHASE 3 — Click handlers + resize (deferred, not blocking)
  ───────────────────────────────────────────────────────────── */
  function setupHandlers() {
    if (!nav || !activeLink) return;

    /* On click: store WHERE WE ARE so the next page can slide FROM here */
    nav.querySelectorAll('a[data-nav-key]').forEach(function (link) {
      link.addEventListener('click', function () {
        var destKey = link.getAttribute('data-nav-key');
        if (destKey === currentKey) return; /* same page — nothing to animate */
        writeState({ key: currentKey, viewportWidth: window.innerWidth });
      });
    });

    /* Resize: re-snap without animation (debounced) */
    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var al = nav.querySelector('a[aria-current="page"]');
        if (al && pill) applyPos(pill, measure(al, nav), false);
      }, 80);
    });
  }

  /* Handlers need full DOM; Phase 1 already ran synchronously above */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupHandlers);
  } else {
    setupHandlers();
  }

})();
</script>
<?php $has_page_container = true; ?>
<!-- Page Content Container for Smooth Sliding Transitions -->
<div id="page-container" class="page-container flex-1 flex flex-col relative w-full overflow-x-hidden">
