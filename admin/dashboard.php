<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Dashboard Admin';
$base = '../';

date_default_timezone_set('Asia/Jakarta');
$sekarang = time();

/* ==========================================
   STATISTIK ADMIN
   ========================================== */
// Total Siswa Aktif
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'siswa' AND status = 'aktif'");
$totalSiswa = (int) $stmt->fetchColumn();

// Total Judul Buku
$stmt = $pdo->query("SELECT COUNT(*) FROM buku");
$totalBuku = (int) $stmt->fetchColumn();

// Total Stok & Tersedia
$stmt = $pdo->query("SELECT COALESCE(SUM(stok_total), 0) AS stok_total, COALESCE(SUM(stok_tersedia), 0) AS stok_tersedia FROM buku");
$stokInfo = $stmt->fetch(PDO::FETCH_ASSOC);
$totalStok = (int)($stokInfo['stok_total'] ?? 0);
$stokTersedia = (int)($stokInfo['stok_tersedia'] ?? 0);

// Peminjaman Aktif
$stmt = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status IN ('disetujui','dipinjam')");
$peminjamanAktif = (int) $stmt->fetchColumn();

// Menunggu Persetujuan
$stmt = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'menunggu'");
$menunggu = (int) $stmt->fetchColumn();

// Peminjaman Terlambat
$stmt = $pdo->query("SELECT tanggal_rencana_kembali, waktu_rencana_kembali FROM peminjaman WHERE status IN ('disetujui','dipinjam')");
$terlambat = 0;
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $loan) {
    $waktuKembali = !empty($loan['waktu_rencana_kembali']) ? $loan['waktu_rencana_kembali'] : '23:59:59';
    $batas = strtotime($loan['tanggal_rencana_kembali'] . ' ' . $waktuKembali);
    if ($batas < $sekarang) {
        $terlambat++;
    }
}

// Peminjaman Terbaru (Limit 6)
$stmt = $pdo->query("
    SELECT p.*, u.foto, u.email
    FROM peminjaman p
    LEFT JOIN users u ON u.id = p.user_id
    ORDER BY p.id DESC
    LIMIT 6
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tanggal Indonesia
$hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tglFormatted = $hariIndo[(int)date('w')] . ', ' . date('j') . ' ' . $bulanIndo[(int)date('n')] . ' ' . date('Y');

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <!-- HERO BANNER ADMIN -->
    <div class="hero-banner">
        <div class="hero-content">
            <span class="hero-pill">
                🏛️ Panel Administrator Perpustakaan
            </span>
            <h1>Selamat Datang, <?= e($_SESSION['user']['nama'] ?? 'Admin') ?>! 👋</h1>
            <p>
                Sistem monitoring perpustakaan aktif hari ini: <strong><?= $tglFormatted ?></strong>. Pantau sirkulasi peminjaman buku dan kelola data siswa secara terpadu.
            </p>
            <div class="hero-actions">
                <a href="tambah_buku.php" class="btn btn-white">
                    ➕ Tambah Buku Baru
                </a>
                <a href="peminjaman.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                    📋 Kelola Peminjaman
                </a>
                <a href="siswa.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                    👥 Data Siswa
                </a>
            </div>
        </div>
    </div>

    <!-- STAT CARDS (KPIs) -->
    <div class="stat-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Siswa Terdaftar</span>
                <strong class="stat-value"><?= number_format($totalSiswa) ?></strong>
                <span class="stat-desc">Siswa aktif terdata</span>
            </div>
        </div>

        <div class="stat-card emerald">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                    <path d="M6 6h10"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Judul Buku</span>
                <strong class="stat-value"><?= number_format($totalBuku) ?></strong>
                <span class="stat-desc"><?= number_format($stokTersedia) ?> stok tersedia</span>
            </div>
        </div>

        <div class="stat-card indigo">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" x2="16" y1="2" y2="6"/>
                    <line x1="8" x2="8" y1="2" y2="6"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Peminjaman Aktif</span>
                <strong class="stat-value"><?= number_format($peminjamanAktif) ?></strong>
                <span class="stat-desc">Buku sedang dipinjam</span>
            </div>
        </div>

        <div class="stat-card <?= $menunggu > 0 ? 'amber' : 'primary' ?>">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Perlu Persetujuan</span>
                <strong class="stat-value"><?= number_format($menunggu) ?></strong>
                <span class="stat-desc">Permintaan baru</span>
            </div>
        </div>

        <div class="stat-card <?= $terlambat > 0 ? 'rose' : 'emerald' ?>">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Buku Terlambat</span>
                <strong class="stat-value"><?= number_format($terlambat) ?></strong>
                <span class="stat-desc"><?= $terlambat > 0 ? 'Lewat batas waktu' : 'Semua tertib' ?></span>
            </div>
        </div>
    </div>

    <!-- TABEL TRANSAKSI TERBARU -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    Aktivitas Peminjaman Terbaru
                </h3>
                <p class="card-subtitle">Daftar transaksi peminjaman buku terbaru oleh siswa.</p>
            </div>
            <a href="peminjaman.php" class="btn btn-secondary btn-sm">
                Lihat Seluruh Peminjaman &rarr;
            </a>
        </div>

        <?php if (!empty($rows)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Buku Dipinjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Status</th>
                            <th>Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $isOverdue = false;
                            if (in_array($r['status'], ['disetujui', 'dipinjam'], true)) {
                                $waktuKembali = !empty($r['waktu_rencana_kembali']) ? $r['waktu_rencana_kembali'] : '23:59:59';
                                $batas = strtotime($r['tanggal_rencana_kembali'] . ' ' . $waktuKembali);
                                if ($batas < $sekarang) {
                                    $isOverdue = true;
                                }
                            }
                            ?>
                            <tr class="<?= $isOverdue ? 'baris-terlambat' : '' ?>">
                                <td>
                                    <div class="user-cell">
                                        <img src="<?= !empty($r['foto']) ? '../uploads/profile/' . e($r['foto']) : '../assets/images/avatar.svg' ?>" class="user-cell-avatar" alt="">
                                        <div class="user-cell-meta">
                                            <strong><?= e($r['nama_siswa']) ?></strong>
                                            <small><?= e($r['kelas']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= e($r['nama_buku']) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-500);"><?= (int)$r['jumlah'] ?> eksemplar</small>
                                </td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($r['tanggal_pinjam'])) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-400);"><?= e(substr($r['waktu_mulai'] ?? '00:00', 0, 5)) ?> WIB</small>
                                </td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($r['tanggal_rencana_kembali'])) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-400);"><?= e(substr($r['waktu_rencana_kembali'] ?? '15:00', 0, 5)) ?> WIB</small>
                                </td>
                                <td>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge badge-terlambat">Terlambat</span>
                                    <?php else: ?>
                                        <span class="badge badge-<?= e($r['status']) ?>"><?= ucfirst(e($r['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="../proses/status_peminjaman.php" style="display: flex; gap: 6px;">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <?php if ($r['status'] === 'menunggu'): ?>
                                            <input type="hidden" name="status" value="disetujui">
                                            <button type="submit" class="btn btn-success btn-sm" title="Setujui Peminjaman">
                                                ✓ Setujui
                                            </button>
                                        <?php elseif (in_array($r['status'], ['disetujui', 'dipinjam'], true)): ?>
                                            <input type="hidden" name="status" value="dikembalikan">
                                            <button type="submit" class="btn btn-primary btn-sm" title="Tandai Sudah Dikembalikan">
                                                📥 Kembalikan
                                            </button>
                                        <?php else: ?>
                                            <a href="peminjaman.php" class="btn btn-light btn-sm">
                                                Detail
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 36px; color: var(--slate-500);">
                Belum ada data transaksi peminjaman.
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>