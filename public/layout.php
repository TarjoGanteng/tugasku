<?php
/**
 * TugasKu - Shared PHP Functions & Layout
 * Diinclude oleh semua halaman
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta'); // WIB A06

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/src/Db.php';

/**
 * Render topbar HTML
 * @param string $activePage - 'dashboard' | 'cari' | 'matakuliah'
 */
function renderTopbar(string $activePage = 'dashboard'): void {
    $pages = [
        'dashboard'  => ['href' => 'index.php',      'label' => 'Dashboard'],
        'cari'       => ['href' => 'cari.php',        'label' => 'Cari & Filter'],
        'matakuliah' => ['href' => 'matakuliah.php',  'label' => 'Mata Kuliah'],
    ];
    ?>
    <header class="topbar" role="banner">
      <a href="index.php" class="topbar-logo" aria-label="TugasKu - Kembali ke Dashboard">
        <div class="topbar-logo-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 4H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2h-2M9 4h6a1 1 0 010 2H9a1 1 0 010-2z"/>
          </svg>
        </div>
        <span class="topbar-brand">TugasKu</span>
        <span class="topbar-subtitle">Kelompok 02</span>
      </a>

      <nav class="topbar-nav" aria-label="Navigasi utama">
        <?php foreach ($pages as $key => $page): ?>
          <a href="<?= $page['href'] ?>" data-page="<?= $key ?>"
             class="<?= $activePage === $key ? 'active' : '' ?>"
             <?= $activePage === $key ? 'aria-current="page"' : '' ?>>
            <?= htmlspecialchars($page['label']) ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="topbar-spacer"></div>

      <div class="topbar-search-wrap">
        <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"/>
        </svg>
        <input type="search" class="topbar-search" placeholder="Cari tugas..."
               aria-label="Cari tugas" id="topbar-search-input">
      </div>

      <a href="#" id="btn-tambah-tugas-topbar" class="topbar-btn-add" aria-label="Tambah tugas baru">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        + Tambah Tugas
      </a>
    </header>
    <?php
}

/**
 * Render sidebar HTML
 */
function renderSidebar(string $activePage = 'dashboard'): void {
    ?>
    <aside class="sidebar" aria-label="Navigasi ruang kerja">
      <div>
        <div class="sidebar-section-title">Navigasi Ruang Kerja</div>
        <nav class="sidebar-nav">
          <a href="index.php" data-page="dashboard"
             class="<?= $activePage === 'dashboard' ? 'active' : '' ?>"
             <?= $activePage === 'dashboard' ? 'aria-current="page"' : '' ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard Utama
          </a>
          <a href="cari.php" data-page="cari"
             class="<?= $activePage === 'cari' ? 'active' : '' ?>"
             <?= $activePage === 'cari' ? 'aria-current="page"' : '' ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"/>
            </svg>
            Pencarian & Filter
          </a>
          <a href="matakuliah.php" data-page="matakuliah"
             class="<?= $activePage === 'matakuliah' ? 'active' : '' ?>"
             <?= $activePage === 'matakuliah' ? 'aria-current="page"' : '' ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Daftar Mata Kuliah
          </a>
        </nav>
      </div>

      <div>
        <div class="sidebar-section-title">Status Tugas</div>
        <div class="sidebar-nav">
          <a href="index.php?status=Belum" class="sidebar-status-item">
            <span class="sidebar-status-dot" style="background:#DC2626" aria-hidden="true"></span>
            Belum
            <span class="sidebar-status-count" data-sidebar-count="Belum" aria-label="jumlah tugas belum">-</span>
          </a>
          <a href="index.php?status=Dalam+Proses" class="sidebar-status-item">
            <span class="sidebar-status-dot" style="background:#F5B301" aria-hidden="true"></span>
            Dalam Proses
            <span class="sidebar-status-count" data-sidebar-count="Dalam Proses" aria-label="jumlah tugas dalam proses">-</span>
          </a>
          <a href="index.php?status=Selesai" class="sidebar-status-item">
            <span class="sidebar-status-dot" style="background:#16A34A" aria-hidden="true"></span>
            Selesai
            <span class="sidebar-status-count" data-sidebar-count="Selesai" aria-label="jumlah tugas selesai">-</span>
          </a>
        </div>
      </div>
    </aside>
    <?php
}

/**
 * Render mobile bottom bar + FAB + drawer
 */
function renderMobileBar(): void {
    ?>
    <div class="mobile-bottom-bar" aria-label="Aksi utama mobile">
      <button class="btn btn-primary w-full" id="btn-tambah-tugas-mobile" aria-label="Tambah tugas baru">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        + Tambah Tugas
      </button>
    </div>

    <button class="mobile-fab" id="mobile-fab" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobile-drawer-overlay">
      ☰
    </button>

    <div class="mobile-drawer-overlay" id="mobile-drawer-overlay" aria-hidden="true" style="display:none">
      <div class="mobile-drawer" role="dialog" aria-label="Menu navigasi">
        <div class="mobile-drawer-handle" aria-hidden="true"></div>
        <nav class="mobile-drawer-nav">
          <a href="index.php" class="mobile-drawer-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
          </a>
          <a href="cari.php" class="mobile-drawer-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"/>
            </svg>
            Cari & Filter
          </a>
          <a href="matakuliah.php" class="mobile-drawer-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Mata Kuliah
          </a>
        </nav>
      </div>
    </div>
    <?php
}

/**
 * Render opening <html> tags + head
 */
function renderHead(string $title = 'TugasKu', string $desc = 'Sistem Manajemen Tugas Mahasiswa'): void {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($desc) ?>">
  <meta name="theme-color" content="#4F46E5">
  <title><?= htmlspecialchars($title) ?> | TugasKu</title>
  <link rel="stylesheet" href="assets/css/app.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
    <?php
}
