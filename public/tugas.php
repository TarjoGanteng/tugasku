<?php
/**
 * TugasKu - P04 Detail Tugas (tugas.php?id=)
 * FR-06: lihat detail, ubah, riwayat status
 * §8.5: countdown, segmented status, ubah detail
 */
require_once __DIR__ . '/layout.php';
renderHead('Detail Tugas', 'Detail dan riwayat perubahan status tugas - TugasKu');
?>
<body>
<div class="app-shell">
  <?php renderTopbar('dashboard'); ?>
  <?php renderSidebar('dashboard'); ?>

  <main class="main-content" id="main-content">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php">Dashboard</a>
      <span class="breadcrumb-sep" aria-hidden="true">›</span>
      <span class="breadcrumb-current" id="bc-matkul">Memuat...</span>
      <span class="breadcrumb-sep" aria-hidden="true">›</span>
      <span class="breadcrumb-current" id="bc-judul">Memuat...</span>
    </nav>

    <a href="index.php" class="btn-back" aria-label="Kembali ke Dashboard">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      ← Kembali ke Dashboard
    </a>

    <!-- Loading State -->
    <div id="detail-loading" role="status" aria-live="polite" aria-label="Memuat detail tugas">
      <div class="skeleton" style="height:24px;width:200px;margin-bottom:16px"></div>
      <div class="detail-grid">
        <div><div class="skeleton" style="height:300px;border-radius:14px"></div></div>
        <div><div class="skeleton" style="height:200px;border-radius:14px"></div></div>
      </div>
    </div>

    <!-- Not Found State -->
    <div id="detail-notfound" class="state-view" style="display:none" role="alert">
      <div class="state-icon error" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div class="state-title">Tugas tidak ditemukan</div>
      <p class="state-desc">Tugas yang kamu cari tidak ada atau sudah dihapus.</p>
      <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
    </div>

    <!-- Detail Content -->
    <div id="detail-content" style="display:none">
      <div class="detail-grid">
        <!-- Left Column -->
        <div>
          <!-- Main Card -->
          <div class="detail-card mb-4" id="detail-main-card">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px" id="detail-chips">
              <!-- chips rendered by JS -->
            </div>
            <h1 class="page-title" id="detail-judul" style="margin-bottom:8px"></h1>
            <p style="font-size:var(--fs-sm);color:var(--c-text-sec)" id="detail-dibuat"></p>
            <div style="margin-top:12px;font-size:var(--fs-sm);color:var(--c-text-sec)" id="detail-dosen-sks"></div>
          </div>

          <!-- Catatan -->
          <div class="detail-card mb-4">
            <div class="section-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Catatan
            </div>
            <p id="detail-catatan" style="font-size:var(--fs-sm);color:var(--c-text);line-height:1.7"></p>
          </div>

          <!-- Riwayat Status (FR-06) -->
          <div class="detail-card">
            <div class="section-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Riwayat Status
            </div>
            <div id="detail-riwayat">
              <p class="text-muted fs-sm">Memuat riwayat...</p>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div>
          <!-- Deadline Card (dark) -->
          <div class="deadline-card mb-4" id="deadline-card">
            <div class="deadline-card-label">TENGGAT WAKTU</div>
            <div id="deadline-urgent-wrap"></div>
            <div class="deadline-time" id="deadline-display"></div>

            <!-- Countdown -->
            <div class="countdown" id="countdown-wrap" aria-live="off">
              <div class="countdown-unit">
                <div class="countdown-value" id="cd-jam">00</div>
                <div class="countdown-label">JAM</div>
              </div>
              <div class="countdown-sep" aria-hidden="true">:</div>
              <div class="countdown-unit">
                <div class="countdown-value" id="cd-mnt">00</div>
                <div class="countdown-label">MENIT</div>
              </div>
              <div class="countdown-sep" aria-hidden="true">:</div>
              <div class="countdown-unit">
                <div class="countdown-value" id="cd-dtk">00</div>
                <div class="countdown-label">DETIK</div>
              </div>
            </div>

            <div class="deadline-progress">
              <div class="deadline-progress-fill" id="deadline-progress-fill" style="width:0%"></div>
            </div>

            <div id="selesai-info" style="display:none;margin-top:12px;font-size:var(--fs-sm);opacity:.8"></div>
          </div>

          <!-- Status Tugas Card -->
          <div class="detail-card mb-4">
            <div class="section-title">Status Tugas</div>
            <div class="status-segmented" id="status-segmented" role="group" aria-label="Ubah status tugas">
              <button class="status-seg-item belum" data-status="Belum" aria-pressed="false">Belum</button>
              <button class="status-seg-item proses" data-status="Dalam Proses" aria-pressed="false">Dalam Proses</button>
              <button class="status-seg-item selesai" data-status="Selesai" aria-pressed="false">Selesai</button>
            </div>
          </div>

          <!-- Actions -->
          <div class="detail-card">
            <div style="display:flex;flex-direction:column;gap:8px">
              <button class="btn btn-secondary w-full" id="btn-ubah-detail">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Ubah Detail
              </button>
              <button class="btn btn-danger w-full" id="btn-hapus-tugas">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Tugas
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Ubah Detail -->
<div id="modal-ubah-overlay" class="modal-overlay" hidden style="display:none" role="dialog" aria-modal="true" aria-labelledby="modal-ubah-title">
  <div class="modal-box">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-ubah-title">Ubah Detail Tugas</h2>
      <button class="modal-close" id="modal-ubah-close" aria-label="Tutup modal ubah detail">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <form id="form-ubah-tugas" novalidate>
        <div class="form-field">
          <label class="form-label" for="ubah-judul">
            Judul Tugas <span class="required">*</span>
          </label>
          <input type="text" class="form-input" id="ubah-judul" name="judul" maxlength="150" required aria-required="true">
        </div>

        <div class="form-row">
          <div class="form-field">
            <label class="form-label" for="ubah-id_mk">
              Mata Kuliah <span class="required">*</span>
            </label>
            <select class="form-select" id="ubah-id_mk" name="id_mk" required aria-required="true">
              <option value="">Pilih mata kuliah ▾</option>
            </select>
          </div>
          <div class="form-field">
            <label class="form-label" for="ubah-deadline-date">
              Deadline <span class="required">*</span>
            </label>
            <div style="display:flex;gap:8px">
              <input type="date" class="form-input" id="ubah-deadline-date" name="deadline_date" style="flex:1">
              <input type="time" class="form-input" id="ubah-deadline-time" name="deadline_time" style="width:100px">
            </div>
            <div class="form-hint">Ubah deadline hanya jika perlu (tanggal lampau diblokir).</div>
          </div>
        </div>

        <div class="form-field">
          <label class="form-label">Prioritas</label>
          <div class="segmented" role="radiogroup" aria-label="Pilih prioritas tugas">
            <button type="button" class="segmented-item ubah-pri tinggi" data-pri="Tinggi" aria-pressed="false">Tinggi</button>
            <button type="button" class="segmented-item ubah-pri" data-pri="Sedang" aria-pressed="false">Sedang</button>
            <button type="button" class="segmented-item ubah-pri rendah" data-pri="Rendah" aria-pressed="false">Rendah</button>
          </div>
          <input type="hidden" id="ubah-prioritas" name="prioritas" value="Sedang">
        </div>

        <div class="form-field">
          <label class="form-label" for="ubah-deskripsi">
            Catatan <span class="optional">(opsional)</span>
          </label>
          <textarea class="form-textarea" id="ubah-deskripsi" name="deskripsi" maxlength="500" rows="3"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="modal-ubah-batal">Batal</button>
      <button class="btn btn-primary" id="modal-ubah-simpan">Simpan Perubahan</button>
    </div>
  </div>
</div>

<!-- Modal Hapus -->
<div id="modal-hapus-overlay" class="modal-overlay" hidden style="display:none" role="dialog" aria-modal="true" aria-labelledby="modal-hapus-title">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-hapus-title">Hapus Tugas Ini?</h2>
      <button class="modal-close" id="modal-hapus-close" aria-label="Tutup konfirmasi hapus">
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
  updateSidebarCounts, setupTopbarSearch, setupMobileDrawer
} from './assets/js/ui.js';

// ---- Get tugas ID from URL ----
const urlParams = new URLSearchParams(window.location.search);
const tugasId = parseInt(urlParams.get('id'));

let currentTugas   = null;
let countdownTimer = null;
let waktuServer    = null;

setupTopbarSearch();
setupMobileDrawer();

// ---- Load Detail ----
if (!tugasId || isNaN(tugasId)) {
  showNotFound();
} else {
  loadDetail();
}

async function loadDetail() {
  try {
    const res = await api.getDetailTugas(tugasId);
    currentTugas = res.data;
    waktuServer  = currentTugas.waktu_server;
    renderDetail(currentTugas);
    loadMataKuliahOptions();
  } catch (err) {
    if (err.status === 404) showNotFound();
    else {
      document.getElementById('detail-loading').style.display = 'none';
      toast.error('Gagal memuat detail tugas.');
    }
  }
}

function showNotFound() {
  document.getElementById('detail-loading').style.display  = 'none';
  document.getElementById('detail-notfound').style.display = 'flex';
}

// ---- Render Detail ----
function renderDetail(tugas) {
  document.getElementById('detail-loading').style.display = 'none';
  document.getElementById('detail-content').style.display = '';

  // Update document title
  document.title = `${tugas.judul} | TugasKu`;

  // Breadcrumb
  document.getElementById('bc-matkul').textContent = tugas.mata_kuliah?.nama_mk || '–';
  document.getElementById('bc-judul').textContent  = tugas.judul;

  // Chips
  const chipsEl = document.getElementById('detail-chips');
  chipsEl.innerHTML = '';
  const mkChip = document.createElement('span');
  mkChip.className = 'badge';
  mkChip.style.background = (tugas.mata_kuliah?.warna_label || '#4F46E5') + '20';
  mkChip.style.color = tugas.mata_kuliah?.warna_label || '#4F46E5';
  mkChip.style.border = `1.5px solid ${tugas.mata_kuliah?.warna_label || '#4F46E5'}40`;
  mkChip.textContent = tugas.mata_kuliah?.nama_mk || '–'; // §11
  chipsEl.appendChild(mkChip);
  chipsEl.appendChild(createStatusBadge(tugas.status));
  chipsEl.appendChild(createPriorityBadge(tugas.prioritas));
  if (tugas.is_urgent) chipsEl.appendChild(createUrgentBadge());

  // Judul
  document.getElementById('detail-judul').textContent = tugas.judul; // §11

  // Dibuat
  const dibuatDate = new Date(tugas.created_at.replace(' ', 'T') + '+07:00');
  document.getElementById('detail-dibuat').textContent =
    `Dibuat ${dibuatDate.toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' })}`;

  // Dosen + SKS
  const dosenEl = document.getElementById('detail-dosen-sks');
  const parts = [];
  if (tugas.mata_kuliah?.dosen) parts.push(`Dosen: ${tugas.mata_kuliah.dosen}`);
  if (tugas.mata_kuliah?.sks)   parts.push(`${tugas.mata_kuliah.sks} SKS`);
  dosenEl.textContent = parts.join(' · ');

  // Catatan
  const catatanEl = document.getElementById('detail-catatan');
  if (tugas.deskripsi) {
    catatanEl.textContent = tugas.deskripsi; // §11
    catatanEl.style.color = 'var(--c-text)';
  } else {
    catatanEl.textContent = 'Tidak ada catatan.';
    catatanEl.style.color = 'var(--c-text-muted)';
  }

  // Riwayat Status
  renderRiwayat(tugas.riwayat || []);

  // Deadline Card
  renderDeadlineCard(tugas);

  // Status Segmented
  updateStatusSegmented(tugas.status);
}

function renderRiwayat(riwayat) {
  const el = document.getElementById('detail-riwayat');
  el.innerHTML = '';

  if (!riwayat.length) {
    const p = document.createElement('p');
    p.className = 'text-muted fs-sm';
    p.textContent = 'Belum ada perubahan status.';
    el.appendChild(p);
    return;
  }

  const list = document.createElement('div');
  list.className = 'riwayat-list';

  const bulanNama = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

  riwayat.forEach(log => {
    const item = document.createElement('div');
    item.className = 'riwayat-item';

    const dot = document.createElement('div');
    dot.className = 'riwayat-dot';
    dot.setAttribute('aria-hidden', 'true');
    dot.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>`;

    const content = document.createElement('div');
    const textEl  = document.createElement('div');
    textEl.className = 'riwayat-text';
    // §11: gunakan textContent
    const statusText = document.createTextNode(`${log.status_lama} → ${log.status_baru}`);
    textEl.appendChild(statusText);

    const timeEl = document.createElement('div');
    timeEl.className = 'riwayat-time';
    const d = new Date(log.waktu_perubahan.replace(' ', 'T') + '+07:00');
    const tgl = `${d.getDate()} ${bulanNama[d.getMonth()]} ${d.getFullYear()}`;
    const jam = d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', hour12:false});
    timeEl.textContent = `${tgl}, ${jam}`;

    content.appendChild(textEl);
    content.appendChild(timeEl);
    item.appendChild(dot);
    item.appendChild(content);
    list.appendChild(item);
  });

  el.appendChild(list);
}

// ---- Deadline Card + Countdown ----
function renderDeadlineCard(tugas) {
  const deadlineEl  = document.getElementById('deadline-display');
  const urgentWrap  = document.getElementById('deadline-urgent-wrap');
  const selesaiInfo = document.getElementById('selesai-info');
  const countdownEl = document.getElementById('countdown-wrap');

  // Format deadline text
  deadlineEl.textContent = `Batas Pengumpulan: ${formatDeadlineText(tugas.deadline, waktuServer)} WIB`;

  // Urgent badge
  urgentWrap.innerHTML = '';
  if (tugas.is_urgent) {
    urgentWrap.appendChild(createUrgentBadge());
  }

  if (tugas.status === 'Selesai') {
    // Tampilkan selesai_pada
    countdownEl.style.display = 'none';
    selesaiInfo.style.display = '';
    const selesaiDate = new Date(tugas.selesai_pada.replace(' ', 'T') + '+07:00');
    const fmt = selesaiDate.toLocaleString('id-ID', {
      day:'numeric', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit',
    });
    selesaiInfo.textContent = `Diselesaikan pada ${fmt}`;
  } else {
    selesaiInfo.style.display = 'none';
    countdownEl.style.display = 'flex';
    startCountdown(tugas);
  }
}

function startCountdown(tugas) {
  if (countdownTimer) clearInterval(countdownTimer);

  const deadlineMs = new Date(tugas.deadline.replace(' ', 'T') + '+07:00').getTime();
  const serverNowMs = waktuServer
    ? new Date(waktuServer.replace(' ', 'T') + '+07:00').getTime()
    : Date.now();
  const clientNowMs = Date.now();
  const serverOffset = serverNowMs - clientNowMs;

  function update() {
    const nowMs = Date.now() + serverOffset;
    const diffMs = deadlineMs - nowMs;
    const diffSec = Math.floor(diffMs / 1000);
    const absSec  = Math.abs(diffSec);

    const jam = Math.floor(absSec / 3600);
    const mnt = Math.floor((absSec % 3600) / 60);
    const dtk = absSec % 60;

    const urgent = diffSec < 0 || diffSec < 86400;
    const terlambat = diffSec < 0;

    const cdJam = document.getElementById('cd-jam');
    const cdMnt = document.getElementById('cd-mnt');
    const cdDtk = document.getElementById('cd-dtk');

    if (cdJam) cdJam.textContent = String(jam).padStart(2, '0');
    if (cdMnt) cdMnt.textContent = String(mnt).padStart(2, '0');
    if (cdDtk) cdDtk.textContent = String(dtk).padStart(2, '0');

    [cdJam, cdMnt, cdDtk].forEach(el => {
      if (el) el.classList.toggle('urgent', urgent);
    });

    // Progres bar
    const createdMs = new Date(tugas.created_at.replace(' ', 'T') + '+07:00').getTime();
    const total = deadlineMs - createdMs;
    const elapsed = nowMs - createdMs;
    const pct = total > 0 ? Math.min(100, Math.max(0, (elapsed / total) * 100)) : 0;
    const fill = document.getElementById('deadline-progress-fill');
    if (fill) fill.style.width = `${pct}%`;

    // Update deadline display color
    const dlEl = document.getElementById('deadline-display');
    if (dlEl) dlEl.style.color = urgent ? '#FCA5A5' : '#fff';

    if (terlambat) {
      const countdownEl = document.getElementById('countdown-wrap');
      if (countdownEl) {
        const label = countdownEl.previousElementSibling;
      }
    }
  }

  update();
  countdownTimer = setInterval(update, 1000);
}

// ---- Status Segmented (sama dengan Quick Status Dropdown) ----
function updateStatusSegmented(currentStatus) {
  document.querySelectorAll('.status-seg-item').forEach(btn => {
    const isActive = btn.dataset.status === currentStatus;
    btn.classList.toggle('active', isActive);
    btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
  });
}

document.querySelectorAll('.status-seg-item').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!currentTugas) return;
    const newStatus = btn.dataset.status;
    if (newStatus === currentTugas.status) return;

    const oldStatus = currentTugas.status;

    // Optimistic
    updateStatusSegmented(newStatus);
    // Update badge di chips
    const chipsEl = document.getElementById('detail-chips');
    // Re-render chips dengan status baru
    chipsEl.querySelectorAll('.badge-belum, .badge-proses, .badge-selesai').forEach(b => b.remove());
    chipsEl.insertBefore(createStatusBadge(newStatus), chipsEl.children[1] || null);

    try {
      await api.ubahStatus(currentTugas.id_tugas, newStatus);
      toast.success('Status diperbarui.');
      // Reload detail
      const res = await api.getDetailTugas(tugasId);
      currentTugas = res.data;
      waktuServer  = currentTugas.waktu_server;
      renderDetail(currentTugas);
    } catch (err) {
      updateStatusSegmented(oldStatus);
      toast.error('Gagal mengubah status.', {
        actionLabel: 'Coba Lagi',
        onAction: () => btn.click(),
      });
    }
  });
});

// ---- Ubah Detail (PUT) ----
async function loadMataKuliahOptions() {
  try {
    const res = await api.getDaftarMK();
    const mks = res.data || [];
    const select = document.getElementById('ubah-id_mk');
    select.innerHTML = '<option value="">Pilih mata kuliah ▾</option>';
    mks.forEach(mk => {
      const opt = document.createElement('option');
      opt.value = mk.id_mk;
      opt.textContent = mk.nama_mk; // §11
      select.appendChild(opt);
    });
  } catch (e) {}
}

document.getElementById('btn-ubah-detail').addEventListener('click', () => {
  if (!currentTugas) return;
  fillUbahForm(currentTugas);
  openModal(document.getElementById('modal-ubah-overlay'));
});

function fillUbahForm(tugas) {
  document.getElementById('ubah-judul').value = tugas.judul;
  document.getElementById('ubah-id_mk').value = tugas.id_mk;
  document.getElementById('ubah-deskripsi').value = tugas.deskripsi || '';
  document.getElementById('ubah-prioritas').value = tugas.prioritas;

  // Deadline
  const dl = new Date(tugas.deadline.replace(' ', 'T') + '+07:00');
  document.getElementById('ubah-deadline-date').value =
    dl.toLocaleDateString('sv-SE'); // YYYY-MM-DD
  document.getElementById('ubah-deadline-time').value =
    dl.toTimeString().slice(0, 5);

  // Prioritas segmented
  document.querySelectorAll('.ubah-pri').forEach(b => {
    b.classList.toggle('active', b.dataset.pri === tugas.prioritas);
    b.setAttribute('aria-pressed', b.dataset.pri === tugas.prioritas ? 'true' : 'false');
  });
}

// Prioritas segmented ubah
document.querySelectorAll('.ubah-pri').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.ubah-pri').forEach(b => {
      b.classList.remove('active');
      b.setAttribute('aria-pressed', 'false');
    });
    btn.classList.add('active');
    btn.setAttribute('aria-pressed', 'true');
    document.getElementById('ubah-prioritas').value = btn.dataset.pri;
  });
});

document.getElementById('modal-ubah-close').addEventListener('click', () =>
  closeModal(document.getElementById('modal-ubah-overlay')));
document.getElementById('modal-ubah-batal').addEventListener('click', () =>
  closeModal(document.getElementById('modal-ubah-overlay')));

let isUbahSubmitting = false;
document.getElementById('modal-ubah-simpan').addEventListener('click', async () => {
  if (isUbahSubmitting || !currentTugas) return;
  isUbahSubmitting = true;

  const form = document.getElementById('form-ubah-tugas');
  const btn  = document.getElementById('modal-ubah-simpan');
  clearFormErrors(form);
  btn.textContent = 'Menyimpan...';
  btn.disabled    = true;

  const deadlineDate = document.getElementById('ubah-deadline-date').value;
  const deadlineTime = document.getElementById('ubah-deadline-time').value || '23:59';
  const deadline = deadlineDate ? `${deadlineDate} ${deadlineTime}:00` : '';

  // Cek apakah deadline berubah (BR-02)
  const originalDeadline = new Date(currentTugas.deadline.replace(' ', 'T') + '+07:00')
    .toLocaleDateString('sv-SE') + ' ' + new Date(currentTugas.deadline.replace(' ', 'T') + '+07:00').toTimeString().slice(0,5) + ':00';
  const deadlineChanged = deadline !== originalDeadline;

  const payload = {
    judul:     document.getElementById('ubah-judul').value,
    id_mk:     parseInt(document.getElementById('ubah-id_mk').value) || null,
    prioritas: document.getElementById('ubah-prioritas').value,
    deskripsi: document.getElementById('ubah-deskripsi').value,
  };

  // Selalu kirim deadline
  payload.deadline = deadline;

  try {
    await api.updateTugas(currentTugas.id_tugas, payload);
    closeModal(document.getElementById('modal-ubah-overlay'));
    toast.success('Detail tugas berhasil diperbarui.');
    const res = await api.getDetailTugas(tugasId);
    currentTugas = res.data;
    waktuServer  = currentTugas.waktu_server;
    renderDetail(currentTugas);
  } catch (err) {
    if (err.status === 422 && err.fields) {
      showFormErrors(err.fields, form);
    } else {
      toast.error('Gagal menyimpan perubahan.');
    }
  } finally {
    btn.textContent = 'Simpan Perubahan';
    btn.disabled    = false;
    isUbahSubmitting = false;
  }
});

// ---- Hapus Modal ----
document.getElementById('btn-hapus-tugas').addEventListener('click', () => {
  if (!currentTugas) return;

  const logCount = currentTugas.riwayat?.length || 0;

  document.getElementById('hapus-confirm-text').textContent =
    `Apakah Anda yakin ingin menghapus tugas "${currentTugas.judul}"?`; // §11 - string concat safe
  document.getElementById('hapus-warning-text').textContent =
    `Tindakan ini bersifat permanen. Data tugas beserta ${logCount} catatan riwayat statusnya tidak dapat dipulihkan.`;
  document.getElementById('hapus-matkul-text').textContent =
    `Mata kuliah: ${currentTugas.mata_kuliah?.nama_mk || '–'}`;

  openModal(document.getElementById('modal-hapus-overlay'));
  document.getElementById('modal-hapus-batal').focus();
});

document.getElementById('modal-hapus-close').addEventListener('click', () =>
  closeModal(document.getElementById('modal-hapus-overlay')));
document.getElementById('modal-hapus-batal').addEventListener('click', () =>
  closeModal(document.getElementById('modal-hapus-overlay')));

document.getElementById('modal-hapus-konfirm').addEventListener('click', async () => {
  if (!currentTugas) return;
  const btn = document.getElementById('modal-hapus-konfirm');
  btn.disabled    = true;
  btn.textContent = 'Menghapus...';

  try {
    await api.hapusTugas(currentTugas.id_tugas);
    toast.success('Tugas dihapus.');
    window.location.href = 'index.php';
  } catch (err) {
    if (err.status === 404) {
      toast.error('Tugas tidak ditemukan.');
      window.location.href = 'index.php';
    } else {
      toast.error('Gagal menghapus tugas.');
      btn.disabled    = false;
      btn.textContent = 'Hapus';
    }
  }
});
</script>
</body>
</html>
