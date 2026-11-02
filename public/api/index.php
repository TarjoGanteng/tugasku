<?php
/**
 * TugasKu - API Front Controller / Router
 * Semua endpoint di bawah /api
 * §7 Keamanan: display_errors=0, X-Content-Type-Options: nosniff
 */

// Bootstrap
ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta'); // WIB (A06)

// Load config & src
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/src/Db.php';
require_once dirname(__DIR__, 2) . '/src/Response.php';
require_once dirname(__DIR__, 2) . '/src/Validator.php';
require_once dirname(__DIR__, 2) . '/src/StatusFlow.php';
require_once dirname(__DIR__, 2) . '/src/TugasRepository.php';
require_once dirname(__DIR__, 2) . '/src/MataKuliahRepository.php';

// CORS untuk dev lokal
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Parse request path
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName  = dirname($_SERVER['SCRIPT_NAME'] ?? '/api/index.php');
$path = parse_url($requestUri, PHP_URL_PATH);

// Normalisasi path: strip prefix /api atau /tugasku/public/api
$path = preg_replace('#^.*/api#', '', $path);
$path = rtrim($path, '/') ?: '/';

$method = $_SERVER['REQUEST_METHOD'];

// Parse request body untuk mutasi
$body = [];
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
    // Validasi header X-Requested-With (§6, A14)
    $xRequestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if ($xRequestedWith !== 'TugasKu') {
        Response::badRequest('Header X-Requested-With: TugasKu diperlukan.');
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') === false) {
        Response::badRequest('Content-Type: application/json diperlukan.');
    }

    $rawBody = file_get_contents('php://input');
    $body = json_decode($rawBody, true) ?? [];
}

// PDO instance
$pdo = Db::getInstance();

// Repositories
$tugasRepo = new TugasRepository($pdo);
$mkRepo    = new MataKuliahRepository($pdo);
$statusFlow = new StatusFlow($pdo);
$validator  = new Validator();

// Helper: waktu server WIB untuk respons
function waktuServer(): string {
    return date('Y-m-d H:i:s');
}

// =============================================
// ROUTING
// =============================================

try {
    // --- GET /api/ringkasan ---
    if ($method === 'GET' && $path === '/ringkasan') {
        $ringkasan = $tugasRepo->getRingkasan();
        $ringkasan['waktu_server'] = waktuServer();
        Response::success($ringkasan, 'Ringkasan berhasil diambil.');
    }

    // --- GET /api/matakuliah ---
    if ($method === 'GET' && $path === '/matakuliah') {
        $daftar = $mkRepo->getDaftar();
        Response::success($daftar, '', 200, [
            'total_hasil' => count($daftar),
            'total_semua' => count($daftar),
        ]);
    }

    // --- POST /api/matakuliah ---
    if ($method === 'POST' && $path === '/matakuliah') {
        $errors = $mkRepo->validate($body);
        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = [
            'nama_mk'     => trim(strip_tags($body['nama_mk'] ?? '')),
            'kode_mk'     => trim(strip_tags($body['kode_mk'] ?? '')),
            'dosen'       => trim(strip_tags($body['dosen'] ?? '')),
            'sks'         => is_numeric($body['sks'] ?? '') ? (int)$body['sks'] : null,
            'warna_label' => trim($body['warna_label'] ?? ''),
        ];

        $mk = $mkRepo->create($data);
        Response::success($mk, 'Mata kuliah berhasil ditambahkan.', 201);
    }

    // --- PUT /api/matakuliah/{id} ---
    if ($method === 'PUT' && preg_match('#^/matakuliah/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
        $errors = $mkRepo->validate($body, $id);
        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = [
            'nama_mk'     => trim(strip_tags($body['nama_mk'] ?? '')),
            'kode_mk'     => trim(strip_tags($body['kode_mk'] ?? '')),
            'dosen'       => trim(strip_tags($body['dosen'] ?? '')),
            'sks'         => is_numeric($body['sks'] ?? '') ? (int)$body['sks'] : null,
            'warna_label' => trim($body['warna_label'] ?? ''),
        ];

        $mk = $mkRepo->update($id, $data);
        if ($mk === null) {
            Response::notFound('Mata kuliah tidak ditemukan.');
        }
        Response::success($mk, 'Mata kuliah berhasil diperbarui.');
    }

    // --- DELETE /api/matakuliah/{id} ---
    if ($method === 'DELETE' && preg_match('#^/matakuliah/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
        $result = $mkRepo->delete($id);

        switch ($result) {
            case 'NOT_FOUND':
                Response::notFound('Mata kuliah tidak ditemukan.');
                break;
            case 'MK_MASIH_DIPAKAI':
                Response::error('MK_MASIH_DIPAKAI', 'Mata kuliah masih memiliki tugas dan tidak dapat dihapus.', 409);
                break;
            case 'OK':
                Response::success(null, 'Mata kuliah berhasil dihapus.');
                break;
        }
    }

    // --- GET /api/tugas ---
    if ($method === 'GET' && $path === '/tugas') {
        $params = $_GET;

        // Validasi query q > 150 karakter (A12)
        if (isset($params['q']) && mb_strlen(trim($params['q'])) > 150) {
            Response::validationError(['q' => 'Kata kunci pencarian maksimal 150 karakter.']);
        }

        // Validasi status (A12)
        if (!empty($params['status'])) {
            $statuses = array_map('trim', explode(',', $params['status']));
            $allowed  = ['Belum', 'Dalam Proses', 'Selesai'];
            foreach ($statuses as $s) {
                if (!in_array($s, $allowed, true)) {
                    Response::validationError(['status' => 'Nilai status tidak valid: ' . $s]);
                }
            }
        }

        // Validasi id_mk (A12)
        if (!empty($params['id_mk'])) {
            $mks = array_map('trim', explode(',', $params['id_mk']));
            foreach ($mks as $mk) {
                if (!is_numeric($mk) || (int)$mk <= 0) {
                    Response::validationError(['id_mk' => 'ID mata kuliah tidak valid.']);
                }
            }
        }

        // Validasi sort & order (A12)
        $sortAllowed  = ['deadline', 'judul', 'created_at'];
        $orderAllowed = ['asc', 'desc'];
        if (!empty($params['sort']) && !in_array($params['sort'], $sortAllowed, true)) {
            Response::validationError(['sort' => 'Nilai sort tidak valid.']);
        }
        if (!empty($params['order']) && !in_array($params['order'], $orderAllowed, true)) {
            Response::validationError(['order' => 'Nilai order tidak valid.']);
        }

        // Validasi tempo
        $tempoAllowed = ['hari_ini', 'minggu_ini', 'bulan_ini', 'semua'];
        if (!empty($params['tempo']) && !in_array($params['tempo'], $tempoAllowed, true)) {
            Response::validationError(['tempo' => 'Nilai tempo tidak valid.']);
        }

        $daftar     = $tugasRepo->getDaftar($params);
        $totalSemua = count($tugasRepo->getDaftar()); // total tanpa filter

        Response::success($daftar, '', 200, [
            'total_hasil' => count($daftar),
            'total_semua' => $totalSemua,
            'waktu_server' => waktuServer(),
        ]);
    }

    // --- GET /api/tugas/{id} ---
    if ($method === 'GET' && preg_match('#^/tugas/(\d+)$#', $path, $m)) {
        $id    = (int)$m[1];
        $tugas = $tugasRepo->getById($id);
        if ($tugas === null) {
            Response::notFound('Tugas tidak ditemukan.');
        }
        $tugas['waktu_server'] = waktuServer();
        Response::success($tugas, 'Detail tugas berhasil diambil.');
    }

    // --- POST /api/tugas ---
    if ($method === 'POST' && $path === '/tugas') {
        $errors = [];
        $v = new Validator();

        // Validasi field
        $judul    = $v->validateJudul($body['judul'] ?? null);
        $idMk     = $v->validateIdMk($body['id_mk'] ?? null, $pdo);
        $deadline = $v->validateDeadline($body['deadline'] ?? null, true);
        $prioritas = $v->validatePrioritas($body['prioritas'] ?? null);
        $deskripsi = $v->validateDeskripsi($body['deskripsi'] ?? null);

        if ($v->hasErrors()) {
            Response::validationError($v->getErrors());
        }

        $tugas = $tugasRepo->create([
            'judul'     => $judul,
            'id_mk'     => $idMk,
            'deadline'  => $deadline,
            'prioritas' => $prioritas,
            'deskripsi' => $deskripsi,
        ]);
        $tugas['waktu_server'] = waktuServer();
        Response::success($tugas, 'Tugas berhasil ditambahkan.', 201);
    }

    // --- PUT /api/tugas/{id} ---
    if ($method === 'PUT' && preg_match('#^/tugas/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
        $v  = new Validator();

        // Cek field terlarang (A5)
        $v->validateNoForbiddenFields($body);

        // Ambil deadline saat ini untuk BR-02 (ubah field lain tanpa mengubah deadline)
        $currentDeadline = $tugasRepo->getDeadline($id);
        if ($currentDeadline === null) {
            Response::notFound('Tugas tidak ditemukan.');
        }

        // Cek apakah deadline di-kirim atau tidak
        $deadlineChanged = array_key_exists('deadline', $body);
        $checkPast = $deadlineChanged; // hanya cek past jika deadline diubah (BR-02)

        $judul    = $v->validateJudul($body['judul'] ?? null);
        $idMk     = $v->validateIdMk($body['id_mk'] ?? null, $pdo);
        $deadline = $deadlineChanged
            ? $v->validateDeadline($body['deadline'] ?? null, true)
            : $currentDeadline; // gunakan deadline lama
        $prioritas = $v->validatePrioritas($body['prioritas'] ?? null);
        $deskripsi = $v->validateDeskripsi($body['deskripsi'] ?? null);

        if ($v->hasErrors()) {
            Response::validationError($v->getErrors());
        }

        $tugas = $tugasRepo->update($id, [
            'judul'     => $judul,
            'id_mk'     => $idMk,
            'deadline'  => $deadline,
            'prioritas' => $prioritas,
            'deskripsi' => $deskripsi,
        ]);

        if ($tugas === null) {
            Response::notFound('Tugas tidak ditemukan.');
        }
        $tugas['waktu_server'] = waktuServer();
        Response::success($tugas, 'Tugas berhasil diperbarui.');
    }

    // --- PATCH /api/tugas/{id}/status ---
    if ($method === 'PATCH' && preg_match('#^/tugas/(\d+)/status$#', $path, $m)) {
        $id = (int)$m[1];
        $v  = new Validator();

        $status = $v->validateStatus($body['status'] ?? null);
        if ($v->hasErrors()) {
            Response::validationError($v->getErrors());
        }

        try {
            $result = $statusFlow->ubahStatus($id, $status);
            $tugas  = $tugasRepo->getById($id);
            if ($tugas) $tugas['waktu_server'] = waktuServer();
            Response::success(array_merge($result, ['tugas' => $tugas]), 'Status diperbarui.');
        } catch (RuntimeException $e) {
            [$code, $msg] = explode(':', $e->getMessage(), 2) + ['SERVER_ERROR', 'Error'];
            if ($code === 'NOT_FOUND') Response::notFound($msg);
            Response::serverError($e->getMessage());
        }
    }

    // --- DELETE /api/tugas/{id} ---
    if ($method === 'DELETE' && preg_match('#^/tugas/(\d+)$#', $path, $m)) {
        $id = (int)$m[1];
        $v  = new Validator();

        // Validasi konfirmasi
        $v->validateKonfirmasi($body['konfirmasi'] ?? null);
        if ($v->hasErrors()) {
            Response::validationError($v->getErrors());
        }

        // Cek ada tidak
        $existing = $tugasRepo->getById($id);
        if ($existing === null) {
            Response::notFound('Tugas tidak ditemukan.');
        }

        $deleted = $tugasRepo->delete($id);
        if (!$deleted) {
            Response::notFound('Tugas tidak ditemukan.');
        }

        Response::success(null, 'Tugas berhasil dihapus.');
    }

    // Tidak ada route yang cocok
    Response::notFound('Endpoint tidak ditemukan.');

} catch (PDOException $e) {
    error_log('[TugasKu] PDO Exception: ' . $e->getMessage());
    Response::serverError($e->getMessage());
} catch (Throwable $e) {
    error_log('[TugasKu] Exception: ' . $e->getMessage());
    Response::serverError($e->getMessage());
}
