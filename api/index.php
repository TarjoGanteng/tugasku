<?php
/**
 * TugasKu - Entry point Vercel (runtime vercel-php)
 * Vercel hanya menjalankan PHP di folder /api, jadi semua request non-statis
 * diarahkan ke sini lalu didelegasikan ke file di /public.
 * Lokal/XAMPP tidak memakai file ini.
 */

$publicDir = dirname(__DIR__) . '/public';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// API JSON → front controller API
if (preg_match('#^/api(/|$)#', $path)) {
    require $publicDir . '/api/index.php';
    exit;
}

// Halaman yang diizinkan
$pages = [
    '/'               => 'index.php',
    '/index.php'      => 'index.php',
    '/cari.php'       => 'cari.php',
    '/tugas.php'      => 'tugas.php',
    '/matakuliah.php' => 'matakuliah.php',
];

if (isset($pages[$path])) {
    chdir($publicDir);
    require $publicDir . '/' . $pages[$path];
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404 - Halaman tidak ditemukan.';
