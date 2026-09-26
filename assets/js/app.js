/**
 * CampusRent Hub - Main Shared Application Logic & UI Handlers
 * Tailored for Chittagong University Campuses
 */

// Storage keys
const AUTH_TOKEN_KEY = 'CRH_AUTH_TOKEN';
const CURRENT_USER_KEY = 'CRH_CURRENT_USER';
const USER_ROLE_KEY = 'CRH_USER_ROLE';
const LOGGED_OUT_KEY = 'CRH_EXPLICIT_LOGOUT';

// Authentication Service
const Auth = {
  getCurrentUser() {
    try {
      const u = localStorage.getItem(CURRENT_USER_KEY);
      return u ? JSON.parse(u) : null;
    } catch (e) {
      return null;
    }
  },

  isAuthenticated() {
    return !!localStorage.getItem(AUTH_TOKEN_KEY) && !!this.getCurrentUser();
  },

  getUserRole() {
    const user = this.getCurrentUser();
    return user ? user.role : 'guest';
  },

  login(loginInput, password) {
    const data = CampusRentData.get();
    const cleanInput = loginInput.trim().toLowerCase();

    // Match against stored users by email or studentId
    const user = data.users.find(u => 
      (u.email.toLowerCase() === cleanInput || u.studentId.toLowerCase() === cleanInput)
    );

    if (user) {
      let isValidPassword = false;
      if (user.passwordReset) {
        isValidPassword = (user.password === password);
      } else {
        isValidPassword = (!user.password || 
          user.password === password || 
          password === '123' || 
          password === 'password123' ||
          (user.role === 'admin' && password === 'admin123'));
      }

      if (!isValidPassword) {
        return { success: false, message: 'Incorrect password. (Hint: default demo password is 123)' };
      }

      const token = 'token_' + Date.now();
      localStorage.setItem(AUTH_TOKEN_KEY, token);
      localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));
      localStorage.setItem(USER_ROLE_KEY, user.role);
      localStorage.removeItem(LOGGED_OUT_KEY);

      // Sync with mock-data
      data.currentUser = user;
      CampusRentData.save(data);

      return { success: true, user };
    }

    return { success: false, message: 'Student ID or Email not recognized on campus database.' };
  },

  findUserByEmail(email) {
    const data = CampusRentData.get();
    const cleanEmail = (email || '').trim().toLowerCase();
    if (!cleanEmail) return null;
    return (data.users && data.users.find(u => u.email && u.email.toLowerCase() === cleanEmail)) || null;
  },

  updatePassword(email, newPassword) {
    const data = CampusRentData.get();
    const cleanEmail = (email || '').trim().toLowerCase();
    const user = (data.users && data.users.find(u => u.email && u.email.toLowerCase() === cleanEmail));
    if (!user) {
      return { success: false, message: 'No account associated with this email.' };
    }

    user.password = newPassword;
    user.passwordReset = true;
    CampusRentData.save(data);

    // Sync active session if currently logged in
    const current = this.getCurrentUser();
    if (current && (current.email.toLowerCase() === cleanEmail || current.id === user.id)) {
      current.password = newPassword;
      current.passwordReset = true;
      localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(current));
    }

    return { success: true, user };
  },

  register(userData) {
    const data = CampusRentData.get();
    const cleanId = (userData.studentId || '').trim().toUpperCase();
    const cleanEmail = (userData.email || `${cleanId.toLowerCase()}@campus.ac.bd`).trim().toLowerCase();

    // Check duplicate
    const exists = data.users.some(u => 
      (u.email && u.email.toLowerCase() === cleanEmail) || 
      (u.studentId && u.studentId.toUpperCase() === cleanId)
    );

    if (exists) {
      return { success: false, message: 'An account with this Student ID or Email already exists.' };
    }

    const newUser = {
      id: 'usr_' + Date.now().toString().slice(-4),
      name: userData.name.trim(),
      studentId: cleanId,
      department: userData.department || 'CSE',
      campus: userData.campus || 'Premier University, Chittagong',
      campusShort: userData.campusShort || 'PUC',
      role: userData.role || 'renter',
      phone: userData.phone || '+8801700-000000',
      email: cleanEmail,
      password: userData.password || '123',
      trustScore: 5.0,
      verifiedStatus: 'Verified Student',
      ratingCount: 1,
      avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'
    };

    data.users.push(newUser);
    CampusRentData.save(data);

    // Auto-login
    this.login(newUser.studentId, newUser.password);

    return { success: true, user: newUser };
  },

  logout() {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(CURRENT_USER_KEY);
    localStorage.removeItem(USER_ROLE_KEY);
    localStorage.setItem(LOGGED_OUT_KEY, 'true');

    const data = CampusRentData.get();
    data.currentUser = null;
    CampusRentData.save(data);

    showToast('You have been signed out successfully.', 'info');
    setTimeout(() => {
      if (typeof window !== 'undefined' && window.location) {
        window.location.href = 'index.html';
      }
    }, 400);
  },

  initSession() {
    // Default initial launch state is completely unauthenticated (null)
    // No automatic login for guest visitors
  }
};

// Document Lifecycle Initialization
document.addEventListener('DOMContentLoaded', () => {
  Auth.initSession();
  initMobileMenu();
  initModalListeners();
  initEscKeyClose();
  renderNotificationCounts();
  ensureAuthModalDOM();
  ensureProfileModalDOM();
  initDynamicHeader();
  initProfileDropdown();
});

// Format currency in Bangladeshi Taka
function formatBDT(amount) {
  return '৳' + Number(amount || 0).toLocaleString('en-IN');
}

// Global Toast Notification Engine
function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full px-4 pointer-events-none';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-xl shadow-xl text-sm font-medium border backdrop-blur-md transition-all duration-300 transform translate-y-8 opacity-0';

  let iconSvg = '';
  let colorStyles = '';

  switch (type) {
    case 'success':
      colorStyles = 'bg-emerald-900/90 border-emerald-500 text-white';
      iconSvg = `<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
      break;
    case 'warning':
      colorStyles = 'bg-amber-900/90 border-amber-500 text-white';
      iconSvg = `<svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
      break;
    case 'error':
      colorStyles = 'bg-red-900/90 border-red-500 text-white';
      iconSvg = `<svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
      break;
    default:
      colorStyles = 'bg-slate-900/95 border-blue-500 text-white';
      iconSvg = `<svg class="w-5 h-5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
  }

  toast.className += ` ${colorStyles}`;
  toast.innerHTML = `
    ${iconSvg}
    <div class="flex-1">${message}</div>
    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
  `;

  container.appendChild(toast);

  requestAnimationFrame(() => {
    toast.classList.remove('translate-y-8', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
  });

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-4');
    setTimeout(() => toast.remove(), 300);
  }, 4200);
}

// Modal Controllers
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.remove('hidden');
  document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
}

function initModalListeners() {
  document.querySelectorAll('[data-modal-target]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const target = btn.getAttribute('data-modal-target');
      openModal(target);
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-container');
      if (modal) closeModal(modal.id);
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) {
        const modal = backdrop.closest('.modal-container');
        if (modal) closeModal(modal.id);
      }
    });
  });
}

function initEscKeyClose() {
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-container:not(.hidden)').forEach(modal => {
        closeModal(modal.id);
      });
      closeProfileDropdown();
    }
  });
}

// Mobile Navbar Toggle
function initMobileMenu() {
  const toggleBtn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (toggleBtn && menu) {
    toggleBtn.addEventListener('click', () => {
      menu.classList.toggle('hidden');
    });
  }
}

// Copy to Clipboard
function copyToClipboard(text, successMsg = 'Copied to clipboard!') {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(() => {
      showToast(successMsg, 'success');
    }).catch(() => {
      fallbackCopy(text, successMsg);
    });
  } else {
    fallbackCopy(text, successMsg);
  }
}

function fallbackCopy(text, successMsg) {
  const el = document.createElement('textarea');
  el.value = text;
  document.body.appendChild(el);
  el.select();
  document.execCommand('copy');
  document.body.removeChild(el);
  showToast(successMsg, 'success');
}

// Notification counters
function renderNotificationCounts() {
  const notifDots = document.querySelectorAll('.notification-dot');
  const user = Auth.getCurrentUser();
  const isAuth = Auth.isAuthenticated();
  const data = CampusRentData.get();

  let count = 0;
  if (isAuth && user && data.rentals) {
    count = data.rentals.filter(r => r.statusCode === 'ready_handover' || r.statusCode === 'active').length;
  }

  notifDots.forEach(dot => {
    if (count > 0) {
      dot.textContent = count.toString();
      dot.classList.remove('hidden');
    } else {
      dot.textContent = '0';
      dot.classList.add('hidden');
    }
  });
}

// ==============================================================================
// Route-Aware Navigation Active State Engine
// ==============================================================================
function getRouteFromPath(path) {
  if (!path) return '';
  const cleanPath = path.split('?')[0].split('#')[0].toLowerCase();
  
  if (cleanPath.includes('dashboard.php') && !cleanPath.includes('/admin/')) {
    return 'dashboard';
  }
  if (cleanPath.includes('equipment.php') || cleanPath.includes('add_item.php')) {
    return 'equipment';
  }
  if (cleanPath.includes('rentals.php')) {
    return 'rentals';
  }
  if (cleanPath.includes('exchanges.php')) {
    return 'exchanges';
  }
  if (
    cleanPath.includes('index.php') || 
    cleanPath.endsWith('/rentora') || 
    cleanPath.endsWith('/rentora/') || 
    cleanPath === '/' || 
    cleanPath.endsWith('/index.html') ||
    cleanPath.includes('item-details.php')
  ) {
    return 'browse';
  }
  return '';
}

function getLinkRoute(link) {
  const href = (link.getAttribute('href') || '').toLowerCase();
  const text = (link.textContent || '').trim().toLowerCase();

  if (href.includes('dashboard.php') && !href.includes('/admin/')) return 'dashboard';
  if (href.includes('equipment.php')) return 'equipment';
  if (href.includes('rentals.php')) return 'rentals';
  if (href.includes('exchanges.php')) return 'exchanges';
  if (href.includes('index.php')) return 'browse';

  // Fallback text check
  if (text === 'dashboard') return 'dashboard';
  if (text.includes('my equipment')) return 'equipment';
  if (text.includes('rentals')) return 'rentals';
  if (text.includes('exchanges')) return 'exchanges';
  if (text.includes('browse equipment')) return 'browse';

  return '';
}

function updateNavbarActiveState(targetUrl) {
  const nav = document.querySelector('header nav');
  if (!nav) return;

  const currentPath = targetUrl || window.location.pathname || window.location.href;
  const activeRoute = getRouteFromPath(currentPath);

  const activeClasses = ['font-semibold', 'text-primary-600', 'hover:bg-blue-50/80'];
  const inactiveClasses = ['font-medium', 'text-slate-700', 'hover:text-primary-600', 'hover:bg-slate-100'];

  const links = nav.querySelectorAll('a');
  links.forEach(link => {
    const linkRoute = getLinkRoute(link);
    if (!linkRoute) return;

    if (activeRoute && linkRoute === activeRoute) {
      inactiveClasses.forEach(cls => link.classList.remove(cls));
      activeClasses.forEach(cls => {
        if (!link.classList.contains(cls)) {
          link.classList.add(cls);
        }
      });
      link.setAttribute('aria-current', 'page');
    } else {
      activeClasses.forEach(cls => link.classList.remove(cls));
      inactiveClasses.forEach(cls => {
        if (!link.classList.contains(cls)) {
          link.classList.add(cls);
        }
      });
      link.removeAttribute('aria-current');
    }
  });
}

// ==============================================================================
// Page Transition Engine
// ==============================================================================

/**
 * Load a new page via AJAX and apply slide transition.
 * @param {string} targetUrl - URL to navigate to (relative or absolute).
 * @param {boolean} addToHistory - Whether to push a new history entry (false for popstate handling).
 */
function navigateTo(targetUrl, addToHistory = true) {
  const container = document.getElementById('page-container');
  if (!container) return;

  // Resolve relative URLs against current location
  const resolvedUrl = new URL(targetUrl, window.location.origin).href;

  fetch(resolvedUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(resp => resp.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newContainer = doc.getElementById('page-container');
      if (!newContainer) {
        // Fallback to normal navigation if container missing
        if (addToHistory) window.location.href = targetUrl;
        return;
      }

      // Prepare incoming panel off‑screen to the right
      const incoming = document.createElement('div');
      incoming.id = 'page-container';
      incoming.className = 'page-slide-panel slide-prep-right';
      incoming.innerHTML = newContainer.innerHTML;

      // Insert after current panel
      container.parentNode.appendChild(incoming);

      // Trigger animations on next frame
      requestAnimationFrame(() => {
        container.classList.add('slide-animating', 'slide-out-left');
        incoming.classList.add('slide-animating', 'slide-in-center');
      });

      const onAnimEnd = (e) => {
        // Cleanup old panel
        if (container && container.parentNode) {
          container.parentNode.removeChild(container);
        }
        // Reset incoming panel classes to regular page container
        incoming.className = 'page-container flex-1 flex flex-col relative w-full overflow-x-hidden';
        // Re‑initialise dynamic UI (navbar active state, etc.)
        if (typeof initDynamicHeader === 'function') initDynamicHeader();
        // Push history if required
        if (addToHistory) {
          window.history.pushState(null, '', targetUrl);
        }
        incoming.removeEventListener('transitionend', onAnimEnd);
      };

      // Listen for the end of the incoming animation
      incoming.addEventListener('transitionend', onAnimEnd);
    })
    .catch(err => {
      console.error('Navigation error:', err);
      // Fallback to full navigation on error
      if (addToHistory) window.location.href = targetUrl;
    });
}

// Extend initNavbarRouting to use navigateTo for internal clicks and back/forward navigation
function initNavbarRouting() {
  updateNavbarActiveState();

  // Listen to popstate and hashchange events (back/forward or hash changes)
  window.addEventListener('popstate', () => {
    const url = window.location.pathname + window.location.search;
    updateNavbarActiveState();
    navigateTo(url, false); // load without pushing another state
  });
  window.addEventListener('hashchange', () => {
    updateNavbarActiveState();
  });

  // Intercept history.pushState and history.replaceState for in-browser client navigation
  if (window.history && typeof window.history.pushState === 'function') {
    const originalPushState = window.history.pushState;
    if (!originalPushState._isIntercepted) {
      window.history.pushState = function(...args) {
        const res = originalPushState.apply(this, args);
        updateNavbarActiveState();
        return res;
      };
      window.history.pushState._isIntercepted = true;
    }
  }

  if (window.history && typeof window.history.replaceState === 'function') {
    const originalReplaceState = window.history.replaceState;
    if (!originalReplaceState._isIntercepted) {
      window.history.replaceState = function(...args) {
        const res = originalReplaceState.apply(this, args);
        updateNavbarActiveState();
        return res;
      };
      window.history.replaceState._isIntercepted = true;
    }
  }

  // Intercept clicks on header navigation links to update active state & animate transition
  const nav = document.querySelector('header nav');
  if (nav && !nav._activeClickAttached) {
    nav.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (link && nav.contains(link)) {
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
          e.preventDefault(); // stop full page navigation
          updateNavbarActiveState(href);
          navigateTo(href);
        }
      }
    });
    nav._activeClickAttached = true;
  }
}

if (typeof window !== 'undefined') {
  window.updateNavbarActiveState = updateNavbarActiveState;
  window.initNavbarRouting = initNavbarRouting;
}

// ==============================================================================
// Dynamic Navigation Header & Profile Dropdown Engine
// ==============================================================================
function renderUserAvatarHTML(user, sizeClass = "w-8 h-8", textClass = "text-xs") {
  if (user && user.avatar && user.avatar.trim() !== '') {
    return `<img src="${user.avatar}" alt="${user.name || 'User'}" class="${sizeClass} rounded-full ring-2 ring-primary-600/30 object-cover shrink-0">`;
  }
  const initials = (user && user.name) 
    ? user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() 
    : 'U';
  return `<div class="${sizeClass} rounded-full bg-gradient-to-tr from-primary-700 to-blue-500 text-white flex items-center justify-center font-bold ${textClass} ring-2 ring-primary-600/30 shrink-0 shadow-sm">${initials}</div>`;
}

function initDynamicHeader() {
  initNavbarRouting();
  renderNotificationCounts();
  const container = document.getElementById('header-auth-container') || document.querySelector('.header-profile-section');
  const mobileMenu = document.getElementById('mobile-menu');
  const user = Auth.getCurrentUser();
  const isAuth = Auth.isAuthenticated();

  // Desktop navigation links: hide internal dashboard links for guests
  const desktopNav = document.querySelector('header nav');
  if (desktopNav) {
    const links = desktopNav.querySelectorAll('a');
    links.forEach(link => {
      const href = link.getAttribute('href') || '';
      if (href.includes('owner-dashboard') || href.includes('renter-dashboard') || href.includes('admin')) {
        if (isAuth && user) {
          link.classList.remove('hidden');
          link.classList.add('flex');
        } else {
          link.classList.add('hidden');
          link.classList.remove('flex');
        }
      }
    });
  }

  if (container) {
    if (isAuth && user) {
      // Authenticated State: Interactive Profile Dropdown Trigger
      container.innerHTML = `
        <div class="relative">
          <button id="profile-dropdown-btn" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-600/30">
            ${renderUserAvatarHTML(user, 'w-8 h-8', 'text-xs')}
            <div class="text-left leading-tight hidden lg:block">
              <div class="text-xs font-bold text-navy-900">${user.name}</div>
              <div class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                <span>${user.studentId}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              </div>
            </div>
            <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>

          <!-- Floating Profile Dropdown Menu -->
          <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
              <div class="flex items-center gap-2.5 mb-1.5">
                ${renderUserAvatarHTML(user, 'w-9 h-9', 'text-sm')}
                <div class="overflow-hidden">
                  <p class="text-xs font-bold text-navy-900 truncate">${user.name}</p>
                  <p class="text-[11px] text-slate-500 font-mono truncate">${user.studentId}</p>
                </div>
              </div>
              <p class="text-[11px] text-slate-500 truncate">${user.email}</p>
              <div class="mt-2 flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-primary-800 text-[10px] font-bold">${user.campusShort || 'CTG'}</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">${user.role}</span>
              </div>
            </div>

            <div class="py-1 text-xs">
              <a href="renter-dashboard.html" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-primary-700 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span>My Rentals & Active Tokens</span>
              </a>
              <a href="owner-dashboard.html" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-blue-50 hover:text-primary-700 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Lender Hub & Listings</span>
              </a>
              <a href="admin.html" class="flex items-center gap-2.5 px-4 py-2 text-amber-700 hover:bg-amber-50 transition-colors">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span>Admin Operations Console</span>
              </a>
              <button type="button" onclick="openProfileModal()" class="w-full flex items-center gap-2.5 px-4 py-2 text-primary-700 hover:bg-blue-50 transition-colors text-left">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>Edit Profile & Photo</span>
              </button>
            </div>

            <div class="border-t border-slate-100 pt-1">
              <button onclick="Auth.logout()" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Sign Out</span>
              </button>
            </div>
          </div>
        </div>
      `;
    } else {
      // Guest State: Sign In & Register buttons
      container.innerHTML = `
        <div class="flex items-center gap-2">
          <button onclick="openAuthModal('signin')" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-primary-600 transition-colors">
            Sign In
          </button>
          <button onclick="openAuthModal('register')" class="px-3.5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition-all">
            Register
          </button>
        </div>
      `;
    }
  }

  // Update Mobile Navigation Menu
  if (mobileMenu) {
    const mobileAuthSlot = mobileMenu.querySelector('.mobile-auth-slot');
    if (mobileAuthSlot) {
      if (isAuth && user) {
        mobileAuthSlot.innerHTML = `
          <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-xl mb-2">
            ${renderUserAvatarHTML(user, 'w-9 h-9', 'text-sm')}
            <div>
              <div class="text-sm font-bold text-navy-900">${user.name}</div>
              <div class="text-xs text-emerald-600 font-semibold">${user.studentId} &bull; ${user.campusShort}</div>
            </div>
          </div>
          <button onclick="openProfileModal()" class="w-full text-left px-3 py-2 text-sm font-medium text-primary-600 hover:bg-blue-50 rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span>Edit Profile & Photo</span>
          </button>
          <button onclick="Auth.logout()" class="w-full text-left px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-lg">Sign Out</button>
        `;
      } else {
        mobileAuthSlot.innerHTML = `
          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
            <button onclick="openAuthModal('signin')" class="py-2 px-3 text-xs font-bold bg-slate-100 text-slate-800 rounded-lg text-center">Sign In</button>
            <button onclick="openAuthModal('register')" class="py-2 px-3 text-xs font-bold bg-primary-600 text-white rounded-lg text-center">Register</button>
          </div>
        `;
      }
    }

    // Hide internal links on mobile for guests
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
      const href = link.getAttribute('href') || '';
      if (href.includes('owner-dashboard') || href.includes('renter-dashboard') || href.includes('admin')) {
        if (isAuth && user) {
          link.classList.remove('hidden');
        } else {
          link.classList.add('hidden');
        }
      }
    });
  }
}

function initProfileDropdown() {
  document.addEventListener('click', (e) => {
    const btn = document.getElementById('profile-dropdown-btn');
    const menu = document.getElementById('profile-dropdown-menu');

    if (!btn || !menu) return;

    if (btn.contains(e.target)) {
      e.stopPropagation();
      menu.classList.toggle('hidden');
    } else if (!menu.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });
}

function closeProfileDropdown() {
  const menu = document.getElementById('profile-dropdown-menu');
  if (menu) menu.classList.add('hidden');
}

// ==============================================================================
// Universal Interactive Auth Modal
// ==============================================================================
function ensureAuthModalDOM() {
  if (document.getElementById('auth-modal')) return;

  const modal = document.createElement('div');
  modal.id = 'auth-modal';
  modal.className = 'modal-container hidden fixed inset-0 z-50 flex items-center justify-center p-4';
  modal.innerHTML = `
    <div class="modal-backdrop fixed inset-0 bg-navy-950/80 backdrop-blur-sm"></div>
    
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden z-10 border border-slate-200">
      
      <!-- Modal Header -->
      <div class="bg-navy-900 p-5 text-white flex justify-between items-center">
        <div>
          <h3 id="auth-modal-title" class="font-extrabold text-base">Chittagong Campus Portal</h3>
          <p id="auth-modal-subtitle" class="text-xs text-slate-300">PUC &bull; IIUC &bull; CUET &bull; CU Unified Hub</p>
        </div>
        <button onclick="closeModal('auth-modal')" class="text-slate-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <!-- Tab Switcher -->
      <div id="auth-tab-bar" class="flex border-b border-slate-200 bg-slate-50 text-xs font-bold">
        <button id="tab-signin-btn" onclick="switchAuthTab('signin')" class="flex-1 py-3 text-center border-b-2 border-primary-600 text-primary-600 bg-white">
          Sign In
        </button>
        <button id="tab-register-btn" onclick="switchAuthTab('register')" class="flex-1 py-3 text-center text-slate-500 hover:text-navy-900">
          Create Account
        </button>
      </div>

      <div class="p-6">
        
        <!-- Sign In Form -->
        <form id="auth-signin-form" onsubmit="handleAuthSignIn(event)" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Student ID or Campus Email</label>
            <input type="text" id="auth_login_input" required placeholder="e.g. PUC-22-0145 or CUET-21-0342" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:ring-2 focus:ring-primary-600">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password</label>
            <input type="password" id="auth_password_input" required placeholder="Enter password" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:ring-2 focus:ring-primary-600">
          </div>

          <button type="submit" class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all">
            Sign In
          </button>

          <!-- Quick Test Demo Logins -->
          <div class="pt-3 border-t border-slate-200">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center mb-2">⚡ 1-Click Chittagong Demo Logins</span>
            <div class="grid grid-cols-3 gap-1.5">
              <button type="button" onclick="quickFillAuth('PUC-22-0145', '123')" class="py-1 px-1.5 bg-slate-100 hover:bg-blue-50 hover:text-primary-700 text-slate-700 font-semibold rounded-lg text-[10px] border border-slate-200">
                PUC Renter
              </button>
              <button type="button" onclick="quickFillAuth('CUET-21-0342', '123')" class="py-1 px-1.5 bg-slate-100 hover:bg-blue-50 hover:text-primary-700 text-slate-700 font-semibold rounded-lg text-[10px] border border-slate-200">
                CUET Owner
              </button>
              <button type="button" onclick="quickFillAuth('ADM-CTG-001', 'admin123')" class="py-1 px-1.5 bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-700 font-semibold rounded-lg text-[10px] border border-slate-200">
                Admin
              </button>
            </div>
          </div>
        </form>

        <!-- Register Form (Hidden by default) -->
        <form id="auth-register-form" onsubmit="handleAuthRegister(event)" class="space-y-3 hidden">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
            <input type="text" id="reg_name" required placeholder="e.g. Shakil Chowdhury" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Student ID *</label>
              <input type="text" id="reg_student_id" required placeholder="CUET-23-0199" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800 font-mono uppercase">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Role *</label>
              <select id="reg_role" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
                <option value="renter">Student Renter</option>
                <option value="owner">Equipment Owner</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">University Campus *</label>
            <select id="reg_campus" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
              <option value="Premier University, Chittagong">Premier University, Chittagong (PUC)</option>
              <option value="Chittagong University of Engineering and Technology (CUET)">CUET</option>
              <option value="International Islamic University Chittagong (IIUC)">IIUC</option>
              <option value="University of Chittagong (CU)">University of Chittagong (CU)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Campus Email *</label>
            <input type="email" id="reg_email" required placeholder="student@cuet.ac.bd" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
            <input type="password" id="reg_password" required placeholder="Create password" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
          </div>

          <button type="submit" class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition-all">
            Create Account
          </button>
        </form>

      </div>
    </div>
  `;
  document.body.appendChild(modal);
}

function openAuthModal(tab = 'signin') {
  ensureAuthModalDOM();
  switchAuthTab(tab);
  openModal('auth-modal');
}

function switchAuthTab(tab) {
  const signinForm = document.getElementById('auth-signin-form');
  const registerForm = document.getElementById('auth-register-form');
  const signinBtn = document.getElementById('tab-signin-btn');
  const registerBtn = document.getElementById('tab-register-btn');

  if (tab === 'signin') {
    if (signinForm) signinForm.classList.remove('hidden');
    if (registerForm) registerForm.classList.add('hidden');
    if (signinBtn) signinBtn.className = 'flex-1 py-3 text-center border-b-2 border-primary-600 text-primary-600 bg-white font-bold';
    if (registerBtn) registerBtn.className = 'flex-1 py-3 text-center text-slate-500 hover:text-navy-900 font-medium';
  } else {
    if (signinForm) signinForm.classList.add('hidden');
    if (registerForm) registerForm.classList.remove('hidden');
    if (registerBtn) registerBtn.className = 'flex-1 py-3 text-center border-b-2 border-primary-600 text-primary-600 bg-white font-bold';
    if (signinBtn) signinBtn.className = 'flex-1 py-3 text-center text-slate-500 hover:text-navy-900 font-medium';
  }
}

function quickFillAuth(login, pass) {
  document.getElementById('auth_login_input').value = login;
  document.getElementById('auth_password_input').value = pass;
}

function handleAuthSignIn(e) {
  e.preventDefault();
  const login = document.getElementById('auth_login_input').value;
  const pass = document.getElementById('auth_password_input').value;

  const res = Auth.login(login, pass);
  if (res.success) {
    closeModal('auth-modal');
    showToast(`Welcome back, ${res.user.name}! (${res.user.campusShort})`, 'success');
    initDynamicHeader();

    // Redirect based on role if on login page
    if (window.location.pathname.includes('signin.html')) {
      if (res.user.role === 'owner') window.location.href = 'owner-dashboard.html';
      else if (res.user.role === 'admin') window.location.href = 'admin.html';
      else window.location.href = 'renter-dashboard.html';
    }
  } else {
    showToast(res.message, 'error');
  }
}

function handleAuthRegister(e) {
  e.preventDefault();
  const name = document.getElementById('reg_name').value;
  const studentId = document.getElementById('reg_student_id').value;
  const role = document.getElementById('reg_role').value;
  const campus = document.getElementById('reg_campus').value;
  const email = document.getElementById('reg_email').value;
  const password = document.getElementById('reg_password').value;

  let campusShort = 'PUC';
  if (campus.includes('CUET')) campusShort = 'CUET';
  else if (campus.includes('IIUC')) campusShort = 'IIUC';
  else if (campus.includes('University of Chittagong')) campusShort = 'CU';

  const res = Auth.register({ name, studentId, role, campus, campusShort, email, password });
  if (res.success) {
    closeModal('auth-modal');
    showToast(`Registration successful! Welcome to CampusRent Hub, ${res.user.name}.`, 'success');
    initDynamicHeader();
  } else {
    showToast(res.message, 'error');
  }
}

// ==============================================================================
// Universal Interactive Profile & Avatar Settings Modal
// ==============================================================================
window._stagedAvatarBase64 = null;

function ensureProfileModalDOM() {
  if (document.getElementById('profile-settings-modal')) return;

  const modal = document.createElement('div');
  modal.id = 'profile-settings-modal';
  modal.className = 'modal-container hidden fixed inset-0 z-50 flex items-center justify-center p-4';
  modal.innerHTML = `
    <div class="modal-backdrop fixed inset-0 bg-navy-950/80 backdrop-blur-sm"></div>
    
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden z-10 border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
      
      <!-- Modal Header -->
      <div class="bg-navy-900 p-5 text-white flex justify-between items-center">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
          <div>
            <h3 class="font-bold text-base">Campus Profile & Photo</h3>
            <p class="text-xs text-slate-300">Customize your student avatar & credentials</p>
          </div>
        </div>
        <button onclick="closeModal('profile-settings-modal')" class="text-slate-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <!-- Tab Switcher -->
      <div class="flex border-b border-slate-200 bg-slate-50 text-xs font-bold">
        <button id="profile-tab-btn-general" type="button" onclick="switchProfileTab('general')" class="flex-1 py-3 text-center border-b-2 border-primary-600 text-primary-600 bg-white flex items-center justify-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          <span>Profile & Avatar</span>
        </button>
        <button id="profile-tab-btn-security" type="button" onclick="switchProfileTab('security')" class="flex-1 py-3 text-center text-slate-500 hover:text-navy-900 flex items-center justify-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          <span>Security & Password</span>
        </button>
      </div>

      <!-- Tab Panel 1: Profile & Avatar Form -->
      <div id="profile-tab-panel-general">
        <form id="profile-settings-form" onsubmit="handleSaveProfile(event)" class="p-6 space-y-4">
          
          <!-- Profile Picture Upload Area -->
          <div class="flex flex-col items-center justify-center text-center pb-3 border-b border-slate-100">
            <div class="relative group cursor-pointer mb-3" onclick="document.getElementById('profile-avatar-file-input').click()">
              <div id="profile-modal-avatar-preview" class="w-20 h-20 rounded-full ring-4 ring-primary-100 overflow-hidden bg-slate-100 flex items-center justify-center shadow-inner">
                <!-- Dynamic Avatar Preview -->
              </div>
              <div class="absolute inset-0 bg-navy-950/60 rounded-full flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-[10px] font-bold">Change</span>
              </div>
            </div>

            <input type="file" id="profile-avatar-file-input" accept="image/*" class="hidden" onchange="handleProfilePhotoSelected(event)">

            <div class="flex items-center gap-2">
              <button type="button" onclick="document.getElementById('profile-avatar-file-input').click()" class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-primary-700 text-xs font-bold transition-colors">
                Upload Photo
              </button>
              <button type="button" onclick="handleRemoveProfilePhoto()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 text-xs font-semibold transition-colors">
                Remove Photo
              </button>
            </div>
            <span class="text-[11px] text-slate-400 mt-1.5">PNG, JPG or WebP. Automatically stored in active session.</span>
          </div>

          <!-- Name & Student ID -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label for="profile_name" class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
              <input type="text" id="profile_name" name="name" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-primary-600">
            </div>
            <div>
              <label for="profile_student_id" class="block text-xs font-bold text-slate-700 mb-1">Student ID</label>
              <input type="text" id="profile_student_id" name="studentId" readonly class="w-full px-3 py-2 text-xs bg-slate-100 border border-slate-200 rounded-xl font-mono text-slate-500 cursor-not-allowed">
            </div>
          </div>

          <!-- Campus & Department -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label for="profile_campus" class="block text-xs font-bold text-slate-700 mb-1">Campus</label>
              <input type="text" id="profile_campus" name="campus" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
            </div>
            <div>
              <label for="profile_dept" class="block text-xs font-bold text-slate-700 mb-1">Department</label>
              <input type="text" id="profile_dept" name="department" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl text-slate-800">
            </div>
          </div>

          <!-- Phone -->
          <div>
            <label for="profile_phone" class="block text-xs font-bold text-slate-700 mb-1">Contact Phone</label>
            <input type="tel" id="profile_phone" name="phone" placeholder="+88017..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800">
          </div>

          <!-- Submit Button -->
          <div class="pt-2 flex gap-2">
            <button type="button" onclick="closeModal('profile-settings-modal')" class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
              Cancel
            </button>
            <button type="submit" class="flex-1 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-md transition-all">
              Save Changes
            </button>
          </div>

        </form>
      </div>

      <!-- Tab Panel 2: Security & Password Form -->
      <div id="profile-tab-panel-security" class="p-6 space-y-4 hidden">
        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
          <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-navy-900">Change Account Password</h4>
            <p class="text-xs text-slate-500">Update your student credentials to keep your account protected</p>
          </div>
        </div>

        <div id="profile-password-error" class="hidden p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs font-medium flex items-center gap-2">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span id="profile-password-error-text">Error message</span>
        </div>

        <form id="profile-change-password-form" onsubmit="handleProfileChangePassword(event)" class="space-y-4">
          <!-- Current Password -->
          <div>
            <label for="profile_current_password" class="block text-xs font-bold text-slate-700 mb-1">Current Password *</label>
            <div class="relative">
              <input type="password" id="profile_current_password" required placeholder="Enter current password" class="w-full pl-3 pr-10 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:ring-2 focus:ring-primary-600">
              <button type="button" onclick="togglePasswordVisibility('profile_current_password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600" title="Toggle password visibility">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              </button>
            </div>
          </div>

          <!-- New Password -->
          <div>
            <label for="profile_new_password" class="block text-xs font-bold text-slate-700 mb-1">New Password (Min 6 chars) *</label>
            <div class="relative">
              <input type="password" id="profile_new_password" required minlength="6" placeholder="Choose new password" class="w-full pl-3 pr-10 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:ring-2 focus:ring-primary-600">
              <button type="button" onclick="togglePasswordVisibility('profile_new_password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600" title="Toggle password visibility">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              </button>
            </div>
          </div>

          <!-- Confirm New Password -->
          <div>
            <label for="profile_confirm_password" class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password *</label>
            <div class="relative">
              <input type="password" id="profile_confirm_password" required minlength="6" placeholder="Re-enter new password" class="w-full pl-3 pr-10 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:ring-2 focus:ring-primary-600">
              <button type="button" onclick="togglePasswordVisibility('profile_confirm_password', this)" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600" title="Toggle password visibility">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-2 flex gap-2">
            <button type="button" onclick="closeModal('profile-settings-modal')" class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
              Cancel
            </button>
            <button type="submit" class="flex-1 py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-md transition-all">
              Update Password
            </button>
          </div>
        </form>
      </div>

    </div>
  `;

  document.body.appendChild(modal);
}

function switchProfileTab(tab) {
  const generalPanel = document.getElementById('profile-tab-panel-general');
  const securityPanel = document.getElementById('profile-tab-panel-security');
  const generalBtn = document.getElementById('profile-tab-btn-general');
  const securityBtn = document.getElementById('profile-tab-btn-security');

  // Clear errors and password inputs
  const errBox = document.getElementById('profile-password-error');
  if (errBox) errBox.classList.add('hidden');
  const curPass = document.getElementById('profile_current_password');
  const newPass = document.getElementById('profile_new_password');
  const confPass = document.getElementById('profile_confirm_password');
  if (curPass) curPass.value = '';
  if (newPass) newPass.value = '';
  if (confPass) confPass.value = '';

  if (tab === 'security') {
    if (generalPanel) generalPanel.classList.add('hidden');
    if (securityPanel) securityPanel.classList.remove('hidden');
    if (securityBtn) securityBtn.className = 'flex-1 py-3 text-center border-b-2 border-primary-600 text-primary-600 bg-white flex items-center justify-center gap-1.5 font-bold';
    if (generalBtn) generalBtn.className = 'flex-1 py-3 text-center text-slate-500 hover:text-navy-900 flex items-center justify-center gap-1.5 font-medium';
    if (curPass) setTimeout(() => curPass.focus(), 50);
  } else {
    if (generalPanel) generalPanel.classList.remove('hidden');
    if (securityPanel) securityPanel.classList.add('hidden');
    if (generalBtn) generalBtn.className = 'flex-1 py-3 text-center border-b-2 border-primary-600 text-primary-600 bg-white flex items-center justify-center gap-1.5 font-bold';
    if (securityBtn) securityBtn.className = 'flex-1 py-3 text-center text-slate-500 hover:text-navy-900 flex items-center justify-center gap-1.5 font-medium';
  }
}

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = `<svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>`;
  } else {
    input.type = 'password';
    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>`;
  }
}

function handleProfileChangePassword(e) {
  e.preventDefault();
  const user = Auth.getCurrentUser();
  if (!user) {
    showToast('You must be signed in to change your password.', 'error');
    return;
  }

  const curPassInput = document.getElementById('profile_current_password');
  const newPassInput = document.getElementById('profile_new_password');
  const confirmPassInput = document.getElementById('profile_confirm_password');
  const errBox = document.getElementById('profile-password-error');
  const errText = document.getElementById('profile-password-error-text');

  const currentPass = (curPassInput ? curPassInput.value : '').trim();
  const newPass = (newPassInput ? newPassInput.value : '');
  const confirmPass = (confirmPassInput ? confirmPassInput.value : '');

  const showError = (msg) => {
    if (errBox) errBox.classList.remove('hidden');
    if (errText) errText.textContent = msg;
  };

  // 1. Verify Current Password
  let isCurrentValid = false;
  if (user.passwordReset) {
    isCurrentValid = (user.password === currentPass);
  } else {
    isCurrentValid = (!user.password || 
      user.password === currentPass || 
      currentPass === '123' || 
      currentPass === 'password123' || 
      (user.role === 'admin' && currentPass === 'admin123'));
  }

  if (!isCurrentValid) {
    showError('Incorrect current password.');
    if (curPassInput) curPassInput.focus();
    return;
  }

  // 2. Validate New Password length
  if (!newPass || newPass.length < 6) {
    showError('New password must be at least 6 characters.');
    if (newPassInput) newPassInput.focus();
    return;
  }

  // 3. New password must be different
  if (newPass === currentPass) {
    showError('New password must be different from current password.');
    if (newPassInput) newPassInput.focus();
    return;
  }

  // 4. Confirm match
  if (newPass !== confirmPass) {
    showError('Confirm password does not match new password.');
    if (confirmPassInput) confirmPassInput.focus();
    return;
  }

  // 5. Update password
  if (errBox) errBox.classList.add('hidden');
  const data = CampusRentData.get();
  const uIdx = data.users.findIndex(u => u.studentId === user.studentId || u.id === user.id);
  if (uIdx !== -1) {
    data.users[uIdx].password = newPass;
    data.users[uIdx].passwordReset = true;
  }
  user.password = newPass;
  user.passwordReset = true;
  data.currentUser = user;
  CampusRentData.save(data);
  localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));

  // Clear fields
  if (curPassInput) curPassInput.value = '';
  if (newPassInput) newPassInput.value = '';
  if (confirmPassInput) confirmPassInput.value = '';

  showToast('Password updated successfully!', 'success');
  switchProfileTab('general');
}

function openProfileModal() {
  closeProfileDropdown();
  const user = Auth.getCurrentUser();
  if (!user) {
    openAuthModal('signin');
    return;
  }

  ensureProfileModalDOM();
  switchProfileTab('general');
  window._stagedAvatarBase64 = user.avatar || '';

  const nameInput = document.getElementById('profile_name');
  const idInput = document.getElementById('profile_student_id');
  const campusInput = document.getElementById('profile_campus');
  const deptInput = document.getElementById('profile_dept');
  const phoneInput = document.getElementById('profile_phone');

  if (nameInput) nameInput.value = user.name || '';
  if (idInput) idInput.value = user.studentId || '';
  if (campusInput) campusInput.value = user.campus || '';
  if (deptInput) deptInput.value = user.department || '';
  if (phoneInput) phoneInput.value = user.phone || '';

  renderProfileModalAvatarPreview();
  openModal('profile-settings-modal');
}

function closeProfileModal() {
  closeModal('profile-settings-modal');
}

function renderProfileModalAvatarPreview() {
  const container = document.getElementById('profile-modal-avatar-preview');
  if (!container) return;
  const user = Auth.getCurrentUser();
  const avatarUrl = window._stagedAvatarBase64;
  if (avatarUrl && avatarUrl.trim() !== '') {
    container.innerHTML = `<img src="${avatarUrl}" alt="Avatar Preview" class="w-full h-full object-cover">`;
  } else {
    const initials = user && user.name ? user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U';
    container.innerHTML = `<div class="w-full h-full bg-gradient-to-tr from-primary-700 to-blue-500 text-white flex items-center justify-center font-bold text-xl">${initials}</div>`;
  }
}

function handleProfilePhotoSelected(e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;

  if (file.size > 5 * 1024 * 1024) {
    showToast('Please select an image smaller than 5MB.', 'warning');
    return;
  }

  const reader = new FileReader();
  reader.onload = function(event) {
    window._stagedAvatarBase64 = event.target.result;
    renderProfileModalAvatarPreview();
  };
  reader.readAsDataURL(file);
}

function handleRemoveProfilePhoto() {
  window._stagedAvatarBase64 = '';
  renderProfileModalAvatarPreview();
}

function handleSaveProfile(e) {
  e.preventDefault();
  const user = Auth.getCurrentUser();
  if (!user) return;

  const form = document.getElementById('profile-settings-form');
  const formData = new FormData(form);

  user.name = (formData.get('name') || user.name).trim();
  user.campus = (formData.get('campus') || user.campus).trim();
  user.department = (formData.get('department') || user.department).trim();
  user.phone = (formData.get('phone') || user.phone).trim();
  user.avatar = window._stagedAvatarBase64 !== null ? window._stagedAvatarBase64 : (user.avatar || '');

  // Save to localStorage
  localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));

  // Save to CampusRentData
  const data = CampusRentData.get();
  data.currentUser = user;
  const userIdx = data.users.findIndex(u => u.studentId === user.studentId || u.id === user.id);
  if (userIdx !== -1) {
    data.users[userIdx] = { ...data.users[userIdx], ...user };
  }
  CampusRentData.save(data);

  // Update UI immediately
  initDynamicHeader();
  closeModal('profile-settings-modal');
  showToast('Profile photo and details updated successfully!', 'success');
}

// Global App helpers
window.App = {
  formatBDT,
  showToast,
  openModal,
  closeModal,
  copyToClipboard,
  openAuthModal,
  openProfileModal,
  closeProfileModal,
  renderUserAvatarHTML,
  Auth
};
window.Auth = Auth;
window.openAuthModal = openAuthModal;
window.openProfileModal = openProfileModal;
window.closeProfileModal = closeProfileModal;
window.switchProfileTab = switchProfileTab;
window.togglePasswordVisibility = togglePasswordVisibility;
window.handleProfileChangePassword = handleProfileChangePassword;

