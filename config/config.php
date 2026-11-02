<?php
/**
 * TugasKu - Konfigurasi Database
 * Ubah config.local.php (di-gitignore) untuk override
 */

// Default config
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'tugasku');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Timezone WIB (A06)
define('APP_TIMEZONE', 'Asia/Jakarta');
define('APP_NAME', 'TugasKu');
define('APP_VERSION', '1.0.0');

// Load local override if exists
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}
