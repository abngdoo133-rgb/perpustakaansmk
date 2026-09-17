<?php

$role = $_SESSION['user']['role'] ?? '';
$isAdmin = ($role === 'admin');
$userId = (int)($_SESSION['user']['id'] ?? 0);
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

// Badges
$badgePending = 0;
$badgeActiveSiswa = 0;

if (isset($pdo)) {
    try {
        if ($isAdmin) {
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'menunggu'");
            $badgePending = (int) $stmtCount->fetchColumn();
        } else {
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ? AND status IN ('disetujui', 'dipinjam')");
            $stmtCount->execute([$userId]);
            $badgeActiveSiswa = (int) $stmtCount->fetchColumn();
        }
    } catch (Throwable $e) {
        // fail silently for badge
    }
}
?>

<!-- OVERLAY (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <!-- BRAND -->
    <div class="brand">
        <div class="brand-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                <path d="M6 6h10"/>
                <path d="M6 10h10"/>
            </svg>
        </div>

        <div class="brand-text">
            <strong>PERPUS SMK</strong>
            <small><?= $isAdmin ? 'Portal Admin' : 'Portal Siswa' ?></small>
        </div>

        <button type="button" class="sidebar-close" onclick="closeSidebar()" aria-label="Tutup Menu">
            &times;
        </button>
    </div>

    <!-- NAVIGATION MENU -->
    <nav class="sidebar-nav">

        <?php if ($isAdmin): ?>
            <!-- ADMIN MENU -->
            <div class="nav-section-title">Menu Utama</div>

            <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="buku.php" class="<?= in_array($currentPage, ['buku.php', 'tambah_buku.php', 'edit_buku.php']) ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                    <path d="M6 6h10"/>
                    <path d="M6 10h7"/>
                </svg>
                <span>Data Buku</span>
            </a>

            <a href="siswa.php" class="<?= in_array($currentPage, ['siswa.php', 'profil_siswa.php', 'edit_siswa.php']) ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Data Siswa</span>
            </a>

            <div class="nav-section-title">Sirkulasi & Laporan</div>

            <a href="peminjaman.php" class="<?= $currentPage === 'peminjaman.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" x2="16" y1="2" y2="6"/>
                    <line x1="8" x2="8" y1="2" y2="6"/>
                    <line x1="3" x2="21" y1="10" y2="10"/>
                    <path d="m9 16 2 2 4-4"/>
                </svg>
                <span>Peminjaman</span>
                <?php if ($badgePending > 0): ?>
                    <span class="nav-badge badge-amber" title="Menunggu Konfirmasi"><?= $badgePending ?></span>
                <?php endif; ?>
            </a>

            <a href="terlambat.php" class="<?= $currentPage === 'terlambat.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Buku Terlambat</span>
            </a>

            <a href="laporan.php" class="<?= $currentPage === 'laporan.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M3 3v18h18"/>
                    <path d="m19 9-5 5-4-4-3 3"/>
                </svg>
                <span>Laporan</span>
            </a>

            <div class="nav-section-title">Akun & Sistem</div>

            <a href="profil.php" class="<?= $currentPage === 'profil.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="8" r="5"/>
                    <path d="M20 21a8 8 0 1 0-16 0"/>
                </svg>
                <span>Profil Saya</span>
            </a>

            <a href="pengaturan.php" class="<?= $currentPage === 'pengaturan.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span>Pengaturan</span>
            </a>

        <?php else: ?>
            <!-- SISWA MENU -->
            <div class="nav-section-title">Perpustakaan</div>

            <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="buku.php" class="<?= $currentPage === 'buku.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                    <path d="M6 6h10"/>
                    <path d="M6 10h7"/>
                </svg>
                <span>Katalog Buku</span>
            </a>

            <a href="pinjam.php" class="<?= $currentPage === 'pinjam.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>
                <span>Pinjam Buku</span>
            </a>

            <a href="riwayat.php" class="<?= $currentPage === 'riwayat.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Riwayat Pinjam</span>
                <?php if ($badgeActiveSiswa > 0): ?>
                    <span class="nav-badge" title="Buku Sedang Dipinjam"><?= $badgeActiveSiswa ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-title">Akun</div>

            <a href="profil.php" class="<?= $currentPage === 'profil.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="8" r="5"/>
                    <path d="M20 21a8 8 0 1 0-16 0"/>
                </svg>
                <span>Profil Saya</span>
            </a>

            <a href="pengaturan.php" class="<?= $currentPage === 'pengaturan.php' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span>Ganti Password</span>
            </a>

        <?php endif; ?>

        <!-- LOGOUT -->
        <a class="logout-link" href="../logout.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" x2="9" y1="12" y2="12"/>
            </svg>
            <span>Keluar Akun</span>
        </a>

    </nav>
</aside>