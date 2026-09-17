<?php

$isAdmin = (($_SESSION['user']['role'] ?? '') === 'admin');
$isSiswa = (($_SESSION['user']['role'] ?? '') === 'siswa');
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isProfilSiswa = (($page_title ?? '') === 'Profil Siswa');

// Foto User
$fotoTopbar = '';
$fotoUrl = '../assets/images/avatar.svg';

if ($isAdmin && $isProfilSiswa && isset($siswa) && is_array($siswa)) {
    $fotoTopbar = trim($siswa['foto'] ?? '');
} else {
    $fotoTopbar = trim($_SESSION['user']['foto'] ?? '');
}

if ($fotoTopbar !== '') {
    $fotoNamaAman = basename($fotoTopbar);
    $fotoPath = __DIR__ . '/../uploads/profile/' . $fotoNamaAman;

    if (is_file($fotoPath)) {
        $versiFoto = filemtime($fotoPath);
        $idFoto = ($isProfilSiswa && isset($siswa['id'])) ? (int)$siswa['id'] : (int)($_SESSION['user']['id'] ?? 0);
        $fotoUrl = '../uploads/profile/' . rawurlencode($fotoNamaAman) . '?v=' . $versiFoto . '&user=' . $idFoto;
    }
}

$isDashboardAdmin = ($isAdmin && $currentPage === 'dashboard.php');
$isDashboardSiswa = ($isSiswa && $currentPage === 'dashboard.php');
$showBack = (!$isDashboardAdmin && !$isDashboardSiswa && !$isProfilSiswa);

// Tanggal Indonesia
$hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tglSekarang = $hariIndo[(int)date('w')] . ', ' . date('j') . ' ' . $bulanIndo[(int)date('n')] . ' ' . date('Y');
?>

<header class="topbar">
    <!-- KIRI: Hamburger & Breadcrumb -->
    <div class="topbar-left">
        <button type="button" class="hamburger-btn" onclick="openSidebar()" aria-label="Buka menu navigasi">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <?php if ($showBack): ?>
            <a href="dashboard.php" class="topbar-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <div class="topbar-breadcrumbs">
            <span class="badge-role"><?= $isAdmin ? 'Admin Perpustakaan' : 'Siswa SMK' ?></span>
            <h2><?= htmlspecialchars($page_title ?? 'Perpustakaan SMK', ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
    </div>

    <!-- KANAN: Tanggal, Notifikasi & Profil -->
    <div class="topbar-right">
        <div class="topbar-date">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span><?= $tglSekarang ?></span>
        </div>

        <?php if ($isDashboardAdmin): ?>
            <!-- LONCENG ADMIN -->
            <div class="notification-wrapper" id="notificationWrapper">
                <button type="button" class="notification-btn" id="notificationButton" onclick="toggleNotifications()" aria-label="Notifikasi Peminjaman" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span id="notificationDot" class="notification-dot" style="display:none;"></span>
                </button>

                <div id="notificationDropdown" class="notification-dropdown" aria-hidden="true">
                    <div class="notification-header">
                        <strong>Peminjaman Terbaru</strong>
                    </div>
                    <div id="notificationList" class="notification-list">
                        <div class="notification-empty">Memuat notifikasi...</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- USER PILL -->
        <a href="profil.php" class="topbar-user" title="Lihat Profil Saya">
            <img class="topbar-avatar" src="<?= e($fotoUrl) ?>" alt="Foto Profil">
            <div class="topbar-user-info">
                <strong>
                    <?php if ($isProfilSiswa && isset($siswa['nama'])): ?>
                        <?= e($siswa['nama']) ?>
                    <?php else: ?>
                        <?= e($_SESSION['user']['nama'] ?? 'User') ?>
                    <?php endif; ?>
                </strong>
                <span><?= $isAdmin ? 'Administrator' : e($_SESSION['user']['kelas'] ?? 'Siswa') ?></span>
            </div>
        </a>
    </div>
</header>