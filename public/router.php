<?php
/**
 * TugasKu - PHP Built-in Server Router
 * Jalankan: php -S localhost:8000 -t public public/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files if they exist
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Let built-in server handle it
}

// Route /api/* to api/index.php
if (strpos($uri, '/api') === 0) {
    require __DIR__ . '/api/index.php';
    return;
}

// Route PHP pages
$map = [
    '/'            => '/index.php',
    '/index.php'   => '/index.php',
    '/cari.php'    => '/cari.php',
    '/cari'        => '/cari.php',
    '/tugas.php'   => '/tugas.php',
    '/tugas'       => '/tugas.php',
    '/matakuliah.php' => '/matakuliah.php',
    '/matakuliah'  => '/matakuliah.php',
];

$file = $map[$uri] ?? null;
if ($file && file_exists(__DIR__ . $file)) {
    require __DIR__ . $file;
    return;
}

// Default to index.php
require __DIR__ . '/index.php';
