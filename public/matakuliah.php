<?php
/**
 * TugasKu - Halaman Mata Kuliah (matakuliah.php)
 * PB-02 / IS02: CRUD Mata Kuliah, proteksi hapus jika masih terkait tugas
 */
require_once __DIR__ . '/layout.php';
renderHead('Daftar Mata Kuliah', 'Kelola mata kuliah, dosen pengampu, dan SKS - TugasKu');
?>
<body>
<div class="app-shell">
  <?php renderTopbar('matakuliah'); ?>
  <?php renderSidebar('matakuliah'); ?>

  <main class="main-content" id="main-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mata Kuliah</h1>
        <p class="page-subtitle">Kelola daftar mata kuliah dan dosen pengampu</p>
      </div>
      <button class="btn btn-primary" id="btn-tambah-mk" aria-label="Tambah mata kuliah baru">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        + Tambah Mata Kuliah
      </button>
    </div>

    <!-- Container Grid/Table -->
    <div id="mk-container">
      <div id="mk-loading" class="tugas-table-wrap" role="status" aria-live="polite">
        <table class="tugas-table" aria-hidden="true"><tbody>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
          <tr><td><div class="skeleton skeleton-row"></div></td></tr>
        </tbody></table>
        <p class="text-center text-muted" style="padding:16px">Memuat data mata kuliah…</p>
      </div>

      <div id="mk-table-wrap" class="tugas-table-wrap" style="display:none">
        <table class="tugas-table" id="mk-table" aria-label="Daftar mata kuliah">
          <thead>
            <tr>
              <th scope="col">Mata Kuliah</th>
              <th scope="col">Kode MK</th>
              <th scope="col">Dosen Pengampu</th>
              <th scope="col">SKS</th>
              <th scope="col">Terkait</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody id="mk-tbody"></tbody>
        </table>
      </div>

      <div id="mk-empty" class="state-view" style="display:none" role="status">
        <div class="state-icon empty" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <div class="state-title">Belum ada mata kuliah</div>
        <p class="state-desc">Tambahkan mata kuliah baru untuk mengelompokkan tugas-tugas kamu.</p>
        <button class="btn btn-primary" id="btn-empty-add-mk">+ Tambah Mata Kuliah</button>
      </div>
    </div>
  </main>
</div>

<?php renderMobileBar(); ?>

<!-- Modal Tambah/Ubah Mata Kuliah -->
<div id="modal-mk-overlay" class="modal-overlay" hidden style="display:none" role="dialog" aria-modal="true" aria-labelledby="modal-mk-title">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-mk-title">Tambah Mata Kuliah</h2>
      <button class="modal-close" id="modal-mk-close" aria-label="Tutup modal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <form id="form-mk" novalidate>
        <input type="hidden" id="field-mk-id" value="">

        <div class="form-field">
          <label class="form-label" for="field-mk-nama">
            Nama Mata Kuliah <span class="required">*</span>
          </label>
          <input type="text" class="form-input" id="field-mk-nama" name="nama_mk"
                 placeholder="Contoh: Aplikasi Web" maxlength="100" required aria-required="true">
        </div>

        <div class="form-row">
          <div class="form-field">
            <label class="form-label" for="field-mk-kode">
              Kode MK <span class="optional">(opsional)</span>
            </label>
            <input type="text" class="form-input" id="field-mk-kode" name="kode_mk"
                   placeholder="Contoh: INF60285" maxlength="20">
          </div>
          <div class="form-field">
            <label class="form-label" for="field-mk-sks">
              SKS <span class="optional">(opsional)</span>
            </label>
            <input type="number" class="form-input" id="field-mk-sks" name="sks" min="1" max="10" placeholder="Contoh: 3">
          </div>
        </div>

        <div class="form-field">
          <label class="form-label" for="field-mk-dosen">
            Dosen Pengampu <span class="optional">(opsional)</span>
          </label>
          <input type="text" class="form-input" id="field-mk-dosen" name="dosen"
                 placeholder="Nama dosen pengampu" maxlength="100">
        </div>

        <div class="form-field">
          <label class="form-label" for="field-mk-warna">
            Warna Label
          </label>
          <div style="display:flex;align-items:center;gap:12px">
            <input type="color" id="field-mk-warna" name="warna_label" value="#4F46E5" style="width:48px;height:38px;padding:2px;cursor:pointer;border:1px solid var(--c-border);border-radius:6px">
            <span class="fs-xs text-muted" id="warna-hex-label">#4F46E5</span>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="modal-mk-batal">Batal</button>
      <button class="btn btn-primary" id="modal-mk-simpan">Simpan</button>
    </div>
  </div>
</div>

<!-- Modal Hapus Mata Kuliah -->
<div id="modal-hapus-mk-overlay" class="modal-overlay" hidden style="display:none" role="dialog" aria-modal="true" aria-labelledby="modal-hapus-mk-title">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-hapus-mk-title">Hapus Mata Kuliah?</h2>
      <button class="modal-close" id="modal-hapus-mk-close" aria-label="Tutup modal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="delete-modal-icon-wrap" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#DC2C4F" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <div class="delete-modal-subtitle">Konfirmasi Penghapusan</div>
      <p id="hapus-mk-confirm-text" class="fs-sm" style="color:var(--c-text)"></p>
      <div class="delete-modal-warning" role="alert">
        <p>Mata kuliah yang masih memiliki tugas terhubung tidak dapat dihapus sebelum tugasnya diselesaikan/dihapus terlebih dahulu.</p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="modal-hapus-mk-batal" autofocus>Batal</button>
      <button class="btn btn-danger" id="modal-hapus-mk-konfirm">Hapus</button>
    </div>
  </div>
</div>

<script type="module">
import { api } from './assets/js/api.js';
import { toast } from './assets/js/toast.js';
import {
  openModal, closeModal,
  showFormErrors, clearFormErrors,
  updateSidebarCounts, setupTopbarSearch, setupMobileDrawer
} from './assets/js/ui.js';

let allMataKuliah = [];
let pendingDeleteMK = null;

const tbody = document.getElementById('mk-tbody');
const loadingEl = document.getElementById('mk-loading');
const tableWrap = document.getElementById('mk-table-wrap');
const emptyEl = document.getElementById('mk-empty');

setupTopbarSearch();
setupMobileDrawer();
loadRingkasan();
loadDaftarMK();

async function loadRingkasan() {
  try {
    const res = await api.getRingkasan();
    if (res.data) updateSidebarCounts(res.data);
  } catch (e) {}
}

async function loadDaftarMK() {
  loadingEl.style.display = '';
  tableWrap.style.display = 'none';
  emptyEl.style.display = 'none';

  try {
    const res = await api.getDaftarMK();
    allMataKuliah = res.data || [];

    loadingEl.style.display = 'none';
    if (allMataKuliah.length === 0) {
      emptyEl.style.display = 'flex';
    } else {
      tableWrap.style.display = '';
      renderTable(allMataKuliah);
    }
  } catch (err) {
    loadingEl.style.display = 'none';
    toast.error('Gagal memuat daftar mata kuliah.');
  }
}

function renderTable(list) {
  tbody.innerHTML = '';

  list.forEach(mk => {
    const tr = document.createElement('tr');

    // Mata Kuliah (Badge warna + Nama)
    const tdNama = document.createElement('td');
    const mkWrap = document.createElement('div');
    mkWrap.className = 'tugas-matkul';
    const dot = document.createElement('span');
    dot.className = 'matkul-dot';
    dot.style.background = mk.warna_label || '#4F46E5';
    dot.setAttribute('aria-hidden', 'true');
    const namaText = document.createTextNode(mk.nama_mk); // §11
    mkWrap.appendChild(dot);
    mkWrap.appendChild(namaText);
    tdNama.appendChild(mkWrap);

    // Kode MK
    const tdKode = document.createElement('td');
    tdKode.textContent = mk.kode_mk || '–';

    // Dosen
    const tdDosen = document.createElement('td');
    tdDosen.textContent = mk.dosen || '–';

    // SKS
    const tdSks = document.createElement('td');
    tdSks.textContent = mk.sks ? `${mk.sks} SKS` : '–';

    // Terkait (Jumlah tugas)
    const tdTugas = document.createElement('td');
    const badgeTugas = document.createElement('span');
    badgeTugas.className = 'badge';
    badgeTugas.style.background = 'var(--c-bg)';
    badgeTugas.style.color = 'var(--c-text-sec)';
    badgeTugas.textContent = `${mk.jumlah_tugas || 0} Tugas`;
    tdTugas.appendChild(badgeTugas);

    // Aksi
    const tdAksi = document.createElement('td');
    const btnEdit = document.createElement('button');
    btnEdit.className = 'btn btn-secondary btn-sm';
    btnEdit.textContent = 'Ubah';
    btnEdit.style.marginRight = '8px';
    btnEdit.addEventListener('click', () => openEditModal(mk));

    const btnHapus = document.createElement('button');
    btnHapus.className = 'btn btn-ghost btn-sm text-danger';
    btnHapus.textContent = 'Hapus';
    btnHapus.addEventListener('click', () => openDeleteModal(mk));

    tdAksi.appendChild(btnEdit);
    tdAksi.appendChild(btnHapus);

    tr.appendChild(tdNama);
    tr.appendChild(tdKode);
    tr.appendChild(tdDosen);
    tr.appendChild(tdSks);
    tr.appendChild(tdTugas);
    tr.appendChild(tdAksi);
    tbody.appendChild(tr);
  });
}

// Color picker hex label sync
const warnaInput = document.getElementById('field-mk-warna');
const hexLabel = document.getElementById('warna-hex-label');
warnaInput.addEventListener('input', (e) => {
  hexLabel.textContent = e.target.value.toUpperCase();
});

// Modal Tambah & Ubah
const modalOverlay = document.getElementById('modal-mk-overlay');
const formMK = document.getElementById('form-mk');

document.getElementById('btn-tambah-mk').addEventListener('click', () => openCreateModal());
const btnEmptyAdd = document.getElementById('btn-empty-add-mk');
if (btnEmptyAdd) btnEmptyAdd.addEventListener('click', () => openCreateModal());

function openCreateModal() {
  document.getElementById('modal-mk-title').textContent = 'Tambah Mata Kuliah';
  document.getElementById('field-mk-id').value = '';
  formMK.reset();
  warnaInput.value = '#4F46E5';
  hexLabel.textContent = '#4F46E5';
  clearFormErrors(formMK);
  openModal(modalOverlay);
}

function openEditModal(mk) {
  document.getElementById('modal-mk-title').textContent = 'Ubah Mata Kuliah';
  document.getElementById('field-mk-id').value = mk.id_mk;
  document.getElementById('field-mk-nama').value = mk.nama_mk;
  document.getElementById('field-mk-kode').value = mk.kode_mk || '';
  document.getElementById('field-mk-sks').value = mk.sks || '';
  document.getElementById('field-mk-dosen').value = mk.dosen || '';
  const color = mk.warna_label || '#4F46E5';
  warnaInput.value = color;
  hexLabel.textContent = color.toUpperCase();
  clearFormErrors(formMK);
  openModal(modalOverlay);
}

document.getElementById('modal-mk-close').addEventListener('click', () => closeModal(modalOverlay));
document.getElementById('modal-mk-batal').addEventListener('click', () => closeModal(modalOverlay));

let isSubmitting = false;
document.getElementById('modal-mk-simpan').addEventListener('click', async () => {
  if (isSubmitting) return;
  isSubmitting = true;

  clearFormErrors(formMK);
  const btn = document.getElementById('modal-mk-simpan');
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  const mkId = document.getElementById('field-mk-id').value;
  const payload = {
    nama_mk: document.getElementById('field-mk-nama').value.trim(),
    kode_mk: document.getElementById('field-mk-kode').value.trim() || null,
    dosen:   document.getElementById('field-mk-dosen').value.trim() || null,
    sks:     parseInt(document.getElementById('field-mk-sks').value) || null,
    warna_label: warnaInput.value,
  };

  try {
    if (mkId) {
      await api.updateMK(mkId, payload);
      toast.success('Mata kuliah berhasil diperbarui.');
    } else {
      await api.createMK(payload);
      toast.success('Mata kuliah berhasil ditambahkan.');
    }
    closeModal(modalOverlay);
    await loadDaftarMK();
  } catch (err) {
    if (err.status === 422 && err.fields) {
      showFormErrors(err.fields, formMK);
    } else {
      toast.error(err.message || 'Gagal menyimpan mata kuliah.');
    }
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan';
    isSubmitting = false;
  }
});

// Delete Modal
const modalHapusOverlay = document.getElementById('modal-hapus-mk-overlay');

function openDeleteModal(mk) {
  pendingDeleteMK = mk;
  document.getElementById('hapus-mk-confirm-text').textContent =
    `Apakah Anda yakin ingin menghapus mata kuliah "${mk.nama_mk}"?`;
  openModal(modalHapusOverlay);
}

document.getElementById('modal-hapus-mk-close').addEventListener('click', () => closeModal(modalHapusOverlay));
document.getElementById('modal-hapus-mk-batal').addEventListener('click', () => closeModal(modalHapusOverlay));

document.getElementById('modal-hapus-mk-konfirm').addEventListener('click', async () => {
  if (!pendingDeleteMK) return;

  const btn = document.getElementById('modal-hapus-mk-konfirm');
  btn.disabled = true;
  btn.textContent = 'Menghapus...';

  try {
    await api.hapusMK(pendingDeleteMK.id_mk);
    closeModal(modalHapusOverlay);
    toast.success('Mata kuliah berhasil dihapus.');
    await loadDaftarMK();
  } catch (err) {
    if (err.code === 'MK_MASIH_DIPAKAI') {
      toast.error(err.message || 'Mata kuliah tidak dapat dihapus karena masih digunakan.');
    } else {
      toast.error(err.message || 'Gagal menghapus mata kuliah.');
    }
    closeModal(modalHapusOverlay);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Hapus';
    pendingDeleteMK = null;
  }
});
</script>
</body>
</html>

