<?php
/**
 * TugasKu - Konfigurasi Database
 * Ubah config.local.php (di-gitignore) untuk override
 */

// Default config (mendukung Environment Variables untuk Cloud/Vercel/Railway)
define('DB_HOST', getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1'));
define('DB_PORT', getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306'));
define('DB_NAME', getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'tugasku'));
define('DB_USER', getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : ''));
define('DB_CHARSET', 'utf8mb4');

// Timezone WIB (A06)
define('APP_TIMEZONE', 'Asia/Jakarta');
define('APP_NAME', 'TugasKu');
define('APP_VERSION', '1.0.0');

// Load local override if exists
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}
