/**
 * TugasKu - Toast Notifications (toast.js)
 * §8.9: sukses hilang ±5 detik, gagal bertahan sampai ditutup/diklik Coba Lagi
 * role="status"/"alert" untuk aksesibilitas
 */

let container = null;

function getContainer() {
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');
    document.body.appendChild(container);
  }
  return container;
}

/**
 * Tampilkan toast
 * @param {object} options
 * @param {string} options.type    - 'success' | 'error' | 'warning'
 * @param {string} options.message - pesan toast
 * @param {string} [options.actionLabel] - label tombol aksi (misal "Coba Lagi")
 * @param {Function} [options.onAction] - callback tombol aksi
 * @param {number}  [options.duration] - ms, 0 = tidak hilang otomatis
 */
export function showToast({ type = 'success', message, actionLabel, onAction, duration }) {
  const c = getContainer();
  const toast = document.createElement('div');

  toast.className = `toast toast-${type}`;
  toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

  const icons = {
    success: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    error:   `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    warning: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
  };

  const closeBtn = `<button class="toast-close" aria-label="Tutup notifikasi">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>`;

  const actionHtml = (actionLabel && onAction)
    ? `<button class="toast-action">${actionLabel}</button>`
    : '';

  toast.innerHTML = `
    <span class="toast-icon ${type}">${icons[type] || icons.success}</span>
    <div class="toast-content">
      <div class="toast-title">${message}</div>
      ${actionHtml}
    </div>
    ${closeBtn}
  `;

  c.appendChild(toast);

  // Action callback
  if (actionLabel && onAction) {
    toast.querySelector('.toast-action').addEventListener('click', () => {
      removeToast(toast);
      onAction();
    });
  }

  // Close button
  toast.querySelector('.toast-close').addEventListener('click', () => removeToast(toast));

  // Auto dismiss
  const autoDismiss = duration !== undefined ? duration : (type === 'error' ? 0 : 5000);
  if (autoDismiss > 0) {
    setTimeout(() => removeToast(toast), autoDismiss);
  }

  return toast;
}

function removeToast(toast) {
  toast.style.opacity = '0';
  toast.style.transform = 'translateX(24px)';
  setTimeout(() => toast.remove(), 300);
}

export const toast = {
  success: (msg, opts = {}) => showToast({ type: 'success', message: msg, ...opts }),
  error:   (msg, opts = {}) => showToast({ type: 'error',   message: msg, ...opts }),
  warning: (msg, opts = {}) => showToast({ type: 'warning', message: msg, ...opts }),
};
