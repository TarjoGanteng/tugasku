/**
 * TugasKu - API Client (api.js)
 * Base URL, header JSON + X-Requested-With, timeout 10 detik
 * §8.9: satu api.js dipakai di semua halaman
 */

// Deteksi base URL secara dinamis
function getBaseUrl() {
  const { protocol, host, pathname } = window.location;
  // Jika di XAMPP: /tugasku/public/...
  const match = pathname.match(/^(\/[^/]*\/[^/]*)\//);
  const basePath = match ? match[1] : '';
  return `${protocol}//${host}${basePath}/api`;
}

export const API_BASE = getBaseUrl();
export const TIMEOUT_MS = 10000; // 10 detik (NFR-02)

/**
 * Fetch dengan timeout dan header standar
 * @param {string} endpoint - path relative ke /api
 * @param {object} options  - fetch options
 */
export async function apiFetch(endpoint, options = {}) {
  const controller = new AbortController();
  const timeoutId  = setTimeout(() => controller.abort(), TIMEOUT_MS);

  const url = `${API_BASE}${endpoint}`;
  const startTime = performance.now();

  try {
    const isGet = !options.method || options.method === 'GET';

    const headers = {
      'Content-Type': 'application/json',
      ...(isGet ? {} : { 'X-Requested-With': 'TugasKu' }),
      ...options.headers,
    };

    const resp = await fetch(url, {
      ...options,
      headers,
      signal: controller.signal,
    });

    const elapsed = Math.round(performance.now() - startTime);
    console.debug(`[TugasKu API] ${options.method || 'GET'} ${endpoint} → ${resp.status} (${elapsed}ms)`);

    const json = await resp.json().catch(() => null);

    if (!resp.ok) {
      // Kembalikan struktur error seragam
      throw {
        status:  resp.status,
        code:    json?.error?.code    || 'SERVER_ERROR',
        message: json?.error?.message || 'Terjadi kesalahan.',
        fields:  json?.error?.fields  || {},
        raw:     json,
      };
    }

    return { data: json?.data, meta: json?.meta, message: json?.message, raw: json };

  } catch (err) {
    if (err.name === 'AbortError') {
      throw { status: 0, code: 'TIMEOUT', message: 'Permintaan melebihi batas waktu.', fields: {} };
    }
    if (err.status !== undefined) throw err; // sudah diformat
    throw { status: 0, code: 'NETWORK_ERROR', message: 'Gagal terhubung ke server.', fields: {} };
  } finally {
    clearTimeout(timeoutId);
  }
}

// ---- Convenience methods ----

export const api = {
  // Tugas
  getDaftarTugas: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return apiFetch(`/tugas${qs ? '?' + qs : ''}`);
  },
  getDetailTugas: (id) => apiFetch(`/tugas/${id}`),
  createTugas:    (data) => apiFetch('/tugas', { method: 'POST', body: JSON.stringify(data) }),
  updateTugas:    (id, data) => apiFetch(`/tugas/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  ubahStatus:     (id, status) => apiFetch(`/tugas/${id}/status`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  }),
  hapusTugas:     (id) => apiFetch(`/tugas/${id}`, {
    method: 'DELETE',
    body: JSON.stringify({ konfirmasi: true }),
  }),

  // Ringkasan
  getRingkasan: () => apiFetch('/ringkasan'),

  // Mata Kuliah
  getDaftarMK:   () => apiFetch('/matakuliah'),
  createMK:      (data) => apiFetch('/matakuliah', { method: 'POST', body: JSON.stringify(data) }),
  updateMK:      (id, data) => apiFetch(`/matakuliah/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  hapusMK:       (id) => apiFetch(`/matakuliah/${id}`, { method: 'DELETE', body: JSON.stringify({ konfirmasi: true }) }),
};
