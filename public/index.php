<?php
/**
 * TugasKu - P01 Dashboard (index.php)
 * FR-01 s.d. FR-05, BR-03, BR-04
 * SEO: title, meta description, heading hierarchy
 */
require_once __DIR__ . '/layout.php';
renderHead('Dashboard', 'Dashboard tugas kuliah mahasiswa TugasKu - sistem manajemen tugas UNY Kelompok 02');
?>
<body>
<div class="app-shell">
  <?php renderTopbar('dashboard'); ?>
  <?php renderSidebar('dashboard'); ?>

  <main class="main-content" id="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard Tugas</h1>
        <p class="page-subtitle">Kelola dan pantau tugas kuliah kamu</p>
      </div>
      <button class="btn btn-primary" id="btn-tambah-tugas" aria-label="Tambah tugas baru">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        + Tambah Tugas
      </button>
    </div>

    <!-- Progress Banner (FR-05) -->
    <div class="progress-banner" id="progress-banner" role="region" aria-label="Progres penyelesaian tugas">
      <div class="progress-banner-label">Progres Penyelesaian</div>
      <div class="progress-banner-pct" id="pct-display">0% Selesai</div>
      <div class="progress-banner-sub" id="pct-sub">Memuat data...</div>
      <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progress-bar" style="width:0%" role="progressbar"
             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
             aria-label="Persentase tugas selesai"></div>
      </div>
    </div>

    <!-- Summary Cards (FR-05) -->
    <div class="summary-grid" id="summary-grid" role="region" aria-label="Ringkasan tugas">
      <div class="summary-card total">
        <div class="summary-card-label">Total Tugas</div>
        <div class="summary-card-value" id="cnt-total">-</div>
        <svg class="summary-card-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div class="summary-card belum">
        <div class="summary-card-label">Belum</div>
        <div class="summary-card-value" id="cnt-belum">-</div>
        <svg class="summary-card-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
        </svg>
      </div>
      <div class="summary-card proses">
        <div class="summary-card-label">Dalam Proses</div>
        <div class="summary-card-value" id="cnt-proses">-</div>
        <svg class="summary-card-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
      </div>
      <div class="summary-card selesai">
        <div class="summary-card-label">Selesai</div>
        <div class="summary-card-value" id="cnt-selesai">-</div>
        <svg class="summary-card-icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
    </div>

    <!-- Filter Bar (FR-08) -->
    <div class="filter-bar" role="toolbar" aria-label="Filter dan urutan tugas">
      <div class="chip-group" role="group" aria-label="Filter status">
        <button class="chip active" data-filter="status" data-value="" id="chip-semua" aria-pressed="true">Semua</button>
        <button class="chip" data-filter="status" data-value="Belum" id="chip-belum" aria-pressed="false">Belum</button>
        <button class="chip" data-filter="status" data-value="Dalam Proses" id="chip-proses" aria-pressed="false">Dalam Proses</button>
        <button class="chip" data-filter="status" data-value="Selesai" id="chip-selesai" aria-pressed="false">Selesai</button>
      </div>
      <div class="filter-spacer"></div>
      <label for="sort-select" class="sr-only">Urutkan</label>
      <select class="filter-sort" id="sort-select" aria-label="Urutan tampilan tugas">
        <option value="deadline|asc">Urutkan: Deadline Terdekat</option>
        <option value="deadline|desc">Urutkan: Deadline Terjauh</option>
        <option value="judul|asc">Urutkan: Judul A–Z</option>
        <option value="created_at|desc">Urutkan: Terbaru Ditambahkan</option>
      </select>
    </div>

    <!-- Task List Container -->
    <div id="tugas-list-container">
      <!-- Loading State -->
      <div id="state-loading" class="tugas-table-wrap" role="status" aria-live="polite" aria-label="Memuat daftar tugas">
        <table class="tugas-table" aria-hidden="true"><tbody>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
        </tbody></table>
        <p class="text-center text-muted" style="padding:16px">Memuat daftar tugas…</p>
      </div>

      <!-- Table (Desktop ≥ 1024px) -->
      <div id="tugas-table-wrap" class="tugas-table-wrap" style="display:none">
        <table class="tugas-table" id="tugas-table" aria-label="Daftar tugas">
          <thead>
            <tr>
              <th scope="col">Judul Tugas</th>
              <th scope="col">Mata Kuliah</th>
              <th scope="col">Deadline</th>
              <th scope="col">Status</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody id="tugas-tbody"></tbody>
        </table>
      </div>

      <!-- Cards (Mobile/Tablet) -->
      <div class="tugas-cards" id="tugas-cards"></div>

      <!-- Empty State -->
      <div id="state-empty" class="state-view" style="display:none" role="status">
        <div class="state-icon empty" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div class="state-title" id="empty-title">Belum ada tugas</div>
        <p class="state-desc" id="empty-desc">Tambahkan tugas pertama kamu untuk mulai melacak progres.</p>
        <button class="btn btn-primary" id="btn-empty-add">+ Tambah Tugas</button>
        <button class="btn btn-secondary" id="btn-reset-filter" style="display:none">Reset Filter</button>
      </div>

      <!-- Error State -->
      <div id="state-error" class="state-view" style="display:none" role="alert">
        <div class="state-icon error" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
        <div class="state-title">Daftar tugas gagal dimuat.</div>
        <p class="state-desc">Terjadi kesalahan saat mengambil data.</p>
        <button class="btn btn-primary" id="btn-retry-load">Coba Lagi</button>
      </div>
    </div>
  </main>
</div>

<?php renderMobileBar(); ?>

<!-- Modal Tambah Tugas -->
<div id="modal-tambah-overlay" class="modal-overlay" hidden style="display:none" role="dialog" aria-modal="true" aria-labelledby="modal-tambah-title">
  <div class="modal-box" id="modal-tambah-box">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-tambah-title">Tambah Tugas Baru</h2>
      <button class="modal-close" id="modal-tambah-close" aria-label="Tutup modal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <!-- Info banner URGENT -->
      <div class="info-banner" role="note">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Tugas dengan sisa waktu kurang dari 24 jam otomatis diberi penanda <strong>URGENT</strong>.
      </div>

      <form id="form-tambah-tugas" novalidate>
        <div class="form-field">
          <label class="form-label" for="field-judul">
            Judul Tugas <span class="required" aria-label="wajib diisi">*</span>
          </label>
          <input type="text" class="form-input" id="field-judul" name="judul"
                 placeholder="Contoh: Laporan Praktikum Aplikasi Web"
                 maxlength="150" autocomplete="off" required aria-required="true">
        </div>

        <div class="form-row">
          <div class="form-field">
            <label class="form-label" for="field-id_mk">
              Mata Kuliah <span class="required" aria-label="wajib diisi">*</span>
            </label>
            <select class="form-select" id="field-id_mk" name="id_mk" required aria-required="true">
              <option value="">Pilih mata kuliah ▾</option>
            </select>
            <div style="margin-top:4px">
              <a href="matakuliah.php" class="fs-xs" style="color:var(--c-primary)">+ Mata kuliah baru</a>
            </div>
          </div>

          <div class="form-field">
            <label class="form-label" for="field-deadline-date">
              Deadline <span class="required" aria-label="wajib diisi">*</span>
            </label>
            <div style="display:flex;gap:8px">
              <input type="date" class="form-input" id="field-deadline-date" name="deadline_date"
                     required aria-required="true" style="flex:1">
              <input type="time" class="form-input" id="field-deadline-time" name="deadline_time"
                     value="23:59" required aria-required="true" style="width:100px">
            </div>
            <div class="form-hint">Tanggal sebelum hari ini dinonaktifkan di date-picker.</div>
          </div>
        </div>

        <div class="form-field">
          <label class="form-label">Prioritas</label>
          <div class="segmented" role="radiogroup" aria-label="Pilih prioritas tugas">
            <button type="button" class="segmented-item tinggi" data-pri="Tinggi" aria-pressed="false">Tinggi</button>
            <button type="button" class="segmented-item active" data-pri="Sedang" aria-pressed="true">Sedang</button>
            <button type="button" class="segmented-item rendah" data-pri="Rendah" aria-pressed="false">Rendah</button>
          </div>
          <input type="hidden" id="field-prioritas" name="prioritas" value="Sedang">
        </div>

        <div class="form-field">
          <label class="form-label" for="field-deskripsi">
            Catatan <span class="optional">(opsional)</span>
          </label>
          <textarea class="form-textarea" id="field-deskripsi" name="deskripsi"
                    placeholder="Instruksi dosen, tautan referensi, catatan penting..."
                    maxlength="500" rows="3"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="modal-tambah-batal">Batal</button>
      <button class="btn btn-primary" id="modal-tambah-simpan">Simpan Tugas</button>
    </div>
  </div>
</div>

<!-- Modal Hapus Tugas -->
<div id="modal-hapus-overlay" class="modal-overlay" hidden style="display:none" role="dialog" aria-modal="true" aria-labelledby="modal-hapus-title">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-hapus-title">Hapus Tugas Ini?</h2>
      <button class="modal-close" id="modal-hapus-close" aria-label="Tutup modal konfirmasi hapus">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="delete-modal-icon-wrap" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#DC2C4F" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <div class="delete-modal-subtitle">Konfirmasi Penghapusan Permanen</div>
      <p id="hapus-confirm-text" class="fs-sm" style="color:var(--c-text)"></p>
      <div class="delete-modal-warning" role="alert">
        <p id="hapus-warning-text"></p>
      </div>
      <p style="font-size:var(--fs-xs);color:var(--c-text-muted);margin-top:8px" id="hapus-matkul-text"></p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="modal-hapus-batal" autofocus>Batal</button>
      <button class="btn btn-danger" id="modal-hapus-konfirm">Hapus</button>
    </div>
  </div>
</div>

<script type="module">
import { api } from './assets/js/api.js';
import { toast } from './assets/js/toast.js';
import {
  createStatusBadge, createUrgentBadge, createPriorityBadge,
  formatDeadlineText, formatSisaWaktu,
  openModal, closeModal,
  showFormErrors, clearFormErrors,
  showQuickStatusMenu, statusClass,
  updateSidebarCounts, setupTopbarSearch, setupMobileDrawer
} from './assets/js/ui.js';

// ---- State ----
let allTugas   = [];
let waktuServer= null;
let activeFilter = '';
let activeSort   = 'deadline';
let activeOrder  = 'asc';
let pendingDelete= null; // {id, judul, nama_mk, logCount}

// ---- DOM refs ----
const tbody   = document.getElementById('tugas-tbody');
const cards   = document.getElementById('tugas-cards');
const stateLoading = document.getElementById('state-loading');
const stateEmpty   = document.getElementById('state-empty');
const stateError   = document.getElementById('state-error');
const tableWrap    = document.getElementById('tugas-table-wrap');

// ---- Init ----
setupTopbarSearch();
setupMobileDrawer();
setMinDeadlineDate();
loadMataKuliah();
loadDashboard();

// Set min date pada date picker (BR-02)
function setMinDeadlineDate() {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('field-deadline-date').min = today;
}

// ---- Load Dashboard ----
async function loadDashboard() {
  showState('loading');

  try {
    const params = {};
    if (activeFilter) params.status = activeFilter;
    params.sort  = activeSort;
    params.order = activeOrder;

    const [daftarRes, ringkasanRes] = await Promise.all([
      api.getDaftarTugas(params),
      api.getRingkasan(),
    ]);

    allTugas    = daftarRes.data || [];
    waktuServer = daftarRes.meta?.waktu_server || null;

    renderRingkasan(ringkasanRes.data);
    renderTugasList(allTugas);

  } catch (err) {
    console.error('Load dashboard error:', err);
    showState('error');
  }
}

// ---- Render Ringkasan (FR-05) ----
function renderRingkasan(data) {
  if (!data) return;

  const total   = data.total   || 0;
  const selesai = data.selesai || 0;
  const pct     = data.persen_selesai || 0;

  document.getElementById('cnt-total').textContent  = total;
  document.getElementById('cnt-belum').textContent  = data.belum || 0;
  document.getElementById('cnt-proses').textContent = data.dalam_proses || 0;
  document.getElementById('cnt-selesai').textContent= selesai;

  document.getElementById('pct-display').textContent = `${pct}% Selesai`;
  document.getElementById('pct-sub').textContent =
    `${selesai} dari ${total} tugas selesai (${pct}%)`;

  const bar = document.getElementById('progress-bar');
  bar.style.width = `${pct}%`;
  bar.setAttribute('aria-valuenow', pct);

  updateSidebarCounts(data);
}

// ---- Render Tugas List ----
function renderTugasList(list) {
  if (list.length === 0) {
    const hasFilter = activeFilter !== '';
    showState('empty', hasFilter);
    return;
  }

  showState('list');
  renderTable(list);
  renderCards(list);
}

// ---- Render Table (Desktop) ----
function renderTable(list) {
  tbody.innerHTML = '';

  list.forEach(tugas => {
    const tr = document.createElement('tr');
    tr.dataset.id = tugas.id_tugas;

    // Judul + Urgent Badge
    const tdJudul = document.createElement('td');
    const judulWrap = document.createElement('div');
    judulWrap.className = 'tugas-judul-cell';

    if (tugas.is_urgent) {
      judulWrap.appendChild(createUrgentBadge());
    }

    const judulLink = document.createElement('a');
    judulLink.className = 'tugas-judul-link';
    judulLink.href = `tugas.php?id=${tugas.id_tugas}`;
    judulLink.textContent = tugas.judul; // §11
    judulWrap.appendChild(judulLink);
    tdJudul.appendChild(judulWrap);

    // Mata Kuliah
    const tdMk = document.createElement('td');
    const mkWrap = document.createElement('div');
    mkWrap.className = 'tugas-matkul';
    const dot = document.createElement('span');
    dot.className = 'matkul-dot';
    dot.style.background = tugas.mata_kuliah?.warna_label || '#4F46E5';
    dot.setAttribute('aria-hidden', 'true');
    const mkText = document.createTextNode(tugas.mata_kuliah?.nama_mk || '–'); // §11
    mkWrap.appendChild(dot);
    mkWrap.appendChild(mkText);
    tdMk.appendChild(mkWrap);

    // Deadline
    const tdDl = document.createElement('td');
    const dlDiv = document.createElement('div');
    dlDiv.className = 'tugas-deadline' + (tugas.is_urgent ? ' urgent' : '');
    dlDiv.textContent = formatDeadlineText(tugas.deadline, waktuServer);

    const sisaDiv = document.createElement('div');
    sisaDiv.className = 'deadline-sisa' + (tugas.sisa_detik < 0 ? ' terlambat' : '');
    sisaDiv.textContent = tugas.status !== 'Selesai' ? formatSisaWaktu(tugas.sisa_detik) : '';

    tdDl.appendChild(dlDiv);
    tdDl.appendChild(sisaDiv);

    // Status
    const tdStatus = document.createElement('td');
    tdStatus.id = `status-badge-${tugas.id_tugas}`;
    tdStatus.appendChild(createStatusBadge(tugas.status));

    // Aksi
    const tdAksi = document.createElement('td');
    tdAksi.innerHTML = ''; // safe - will use DOM methods

    // Quick Status Button
    const qsWrap = document.createElement('div');
    qsWrap.className = 'quick-status-wrap';
    qsWrap.style.display = 'inline-block';

    const qsBtn = document.createElement('button');
    qsBtn.className = 'quick-status-btn';
    qsBtn.id = `qs-btn-${tugas.id_tugas}`;
    qsBtn.setAttribute('aria-label', `Ubah status tugas: ${tugas.judul}`);
    qsBtn.setAttribute('aria-haspopup', 'menu');
    const qsBtnText = document.createTextNode('Ubah ');
    const qsArrow = document.createElement('span');
    qsArrow.setAttribute('aria-hidden', 'true');
    qsArrow.textContent = '▾';
    qsBtn.appendChild(qsBtnText);
    qsBtn.appendChild(qsArrow);

    qsBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      showQuickStatusMenu(qsBtn, tugas.status, (newStatus) => {
        handleStatusChange(tugas.id_tugas, newStatus, tugas.status);
      });
    });

    qsWrap.appendChild(qsBtn);

    // More menu
    const moreWrap = document.createElement('div');
    moreWrap.className = 'more-menu-wrap';
    moreWrap.style.display = 'inline-block';

    const moreBtn = document.createElement('button');
    moreBtn.className = 'more-menu-btn';
    moreBtn.setAttribute('aria-label', `Menu lebih untuk tugas: ${tugas.judul}`);
    moreBtn.setAttribute('aria-haspopup', 'menu');
    moreBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>`;

    moreBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      showMoreMenu(moreBtn, tugas, moreWrap);
    });

    moreWrap.appendChild(moreBtn);

    tdAksi.appendChild(qsWrap);
    tdAksi.appendChild(document.createTextNode(' '));
    tdAksi.appendChild(moreWrap);

    tr.appendChild(tdJudul);
    tr.appendChild(tdMk);
    tr.appendChild(tdDl);
    tr.appendChild(tdStatus);
    tr.appendChild(tdAksi);
    tbody.appendChild(tr);
  });
}

// ---- Render Cards (Mobile) ----
function renderCards(list) {
  cards.innerHTML = '';

  list.forEach(tugas => {
    const card = document.createElement('div');
    card.className = 'tugas-card';
    card.dataset.id = tugas.id_tugas;

    // Header
    const header = document.createElement('div');
    header.className = 'tugas-card-header';

    const judulWrap = document.createElement('div');
    if (tugas.is_urgent) judulWrap.appendChild(createUrgentBadge());

    const judulLink = document.createElement('a');
    judulLink.className = 'tugas-judul-link fw-700';
    judulLink.href = `tugas.php?id=${tugas.id_tugas}`;
    judulLink.textContent = tugas.judul; // §11
    judulWrap.appendChild(judulLink);

    const badgeWrap = document.createElement('div');
    badgeWrap.id = `status-badge-${tugas.id_tugas}`;
    badgeWrap.appendChild(createStatusBadge(tugas.status));

    header.appendChild(judulWrap);
    header.appendChild(badgeWrap);
    card.appendChild(header);

    // Meta
    const meta = document.createElement('div');
    meta.className = 'tugas-card-meta';

    const mkSpan = document.createElement('div');
    mkSpan.className = 'tugas-matkul';
    const dot = document.createElement('span');
    dot.className = 'matkul-dot';
    dot.style.background = tugas.mata_kuliah?.warna_label || '#4F46E5';
    dot.setAttribute('aria-hidden', 'true');
    const mkText = document.createTextNode(tugas.mata_kuliah?.nama_mk || '–'); // §11
    mkSpan.appendChild(dot);
    mkSpan.appendChild(mkText);

    const dlSpan = document.createElement('div');
    dlSpan.className = 'tugas-deadline' + (tugas.is_urgent ? ' urgent' : '');
    dlSpan.textContent = formatDeadlineText(tugas.deadline, waktuServer);

    const sisaSpan = document.createElement('div');
    sisaSpan.className = 'deadline-sisa' + (tugas.sisa_detik < 0 ? ' terlambat' : '');
    sisaSpan.textContent = tugas.status !== 'Selesai' ? formatSisaWaktu(tugas.sisa_detik) : '';

    meta.appendChild(mkSpan);
    meta.appendChild(dlSpan);
    if (tugas.status !== 'Selesai') meta.appendChild(sisaSpan);

    card.appendChild(meta);

    // Actions
    const actions = document.createElement('div');
    actions.className = 'tugas-card-actions';

    const qsBtn = document.createElement('button');
    qsBtn.className = 'btn btn-secondary btn-sm';
    qsBtn.id = `qs-btn-${tugas.id_tugas}`;
    qsBtn.setAttribute('aria-label', `Ubah status: ${tugas.judul}`);
    const qsText = document.createTextNode('Ubah Status ▾');
    qsBtn.appendChild(qsText);

    qsBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      showQuickStatusMenu(qsBtn, tugas.status, (newStatus) => {
        handleStatusChange(tugas.id_tugas, newStatus, tugas.status);
      });
    });

    const detailLink = document.createElement('a');
    detailLink.className = 'btn btn-ghost btn-sm';
    detailLink.href = `tugas.php?id=${tugas.id_tugas}`;
    detailLink.textContent = 'Detail';

    const hapusBtn = document.createElement('button');
    hapusBtn.className = 'btn btn-ghost btn-sm text-danger';
    hapusBtn.setAttribute('aria-label', `Hapus tugas: ${tugas.judul}`);
    hapusBtn.textContent = 'Hapus';
    hapusBtn.addEventListener('click', () => openDeleteModal(tugas));

    actions.appendChild(qsBtn);
    actions.appendChild(detailLink);
    actions.appendChild(hapusBtn);
    card.appendChild(actions);

    cards.appendChild(card);
  });
}

// ---- More Menu ----
function showMoreMenu(btn, tugas, wrap) {
  document.querySelectorAll('.more-menu-dropdown').forEach(m => m.remove());

  const menu = document.createElement('div');
  menu.className = 'more-menu-dropdown';
  menu.setAttribute('role', 'menu');

  // Lihat Detail
  const lihatItem = document.createElement('a');
  lihatItem.className = 'more-menu-item';
  lihatItem.href = `tugas.php?id=${tugas.id_tugas}`;
  lihatItem.setAttribute('role', 'menuitem');
  lihatItem.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
  lihatItem.appendChild(document.createTextNode(' Lihat Detail')); // §11
  menu.appendChild(lihatItem);

  // Hapus
  const hapusItem = document.createElement('button');
  hapusItem.className = 'more-menu-item danger';
  hapusItem.setAttribute('role', 'menuitem');
  hapusItem.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
  hapusItem.appendChild(document.createTextNode(' Hapus')); // §11
  hapusItem.addEventListener('click', () => {
    menu.remove();
    openDeleteModal(tugas);
  });
  menu.appendChild(hapusItem);

  wrap.appendChild(menu);

  setTimeout(() => {
    document.addEventListener('click', function closeMenu(e) {
      if (!wrap.contains(e.target)) {
        menu.remove();
        document.removeEventListener('click', closeMenu);
      }
    });
  }, 10);
}

// ---- Quick Status Change (FR-04) ----
async function handleStatusChange(idTugas, newStatus, oldStatus) {
  const btn = document.getElementById(`qs-btn-${idTugas}`);
  if (btn) {
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
  }

  // Optimistic update (B3)
  updateStatusBadge(idTugas, newStatus);

  try {
    await api.ubahStatus(idTugas, newStatus);

    toast.success('Status diperbarui.');

    // Reload dashboard (ringkasan + urutan baru)
    await loadDashboard();
  } catch (err) {
    console.error('Status change error:', err);
    // Rollback (BR-09)
    updateStatusBadge(idTugas, oldStatus);

    toast.error('Gagal memperbarui status.', {
      actionLabel: 'Coba Lagi',
      onAction: () => handleStatusChange(idTugas, newStatus, oldStatus),
    });
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.removeAttribute('aria-busy');
    }
  }
}

function updateStatusBadge(idTugas, status) {
  // Cari semua elemen badge yang berhubungan dengan tugas ini
  const containers = document.querySelectorAll(`#status-badge-${idTugas}`);
  containers.forEach(container => {
    container.innerHTML = '';
    container.appendChild(createStatusBadge(status));
  });
}

// ---- Delete Modal (FR-07, P05) ----
function openDeleteModal(tugas) {
  pendingDelete = tugas;

  const confirmText = document.getElementById('hapus-confirm-text');
  const warningText = document.getElementById('hapus-warning-text');
  const matkulText  = document.getElementById('hapus-matkul-text');

  // Gunakan textContent, bukan innerHTML (§11)
  confirmText.textContent = `Apakah Anda yakin ingin menghapus tugas "${tugas.judul}"?`;
  matkulText.textContent  = `Mata kuliah: ${tugas.mata_kuliah?.nama_mk || '–'}`;

  // Count logs jika ada
  const logCount = tugas.riwayat?.length || 0;
  warningText.textContent = `Tindakan ini bersifat permanen. Data tugas beserta ${logCount} catatan riwayat statusnya tidak dapat dipulihkan.`;

  openModal(document.getElementById('modal-hapus-overlay'));
  document.getElementById('modal-hapus-batal').focus();
}

async function doDelete() {
  if (!pendingDelete) return;

  const btn = document.getElementById('modal-hapus-konfirm');
  btn.disabled = true;
  btn.textContent = 'Menghapus...';

  try {
    await api.hapusTugas(pendingDelete.id_tugas);
    closeModal(document.getElementById('modal-hapus-overlay'));
    toast.success('Tugas dihapus.');
    await loadDashboard();
  } catch (err) {
    if (err.status === 404) {
      closeModal(document.getElementById('modal-hapus-overlay'));
      toast.error('Tugas tidak ditemukan.');
      await loadDashboard();
    } else {
      toast.error('Gagal menghapus tugas. Coba lagi.');
    }
  } finally {
    btn.disabled = false;
    btn.textContent = 'Hapus';
    pendingDelete = null;
  }
}

// ---- State Management ----
function showState(state, hasFilter = false) {
  stateLoading.style.display = state === 'loading' ? '' : 'none';
  stateEmpty.style.display   = state === 'empty'   ? 'flex' : 'none';
  stateError.style.display   = state === 'error'   ? 'flex' : 'none';
  tableWrap.style.display    = state === 'list'    ? ''     : 'none';
  cards.style.display        = state === 'list'    ? ''     : 'none';

  if (state === 'empty') {
    const emptyTitle = document.getElementById('empty-title');
    const emptyDesc  = document.getElementById('empty-desc');
    const resetBtn   = document.getElementById('btn-reset-filter');
    const addBtn     = document.getElementById('btn-empty-add');

    if (hasFilter) {
      emptyTitle.textContent = 'Tidak ada tugas yang cocok';
      emptyDesc.textContent  = 'Coba ubah filter atau kata kunci pencarian.';
      resetBtn.style.display = '';
      addBtn.style.display   = 'none';
    } else {
      emptyTitle.textContent = 'Belum ada tugas';
      emptyDesc.textContent  = 'Tambahkan tugas pertama kamu untuk mulai melacak progres.';
      resetBtn.style.display = 'none';
      addBtn.style.display   = '';
    }

    // Ringkasan tetap tampil 0 (BR-10 empty)
    document.getElementById('cnt-total').textContent  = '0';
    document.getElementById('cnt-belum').textContent  = '0';
    document.getElementById('cnt-proses').textContent = '0';
    document.getElementById('cnt-selesai').textContent= '0';
    document.getElementById('pct-display').textContent = '0% Selesai';
    document.getElementById('pct-sub').textContent = '0 dari 0 tugas selesai (0%)';
    document.getElementById('progress-bar').style.width = '0%';
  }
}

// ---- Filter Chips ----
document.querySelectorAll('[data-filter="status"]').forEach(chip => {
  chip.addEventListener('click', () => {
    document.querySelectorAll('[data-filter="status"]').forEach(c => {
      c.classList.remove('active');
      c.setAttribute('aria-pressed', 'false');
    });
    chip.classList.add('active');
    chip.setAttribute('aria-pressed', 'true');
    activeFilter = chip.dataset.value;
    loadDashboard();
  });
});

// Filter dari URL query (sidebar click atau URL)
const urlParams = new URLSearchParams(window.location.search);
const urlStatus = urlParams.get('status');
if (urlStatus) {
  const matchChip = document.querySelector(`[data-filter="status"][data-value="${urlStatus}"]`);
  if (matchChip) {
    document.querySelectorAll('[data-filter="status"]').forEach(c => {
      c.classList.remove('active');
      c.setAttribute('aria-pressed', 'false');
    });
    matchChip.classList.add('active');
    matchChip.setAttribute('aria-pressed', 'true');
    activeFilter = urlStatus;
  }
}

// ---- Sort ----
document.getElementById('sort-select').addEventListener('change', (e) => {
  const [sort, order] = e.target.value.split('|');
  activeSort  = sort;
  activeOrder = order;
  loadDashboard();
});

// ---- Tambah Tugas Modal ----

// Trigger buttons
['btn-tambah-tugas', 'btn-tambah-tugas-topbar', 'btn-tambah-tugas-mobile', 'btn-empty-add'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('click', openTambahModal);
});

function openTambahModal() {
  clearTambahForm();
  document.getElementById('modal-tambah-title').textContent = 'Tambah Tugas Baru';
  document.getElementById('modal-tambah-simpan').textContent = 'Simpan Tugas';
  openModal(document.getElementById('modal-tambah-overlay'));
}

document.getElementById('modal-tambah-close').addEventListener('click', () =>
  closeModal(document.getElementById('modal-tambah-overlay')));
document.getElementById('modal-tambah-batal').addEventListener('click', () =>
  closeModal(document.getElementById('modal-tambah-overlay')));

// Prioritas segmented
document.querySelectorAll('.segmented-item').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.segmented-item').forEach(b => {
      b.classList.remove('active');
      b.setAttribute('aria-pressed', 'false');
    });
    btn.classList.add('active');
    btn.setAttribute('aria-pressed', 'true');
    document.getElementById('field-prioritas').value = btn.dataset.pri;
  });
});

// Load mata kuliah options
async function loadMataKuliah() {
  try {
    const res = await api.getDaftarMK();
    const mks = res.data || [];
    const select = document.getElementById('field-id_mk');
    select.innerHTML = '<option value="">Pilih mata kuliah ▾</option>';
    mks.forEach(mk => {
      const opt = document.createElement('option');
      opt.value = mk.id_mk;
      opt.textContent = mk.nama_mk; // §11
      select.appendChild(opt);
    });
  } catch (e) {
    console.warn('Gagal memuat mata kuliah:', e);
  }
}

// Submit Tambah Tugas (FR-01)
document.getElementById('modal-tambah-simpan').addEventListener('click', submitTambahTugas);

let isSubmitting = false;
async function submitTambahTugas() {
  if (isSubmitting) return; // cegah double submit (B2)
  isSubmitting = true;

  const form   = document.getElementById('form-tambah-tugas');
  const simpanBtn = document.getElementById('modal-tambah-simpan');

  clearFormErrors(form);
  simpanBtn.textContent = 'Menyimpan...';
  simpanBtn.disabled    = true;

  const deadlineDate = document.getElementById('field-deadline-date').value;
  const deadlineTime = document.getElementById('field-deadline-time').value || '23:59';
  const deadline = deadlineDate ? `${deadlineDate} ${deadlineTime}:00` : '';

  const payload = {
    judul:     document.getElementById('field-judul').value,
    id_mk:     parseInt(document.getElementById('field-id_mk').value) || null,
    deadline,
    prioritas: document.getElementById('field-prioritas').value,
    deskripsi: document.getElementById('field-deskripsi').value,
  };

  try {
    await api.createTugas(payload);
    closeModal(document.getElementById('modal-tambah-overlay'));
    toast.success('Tugas berhasil ditambahkan.');
    await loadDashboard();
  } catch (err) {
    if (err.status === 422 && err.fields) {
      showFormErrors(err.fields, form);
    } else {
      toast.error('Tugas gagal disimpan.', {
        actionLabel: 'Coba Lagi',
        onAction: submitTambahTugas,
      });
    }
  } finally {
    simpanBtn.textContent = 'Simpan Tugas';
    simpanBtn.disabled    = false;
    isSubmitting = false;
  }
}

function clearTambahForm() {
  const form = document.getElementById('form-tambah-tugas');
  form.reset();
  clearFormErrors(form);
  document.getElementById('field-prioritas').value = 'Sedang';
  document.querySelectorAll('.segmented-item').forEach(b => {
    b.classList.toggle('active', b.dataset.pri === 'Sedang');
    b.setAttribute('aria-pressed', b.dataset.pri === 'Sedang' ? 'true' : 'false');
  });
  setMinDeadlineDate();
}

// ---- Hapus Modal ----
document.getElementById('modal-hapus-close').addEventListener('click', () =>
  closeModal(document.getElementById('modal-hapus-overlay')));
document.getElementById('modal-hapus-batal').addEventListener('click', () =>
  closeModal(document.getElementById('modal-hapus-overlay')));
document.getElementById('modal-hapus-konfirm').addEventListener('click', doDelete);
document.getElementById('btn-reset-filter').addEventListener('click', () => {
  activeFilter = '';
  document.querySelectorAll('[data-filter="status"]').forEach(c => {
    c.classList.remove('active');
    c.setAttribute('aria-pressed', 'false');
  });
  document.getElementById('chip-semua').classList.add('active');
  document.getElementById('chip-semua').setAttribute('aria-pressed', 'true');
  loadDashboard();
});

document.getElementById('btn-retry-load').addEventListener('click', loadDashboard);
</script>
</body>
</html>
