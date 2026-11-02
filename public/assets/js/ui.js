/**
 * TugasKu - UI Utilities (ui.js)
 * StatusBadge, UrgentBadge, formatDeadline, fokus trap, sidebar, dll.
 * §11: pakai textContent bukan innerHTML untuk data pengguna
 */

// ---- Badge Renderers ---- //

/**
 * Buat elemen status badge
 * @param {string} status - 'Belum' | 'Dalam Proses' | 'Selesai'
 */
export function createStatusBadge(status) {
  const span = document.createElement('span');
  span.className = 'badge ' + statusClass(status);
  span.textContent = status; // §11: textContent
  return span;
}

export function statusClass(status) {
  const map = {
    'Belum':       'badge-belum',
    'Dalam Proses':'badge-proses',
    'Selesai':     'badge-selesai',
  };
  return map[status] || 'badge-belum';
}

export function statusColorHex(status) {
  const map = {
    'Belum':       '#DC2626',
    'Dalam Proses':'#F5B301',
    'Selesai':     '#16A34A',
  };
  return map[status] || '#DC2626';
}

/**
 * Buat elemen Urgent Badge
 */
export function createUrgentBadge() {
  const span = document.createElement('span');
  span.className = 'badge-urgent';
  span.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`;
  span.appendChild(document.createTextNode(' URGENT')); // §11
  return span;
}

/**
 * Buat badge prioritas
 */
export function createPriorityBadge(prioritas) {
  const span = document.createElement('span');
  const map = {
    'Tinggi': 'badge badge-prioritas-tinggi',
    'Sedang': 'badge badge-prioritas-sedang',
    'Rendah': 'badge badge-prioritas-rendah',
  };
  span.className = map[prioritas] || 'badge badge-prioritas-sedang';
  span.textContent = prioritas;
  return span;
}

// ---- Deadline Formatter ---- //

/**
 * Format deadline ke teks lokal Indonesia
 * Pakai waktuServer dari API agar tidak bergantung jam perangkat (§11)
 * @param {string} deadlineStr - 'YYYY-MM-DD HH:MM:SS'
 * @param {string} waktuServer - waktu server sekarang
 */
export function formatDeadlineText(deadlineStr, waktuServer) {
  const deadline = new Date(deadlineStr.replace(' ', 'T') + '+07:00');
  const now      = waktuServer ? new Date(waktuServer.replace(' ', 'T') + '+07:00') : new Date();

  const diffMs   = deadline - now;
  const diffDays = Math.floor(diffMs / 86400000);

  const hariNama = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
  const bulanNama = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

  const jamStr = deadline.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });

  let labelTanggal;
  if (diffDays === 0 || (diffMs > 0 && diffMs < 86400000)) {
    labelTanggal = 'Hari ini';
  } else if (diffDays === 1) {
    labelTanggal = 'Besok';
  } else if (diffDays === -1) {
    labelTanggal = 'Kemarin';
  } else {
    const hari  = hariNama[deadline.getDay()];
    const tgl   = deadline.getDate();
    const bulan = bulanNama[deadline.getMonth()];
    labelTanggal = `${hari}, ${tgl} ${bulan}`;
  }

  return `${labelTanggal}, ${jamStr}`;
}

/**
 * Teks sisa waktu: "Tersisa X jam" atau "Terlambat X jam"
 */
export function formatSisaWaktu(sisaDetik) {
  const abs   = Math.abs(sisaDetik);
  const terlambat = sisaDetik < 0;
  const prefix = terlambat ? 'Terlambat' : 'Tersisa';

  if (abs < 3600) {
    const mnt = Math.floor(abs / 60);
    return `${prefix} ${mnt} menit`;
  } else if (abs < 86400) {
    const jam = Math.floor(abs / 3600);
    return `${prefix} ${jam} jam`;
  } else {
    const hari = Math.floor(abs / 86400);
    return `${prefix} ${hari} hari`;
  }
}

// ---- Modal (Focus Trap) ---- //

let prevFocused = null;

/**
 * Buka modal dengan focus trap
 */
export function openModal(overlay) {
  prevFocused = document.activeElement;
  overlay.removeAttribute('hidden');
  overlay.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  // Focus ke tombol pertama yang bisa difokus
  requestAnimationFrame(() => {
    const focusable = overlay.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    if (focusable.length) focusable[0].focus();
  });

  // Focus trap
  overlay._trapHandler = (e) => trapFocus(e, overlay);
  overlay.addEventListener('keydown', overlay._trapHandler);

  // Esc untuk tutup
  overlay._escHandler = (e) => {
    if (e.key === 'Escape') closeModal(overlay);
  };
  document.addEventListener('keydown', overlay._escHandler);

  // Klik latar
  overlay._bgHandler = (e) => {
    if (e.target === overlay) closeModal(overlay);
  };
  overlay.addEventListener('click', overlay._bgHandler);
}

export function closeModal(overlay) {
  overlay.style.display = 'none';
  overlay.setAttribute('hidden', '');
  document.body.style.overflow = '';

  if (overlay._trapHandler) overlay.removeEventListener('keydown', overlay._trapHandler);
  if (overlay._escHandler)  document.removeEventListener('keydown', overlay._escHandler);
  if (overlay._bgHandler)   overlay.removeEventListener('click', overlay._bgHandler);

  if (prevFocused) prevFocused.focus();
}

function trapFocus(e, container) {
  if (e.key !== 'Tab') return;

  const focusable = [...container.querySelectorAll(
    'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
  )];

  if (!focusable.length) return;

  const first = focusable[0];
  const last  = focusable[focusable.length - 1];

  if (e.shiftKey && document.activeElement === first) {
    last.focus();
    e.preventDefault();
  } else if (!e.shiftKey && document.activeElement === last) {
    first.focus();
    e.preventDefault();
  }
}

// ---- Form Error Display ---- //

/**
 * Tampilkan errors validasi di bawah field
 * @param {object} fields - { fieldName: 'pesan error' }
 * @param {HTMLElement} form - container form
 */
export function showFormErrors(fields, form) {
  // Bersihkan error lama
  clearFormErrors(form);

  let firstErrEl = null;

  for (const [name, msg] of Object.entries(fields)) {
    // Cari input berdasarkan name atau id
    const input = form.querySelector(`[name="${name}"], #field-${name}`);
    if (input) {
      input.setAttribute('aria-invalid', 'true');
      const errEl = document.createElement('div');
      errEl.className = 'form-error';
      errEl.id = `err-${name}`;
      errEl.setAttribute('role', 'alert');

      const icon = document.createElement('span');
      icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`;

      const text = document.createTextNode(' ' + msg); // §11: textContent

      errEl.appendChild(icon);
      errEl.appendChild(text);

      input.setAttribute('aria-describedby', `err-${name}`);

      // Cari wrapper .form-field
      const fieldWrap = input.closest('.form-field');
      if (fieldWrap) {
        fieldWrap.appendChild(errEl);
      } else {
        input.insertAdjacentElement('afterend', errEl);
      }

      if (!firstErrEl) firstErrEl = input;
    }
  }

  // Fokus ke field error pertama
  if (firstErrEl) firstErrEl.focus();
}

export function clearFormErrors(form) {
  form.querySelectorAll('[aria-invalid="true"]').forEach(el => {
    el.removeAttribute('aria-invalid');
    el.removeAttribute('aria-describedby');
  });
  form.querySelectorAll('.form-error').forEach(el => el.remove());
}

// ---- Sidebar & Menu ---- //

/**
 * Set active link di sidebar/topbar berdasarkan halaman saat ini
 */
export function setActiveNav(pageName) {
  document.querySelectorAll('[data-page]').forEach(el => {
    if (el.dataset.page === pageName) {
      el.classList.add('active');
    } else {
      el.classList.remove('active');
    }
  });
}

/**
 * Setup close-on-outside untuk dropdown menu
 */
export function setupDropdownClose() {
  document.addEventListener('click', (e) => {
    document.querySelectorAll('.quick-status-menu, .more-menu-dropdown').forEach(menu => {
      if (!menu.closest('.quick-status-wrap, .more-menu-wrap')?.contains(e.target)) {
        menu.remove();
      }
    });
  });
}

// ---- Quick Status Dropdown ---- //

/**
 * Buat dan tampilkan Quick Status Dropdown
 * @param {HTMLElement} btn - tombol pemicu
 * @param {string} currentStatus - status saat ini
 * @param {Function} onSelect - callback(newStatus)
 */
export function showQuickStatusMenu(btn, currentStatus, onSelect) {
  // Tutup menu yang sudah terbuka
  document.querySelectorAll('.quick-status-menu').forEach(m => m.remove());

  const menu = document.createElement('div');
  menu.className = 'quick-status-menu';
  menu.setAttribute('role', 'menu');

  const statuses = [
    { value: 'Belum',       color: '#DC2626', label: 'Belum' },
    { value: 'Dalam Proses',color: '#F5B301', label: 'Dalam Proses' },
    { value: 'Selesai',     color: '#16A34A', label: 'Selesai' },
  ];

  statuses.forEach(s => {
    const item = document.createElement('button');
    item.className = 'quick-status-item';
    item.setAttribute('role', 'menuitem');
    if (s.value === currentStatus) {
      item.style.background = 'var(--c-bg)';
      item.style.fontWeight = '600';
    }

    const dot = document.createElement('span');
    dot.className = 'quick-status-dot';
    dot.style.background = s.color;

    const label = document.createTextNode(s.label); // §11

    item.appendChild(dot);
    item.appendChild(label);

    if (s.value !== currentStatus) {
      item.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.remove();
        onSelect(s.value);
      });
    } else {
      item.disabled = true;
      item.style.opacity = '.6';
    }

    menu.appendChild(item);
  });

  const wrap = btn.closest('.quick-status-wrap') || btn.parentElement;
  wrap.style.position = 'relative';
  wrap.appendChild(menu);

  // Close on outside click
  setTimeout(() => {
    document.addEventListener('click', function closeMenu(e) {
      if (!wrap.contains(e.target)) {
        menu.remove();
        document.removeEventListener('click', closeMenu);
      }
    });
  }, 10);
}

// ---- Mobile Drawer ---- //

export function setupMobileDrawer() {
  const fab     = document.querySelector('.mobile-fab');
  const overlay = document.querySelector('.mobile-drawer-overlay');

  if (!fab || !overlay) return;

  fab.addEventListener('click', () => {
    overlay.style.display = 'block';
    overlay.querySelector('.mobile-drawer')?.classList.add('open');
  });

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeDrawer();
  });

  function closeDrawer() {
    overlay.style.display = 'none';
  }

  // Close drawer when link clicked
  overlay.querySelectorAll('.mobile-drawer-item').forEach(item => {
    item.addEventListener('click', closeDrawer);
  });
}

// ---- Topbar search shortcut ---- //

export function setupTopbarSearch() {
  const searchInput = document.querySelector('.topbar-search');
  if (!searchInput) return;

  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && searchInput.value.trim()) {
      window.location.href = `cari.php?q=${encodeURIComponent(searchInput.value.trim())}`;
    }
  });
}

// ---- Update Sidebar Status Count ---- //

export function updateSidebarCounts(ringkasan) {
  const belumEl  = document.querySelector('[data-sidebar-count="Belum"]');
  const prosesEl = document.querySelector('[data-sidebar-count="Dalam Proses"]');
  const selesaiEl= document.querySelector('[data-sidebar-count="Selesai"]');

  if (belumEl)   belumEl.textContent   = ringkasan.belum   ?? 0;
  if (prosesEl)  prosesEl.textContent  = ringkasan.dalam_proses ?? 0;
  if (selesaiEl) selesaiEl.textContent = ringkasan.selesai ?? 0;
}

// ---- Safe text helper ---- //
export function safeText(str) {
  const el = document.createTextNode(str ?? '');
  return el;
}

// Escape untuk display di HTML attribute (bukan untuk innerHTML - tidak digunakan)
export function escAttr(str) {
  return String(str ?? '').replace(/['"<>&]/g, c => ({
    "'": '&#39;', '"': '&quot;', '<': '&lt;', '>': '&gt;', '&': '&amp;',
  }[c]));
}
