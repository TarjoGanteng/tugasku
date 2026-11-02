<?php
/**
 * TugasKu - P06 Cari & Filter (cari.php)
 * FR-08: cari judul, filter status & mata kuliah, chip filter aktif
 * State tersimpan di query string (history.replaceState)
 */
require_once __DIR__ . '/layout.php';
renderHead('Cari & Filter', 'Cari dan filter tugas berdasarkan mata kuliah, status, dan kata kunci - TugasKu');
?>
<body>
<div class="app-shell">
  <?php renderTopbar('cari'); ?>
  <?php renderSidebar('cari'); ?>

  <main class="main-content" id="main-content">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <span class="breadcrumb-current">Workspace</span>
      <span class="breadcrumb-sep" aria-hidden="true">›</span>
      <span class="breadcrumb-current">Cari &amp; Filter</span>
    </nav>

    <div class="page-header">
      <div>
        <h1 class="page-title">Pencarian &amp; Filter</h1>
        <p class="page-subtitle">Temukan tugas dengan cepat</p>
      </div>
    </div>

    <div class="search-filter-layout">
      <!-- Filter Panel -->
      <aside>
        <div class="filter-panel" id="filter-panel">
          <div class="filter-panel-header">
            <div class="filter-panel-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
              </svg>
              Filter Tugas Kuliah
            </div>
            <button class="filter-reset-link" id="btn-reset-filter" aria-label="Reset semua filter">Reset Filter</button>
          </div>

          <!-- Mata Kuliah Filter -->
          <div class="filter-group">
            <div class="filter-group-title">Mata Kuliah</div>
            <div id="filter-matkul-list">
              <div class="text-muted fs-xs">Memuat...</div>
            </div>
          </div>

          <!-- Status Filter -->
          <div class="filter-group">
            <div class="filter-group-title">Status Pengerjaan</div>
            <div id="filter-status-list">
              <?php
              $statuses = ['Belum' => '#DC2626', 'Dalam Proses' => '#F5B301', 'Selesai' => '#16A34A'];
              foreach ($statuses as $st => $color): ?>
              <label class="filter-check-item">
                <input type="checkbox" name="filter_status" value="<?= htmlspecialchars($st) ?>"
                       id="fst-<?= str_replace(' ', '-', $st) ?>" class="filter-status-cb">
                <span style="display:inline-flex;align-items:center;gap:6px">
                  <span style="width:8px;height:8px;border-radius:50%;background:<?= $color ?>;display:inline-block" aria-hidden="true"></span>
                  <?= htmlspecialchars($st) ?>
                </span>
                <span class="filter-count" data-status-count="<?= htmlspecialchars($st) ?>">–</span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Prioritas Filter (opsional) -->
          <div class="filter-group">
            <div class="filter-group-title">Tingkat Prioritas</div>
            <label class="filter-check-item">
              <input type="checkbox" name="filter_prioritas" value="Tinggi" class="filter-prioritas-cb"> Tinggi
            </label>
            <label class="filter-check-item">
              <input type="checkbox" name="filter_prioritas" value="Sedang" class="filter-prioritas-cb"> Sedang
            </label>
            <label class="filter-check-item">
              <input type="checkbox" name="filter_prioritas" value="Rendah" class="filter-prioritas-cb"> Rendah
            </label>
          </div>

          <!-- Jatuh Tempo (opsional) -->
          <div class="filter-group">
            <div class="filter-group-title">Jatuh Tempo</div>
            <label class="filter-check-item">
              <input type="radio" name="filter_tempo" value="hari_ini" class="filter-tempo-rb"> Hari Ini (&lt;24 Jam)
            </label>
            <label class="filter-check-item">
              <input type="radio" name="filter_tempo" value="minggu_ini" class="filter-tempo-rb"> Minggu Ini
            </label>
            <label class="filter-check-item">
              <input type="radio" name="filter_tempo" value="bulan_ini" class="filter-tempo-rb"> Bulan Ini
            </label>
            <label class="filter-check-item">
              <input type="radio" name="filter_tempo" value="semua" class="filter-tempo-rb" checked> Semua Waktu
            </label>
          </div>

          <div class="filter-panel-footer">
            <button class="btn btn-primary w-full" id="btn-terapkan-filter">Terapkan Filter</button>
            <button class="btn btn-secondary w-full" id="btn-reset-pilihan">Reset Pilihan</button>
          </div>
        </div>
      </aside>

      <!-- Results Panel -->
      <div>
        <!-- Search Bar -->
        <div class="search-bar-lg" role="search">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"/>
          </svg>
          <input type="search" id="search-input" placeholder="Cari judul tugas..."
                 aria-label="Cari tugas berdasarkan judul" autocomplete="off">
          <button id="btn-clear-search" aria-label="Hapus kata kunci pencarian" style="display:none;color:var(--c-text-muted)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Active Filter Chips -->
        <div class="active-filters" id="active-filters" aria-label="Filter aktif">
          <span class="active-filters-label">Filter Aktif:</span>
          <span id="filter-chip-container"></span>
        </div>

        <!-- Result info + sort -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px">
          <p class="result-info" id="result-info" aria-live="polite">Memuat...</p>
          <select class="filter-sort" id="result-sort" aria-label="Urutan hasil pencarian">
            <option value="deadline|asc">Deadline Terdekat</option>
            <option value="deadline|desc">Deadline Terjauh</option>
            <option value="judul|asc">Judul A–Z</option>
            <option value="created_at|desc">Terbaru</option>
          </select>
        </div>

        <!-- Results -->
        <div id="results-container">
          <div id="results-loading" role="status" aria-live="polite">
            <div class="skeleton" style="height:100px;border-radius:14px;margin-bottom:12px"></div>
            <div class="skeleton" style="height:100px;border-radius:14px;margin-bottom:12px"></div>
          </div>
          <div id="results-list" style="display:none"></div>
          <div id="results-empty" class="state-view" style="display:none" role="status">
            <div class="state-icon empty" aria-hidden="true">🔍</div>
            <div class="state-title">Tidak ada tugas yang cocok</div>
            <p class="state-desc">Coba ubah kata kunci atau filter.</p>
            <button class="btn btn-secondary" id="btn-results-reset">Reset Filter</button>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php renderMobileBar(); ?>

<script type="module">
import { api } from './assets/js/api.js';
import { toast } from './assets/js/toast.js';
import {
  createStatusBadge, createUrgentBadge,
  formatDeadlineText, formatSisaWaktu,
  updateSidebarCounts, setupTopbarSearch, setupMobileDrawer
} from './assets/js/ui.js';

// ---- State ----
let waktuServer = null;
let allMK = [];
let totalSemua = 0;
let debounceTimer = null;

setupTopbarSearch();
setupMobileDrawer();

// Read state dari URL query string
function getFiltersFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return {
    q:         params.get('q')        || '',
    status:    params.getAll('status'),
    id_mk:     params.getAll('id_mk').map(Number).filter(Boolean),
    prioritas: params.getAll('prioritas'),
    tempo:     params.get('tempo')    || 'semua',
    sort:      params.get('sort')     || 'deadline',
    order:     params.get('order')    || 'asc',
  };
}

let filters = getFiltersFromUrl();

// ---- Init ----
(async () => {
  await loadMataKuliah();
  await loadRingkasan();
  restoreFiltersToUI();
  await search();
})();

async function loadMataKuliah() {
  try {
    const res = await api.getDaftarMK();
    allMK = res.data || [];
    renderMatkulFilter(allMK);
  } catch (e) {}
}

async function loadRingkasan() {
  try {
    const res = await api.getRingkasan();
    const data = res.data;
    updateSidebarCounts(data);

    // Update status count
    const statusCounts = {
      'Belum': data.belum,
      'Dalam Proses': data.dalam_proses,
      'Selesai': data.selesai,
    };
    Object.entries(statusCounts).forEach(([st, cnt]) => {
      const el = document.querySelector(`[data-status-count="${st}"]`);
      if (el) el.textContent = cnt;
    });
  } catch (e) {}
}

function renderMatkulFilter(mks) {
  const container = document.getElementById('filter-matkul-list');
  container.innerHTML = '';

  mks.forEach(mk => {
    const label = document.createElement('label');
    label.className = 'filter-check-item';

    const cb = document.createElement('input');
    cb.type    = 'checkbox';
    cb.name    = 'filter_matkul';
    cb.value   = mk.id_mk;
    cb.className = 'filter-matkul-cb';
    cb.id      = `fmk-${mk.id_mk}`;
    cb.checked = filters.id_mk.includes(mk.id_mk);

    const dot = document.createElement('span');
    dot.style.cssText = `width:8px;height:8px;border-radius:50%;background:${mk.warna_label || '#4F46E5'};display:inline-block`;

    const nameText = document.createTextNode(mk.nama_mk); // §11

    const countSpan = document.createElement('span');
    countSpan.className = 'filter-count';
    countSpan.textContent = mk.jumlah_tugas || 0;

    const nameWrap = document.createElement('span');
    nameWrap.style.cssText = 'display:inline-flex;align-items:center;gap:6px';
    nameWrap.appendChild(dot);
    nameWrap.appendChild(nameText);

    label.appendChild(cb);
    label.appendChild(nameWrap);
    label.appendChild(countSpan);
    container.appendChild(label);
  });
}

function restoreFiltersToUI() {
  // Search
  const searchInput = document.getElementById('search-input');
  searchInput.value = filters.q;
  document.getElementById('btn-clear-search').style.display = filters.q ? '' : 'none';

  // Status
  filters.status.forEach(st => {
    const cb = document.getElementById(`fst-${st.replace(' ', '-')}`);
    if (cb) cb.checked = true;
  });

  // Prioritas
  filters.prioritas.forEach(p => {
    document.querySelectorAll(`.filter-prioritas-cb[value="${p}"]`).forEach(cb => cb.checked = true);
  });

  // Tempo
  const tempoRb = document.querySelector(`.filter-tempo-rb[value="${filters.tempo}"]`);
  if (tempoRb) tempoRb.checked = true;

  // Sort
  const sortEl = document.getElementById('result-sort');
  sortEl.value = `${filters.sort}|${filters.order}`;
}

// ---- Search ----
async function search() {
  const params = {};
  if (filters.q)         params.q         = filters.q;
  if (filters.status.length) params.status = filters.status.join(',');
  if (filters.id_mk.length)  params.id_mk  = filters.id_mk.join(',');
  if (filters.prioritas.length) params.prioritas = filters.prioritas.join(',');
  if (filters.tempo && filters.tempo !== 'semua') params.tempo = filters.tempo;
  params.sort  = filters.sort;
  params.order = filters.order;

  showResultsState('loading');

  try {
    const res = await api.getDaftarTugas(params);
    waktuServer = res.meta?.waktu_server;
    totalSemua  = res.meta?.total_semua || 0;
    const list  = res.data || [];

    // Update URL (history.replaceState) agar refresh mempertahankan filter (§8.7)
    const urlParams = new URLSearchParams();
    if (filters.q) urlParams.set('q', filters.q);
    filters.status.forEach(s    => urlParams.append('status',    s));
    filters.id_mk.forEach(id    => urlParams.append('id_mk',     id));
    filters.prioritas.forEach(p => urlParams.append('prioritas', p));
    if (filters.tempo !== 'semua') urlParams.set('tempo', filters.tempo);
    urlParams.set('sort',  filters.sort);
    urlParams.set('order', filters.order);
    history.replaceState(null, '', `?${urlParams.toString()}`);

    renderActiveFilterChips();
    renderResults(list, res.meta);

  } catch (err) {
    console.error('Search error:', err);
    showResultsState('error');
    toast.error('Gagal memuat hasil pencarian.');
  }
}

function renderResults(list, meta) {
  const totalHasil = list.length;
  document.getElementById('result-info').textContent =
    `Menampilkan ${totalHasil} dari ${totalSemua} tugas`;

  if (totalHasil === 0) {
    showResultsState('empty');
    return;
  }

  showResultsState('list');

  const container = document.getElementById('results-list');
  container.innerHTML = '';

  list.forEach(tugas => {
    const card = document.createElement('div');
    card.className = 'result-card';

    // Header
    const header = document.createElement('div');
    header.className = 'result-card-header';

    const tags = document.createElement('div');
    tags.className = 'result-card-tags';
    if (tugas.is_urgent) tags.appendChild(createUrgentBadge());
    // MK chip
    const mkChip = document.createElement('span');
    mkChip.className = 'badge';
    mkChip.style.cssText = `background:${(tugas.mata_kuliah?.warna_label || '#4F46E5')}20;color:${tugas.mata_kuliah?.warna_label || '#4F46E5'};border:1.5px solid ${(tugas.mata_kuliah?.warna_label || '#4F46E5')}40;font-size:var(--fs-xs)`;
    mkChip.textContent = tugas.mata_kuliah?.nama_mk || '–'; // §11
    tags.appendChild(mkChip);
    tags.appendChild(createStatusBadge(tugas.status));

    const judulLink = document.createElement('a');
    judulLink.href = `tugas.php?id=${tugas.id_tugas}`;
    judulLink.className = 'btn btn-secondary btn-sm';
    judulLink.textContent = 'Detail';

    header.appendChild(tags);
    header.appendChild(judulLink);
    card.appendChild(header);

    // Judul
    const judul = document.createElement('h3');
    judul.style.cssText = 'font-size:var(--fs-md);font-weight:700;color:var(--c-text);margin-bottom:4px';
    const judulA = document.createElement('a');
    judulA.href = `tugas.php?id=${tugas.id_tugas}`;
    judulA.className = 'tugas-judul-link';
    judulA.textContent = tugas.judul; // §11
    judul.appendChild(judulA);
    card.appendChild(judul);

    // Snippet catatan
    if (tugas.deskripsi) {
      const snippet = document.createElement('p');
      snippet.className = 'result-card-snippet';
      snippet.textContent = tugas.deskripsi; // §11
      card.appendChild(snippet);
    }

    // Footer
    const footer = document.createElement('div');
    footer.className = 'result-card-footer';

    const deadlineEl = document.createElement('div');
    deadlineEl.className = 'tugas-deadline' + (tugas.is_urgent ? ' urgent' : '');
    deadlineEl.style.fontSize = 'var(--fs-sm)';

    const deadlineIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
    deadlineEl.innerHTML = deadlineIcon + ' ';
    deadlineEl.appendChild(document.createTextNode(formatDeadlineText(tugas.deadline, waktuServer)));

    const sisaEl = document.createElement('span');
    sisaEl.className = 'deadline-sisa' + (tugas.sisa_detik < 0 ? ' terlambat' : '');
    sisaEl.style.marginLeft = '8px';
    sisaEl.textContent = tugas.status !== 'Selesai' ? formatSisaWaktu(tugas.sisa_detik) : '';
    deadlineEl.appendChild(sisaEl);

    footer.appendChild(deadlineEl);
    card.appendChild(footer);

    container.appendChild(card);
  });
}

function renderActiveFilterChips() {
  const container = document.getElementById('filter-chip-container');
  container.innerHTML = '';

  const addChip = (label, onRemove) => {
    const chip = document.createElement('span');
    chip.className = 'chip chip-removable';
    chip.style.fontSize = 'var(--fs-xs)';
    const labelText = document.createTextNode(label); // §11
    chip.appendChild(labelText);

    const x = document.createElement('button');
    x.className = 'chip-x';
    x.setAttribute('aria-label', `Hapus filter: ${label}`);
    x.innerHTML = '×';
    x.addEventListener('click', onRemove);
    chip.appendChild(x);
    container.appendChild(chip);
  };

  if (filters.q) {
    addChip(`Query: "${filters.q}"`, () => {
      filters.q = '';
      document.getElementById('search-input').value = '';
      document.getElementById('btn-clear-search').style.display = 'none';
      debounceSearch();
    });
  }

  filters.status.forEach(st => {
    addChip(`Status: ${st}`, () => {
      filters.status = filters.status.filter(s => s !== st);
      const cb = document.getElementById(`fst-${st.replace(' ', '-')}`);
      if (cb) cb.checked = false;
      debounceSearch();
    });
  });

  filters.id_mk.forEach(id => {
    const mk = allMK.find(m => m.id_mk === id);
    const label = mk?.nama_mk || `MK #${id}`;
    addChip(`Matkul: ${label}`, () => {
      filters.id_mk = filters.id_mk.filter(m => m !== id);
      const cb = document.getElementById(`fmk-${id}`);
      if (cb) cb.checked = false;
      debounceSearch();
    });
  });

  filters.prioritas.forEach(p => {
    addChip(`Prioritas: ${p}`, () => {
      filters.prioritas = filters.prioritas.filter(pr => pr !== p);
      document.querySelectorAll(`.filter-prioritas-cb[value="${p}"]`).forEach(cb => cb.checked = false);
      debounceSearch();
    });
  });

  if (filters.tempo && filters.tempo !== 'semua') {
    const tempoLabels = { hari_ini: 'Hari Ini', minggu_ini: 'Minggu Ini', bulan_ini: 'Bulan Ini' };
    addChip(`Tempo: ${tempoLabels[filters.tempo] || filters.tempo}`, () => {
      filters.tempo = 'semua';
      const rb = document.querySelector('.filter-tempo-rb[value="semua"]');
      if (rb) rb.checked = true;
      debounceSearch();
    });
  }
}

function showResultsState(state) {
  document.getElementById('results-loading').style.display = state === 'loading' ? '' : 'none';
  document.getElementById('results-list').style.display    = state === 'list'    ? '' : 'none';
  document.getElementById('results-empty').style.display   = state === 'empty'   ? 'flex' : 'none';
}

function debounceSearch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(search, 300);
}

function resetAllFilters() {
  filters = { q: '', status: [], id_mk: [], prioritas: [], tempo: 'semua', sort: 'deadline', order: 'asc' };
  document.getElementById('search-input').value = '';
  document.getElementById('btn-clear-search').style.display = 'none';
  document.querySelectorAll('.filter-status-cb, .filter-matkul-cb, .filter-prioritas-cb').forEach(cb => cb.checked = false);
  const tempoAll = document.querySelector('.filter-tempo-rb[value="semua"]');
  if (tempoAll) tempoAll.checked = true;
  document.getElementById('result-sort').value = 'deadline|asc';
  debounceSearch();
}

// ---- Event Listeners ----

// Search input - debounce 300ms
document.getElementById('search-input').addEventListener('input', (e) => {
  filters.q = e.target.value.trim();
  document.getElementById('btn-clear-search').style.display = filters.q ? '' : 'none';
  debounceSearch();
});

// Clear search
document.getElementById('btn-clear-search').addEventListener('click', () => {
  filters.q = '';
  document.getElementById('search-input').value = '';
  document.getElementById('btn-clear-search').style.display = 'none';
  debounceSearch();
});

// Sort
document.getElementById('result-sort').addEventListener('change', (e) => {
  const [sort, order] = e.target.value.split('|');
  filters.sort  = sort;
  filters.order = order;
  debounceSearch();
});

// Terapkan Filter button
document.getElementById('btn-terapkan-filter').addEventListener('click', () => {
  // Kumpulkan nilai dari checkbox
  filters.status    = [...document.querySelectorAll('.filter-status-cb:checked')].map(cb => cb.value);
  filters.id_mk     = [...document.querySelectorAll('.filter-matkul-cb:checked')].map(cb => parseInt(cb.value));
  filters.prioritas = [...document.querySelectorAll('.filter-prioritas-cb:checked')].map(cb => cb.value);
  const tempoRb = document.querySelector('.filter-tempo-rb:checked');
  filters.tempo = tempoRb?.value || 'semua';
  debounceSearch();
});

// Live filter on checkbox change
document.addEventListener('change', (e) => {
  if (e.target.classList.contains('filter-status-cb') ||
      e.target.classList.contains('filter-matkul-cb') ||
      e.target.classList.contains('filter-prioritas-cb') ||
      e.target.classList.contains('filter-tempo-rb')) {
    // Update filters
    filters.status    = [...document.querySelectorAll('.filter-status-cb:checked')].map(cb => cb.value);
    filters.id_mk     = [...document.querySelectorAll('.filter-matkul-cb:checked')].map(cb => parseInt(cb.value));
    filters.prioritas = [...document.querySelectorAll('.filter-prioritas-cb:checked')].map(cb => cb.value);
    const tempoRb = document.querySelector('.filter-tempo-rb:checked');
    filters.tempo = tempoRb?.value || 'semua';
    debounceSearch();
  }
});

// Reset buttons
document.getElementById('btn-reset-filter').addEventListener('click', resetAllFilters);
document.getElementById('btn-reset-pilihan').addEventListener('click', resetAllFilters);
document.getElementById('btn-results-reset').addEventListener('click', resetAllFilters);
</script>
</body>
</html>
