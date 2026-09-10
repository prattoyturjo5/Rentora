/**
 * CampusRent Hub - Main Shared Application Logic & UI Handlers
 */

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initModalListeners();
  initEscKeyClose();
  renderNotificationCounts();
});

// Format currency in Bangladeshi Taka
function formatBDT(amount) {
  return '৳' + Number(amount).toLocaleString('en-IN');
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

  // Trigger animation
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

  // Close when clicking modal backdrop
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

// Copy Text helper
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
  notifDots.forEach(dot => {
    dot.textContent = '3';
  });
}

// Export common helpers to global window
window.App = {
  formatBDT,
  showToast,
  openModal,
  closeModal,
  copyToClipboard
};
