<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

require_role('siswa');

date_default_timezone_set('Asia/Jakarta');

$page_title = 'Dashboard Siswa';
$base = '../';

$id = (int)($_SESSION['user']['id'] ?? 0);
$nama = $_SESSION['user']['nama'] ?? 'Siswa';
$kelas = strtoupper(trim($_SESSION['user']['kelas'] ?? '-'));
$sekarang = time();

// Function tingkat kelas
function tingkatKelas($k) {
    $k = strtoupper(trim($k));
    if (str_starts_with($k, 'XII')) return 'XII';
    if (str_starts_with($k, 'XI')) return 'XI';
    if (str_starts_with($k, 'X')) return 'X';
    return '';
}
$tingkatUser = tingkatKelas($kelas);

/* ==========================================
   STATISTIK PEMINJAMAN SISWA
   ========================================== */
// Total Peminjaman
$stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ?");
$stmt->execute([$id]);
$total = (int) $stmt->fetchColumn();

// Sedang Dipinjam / Aktif
$stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ? AND status IN ('disetujui','dipinjam')");
$stmt->execute([$id]);
$aktif = (int) $stmt->fetchColumn();

// Dikembalikan
$stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ? AND status = 'dikembalikan'");
$stmt->execute([$id]);
$kembali = (int) $stmt->fetchColumn();

// Peminjaman Terlambat & Mendekati Batas
$stmt = $pdo->prepare("
    SELECT p.*, b.foto AS foto_buku, b.rak, b.nomor_rak
    FROM peminjaman p
    LEFT JOIN buku b ON b.id = p.buku_id
    WHERE p.user_id = ? AND p.status IN ('disetujui','dipinjam')
    ORDER BY p.tanggal_rencana_kembali ASC
");
$stmt->execute([$id]);
$pinjamanBerjalan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$terlambatCount = 0;
$mendekatiBatas = [];

foreach ($pinjamanBerjalan as &$loan) {
    $waktuKembali = !empty($loan['waktu_rencana_kembali']) ? $loan['waktu_rencana_kembali'] : '23:59:59';
    $batas = strtotime($loan['tanggal_rencana_kembali'] . ' ' . $waktuKembali);
    $loan['batas_timestamp'] = $batas;

    if ($batas < $sekarang) {
        $terlambatCount++;
        $loan['is_terlambat'] = true;
        $loan['selisih_hari'] = ceil(($sekarang - $batas) / 86400);
    } else {
        $loan['is_terlambat'] = false;
        $sisaHari = ceil(($batas - $sekarang) / 86400);
        $loan['sisa_hari'] = $sisaHari;
        if ($sisaHari <= 2) {
            $mendekatiBatas[] = $loan;
        }
    }
}
unset($loan);

/* ==========================================
   REKOMENDASI BUKU UNTUK KELAS SISWA
   ========================================== */
$stmtRekomendasi = $pdo->prepare("
    SELECT *
    FROM buku
    WHERE stok_tersedia > 0
      AND (kelas = ? OR kelas = ? OR kelas LIKE ?)
    ORDER BY id DESC
    LIMIT 4
");
$stmtRekomendasi->execute([
    $tingkatUser,
    $kelas,
    $tingkatUser . ' %'
]);
$rekomendasiBuku = $stmtRekomendasi->fetchAll(PDO::FETCH_ASSOC);

function cariCoverBuku($foto) {
    if (empty($foto)) return '';
    $nama = basename(trim($foto));
    if ($nama === '') return '';
    $paths = [
        __DIR__ . '/../uploads/books/' . $nama => '../uploads/books/' . rawurlencode($nama),
        __DIR__ . '/../uploads/buku/' . $nama  => '../uploads/buku/' . rawurlencode($nama),
    ];
    foreach ($paths as $file => $url) {
        if (is_file($file)) return $url . '?v=' . filemtime($file);
    }
    return '';
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <!-- HERO WELCOME BANNER -->
    <div class="hero-banner">
        <div class="hero-content">
            <span class="hero-pill">
                ⭐ Portal Siswa &bull; Kelas <?= e($kelas) ?>
            </span>
            <h1>Halo, <?= e($nama) ?>! 👋</h1>
            <p>
                Selamat datang di Perpustakaan SMK Digital. Akses ribuan judul buku, pantau batas waktu pengembalian, dan pinjam buku favoritmu dengan mudah.
            </p>
            <div class="hero-actions">
                <a href="pinjam.php" class="btn btn-white">
                    ➕ Pinjam Buku Sekarang
                </a>
                <a href="buku.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                    🔍 Jelajahi Katalog
                </a>
            </div>
        </div>
    </div>

    <!-- PERINGATAN KETERLAMBATAN JIKA ADA -->
    <?php if ($terlambatCount > 0): ?>
        <div class="alert alert-danger" style="margin-bottom: 24px;">
            <span class="alert-icon">!</span>
            <div>
                <strong style="font-size: 14px;">Peringatan Pengembalian Terlambat</strong>
                <p>
                    Kamu memiliki <strong><?= $terlambatCount ?> buku</strong> yang telah melewati batas waktu pengembalian. Segera kembalikan buku ke petugas perpustakaan untuk menghindari sanksi administrasi.
                </p>
            </div>
        </div>
    <?php elseif (!empty($mendekatiBatas)): ?>
        <div class="alert alert-warning" style="margin-bottom: 24px;">
            <span class="alert-icon">⏰</span>
            <div>
                <strong>Pengingat Jatuh Tempo</strong>
                <p>
                    Buku <em>"<?= e($mendekatiBatas[0]['nama_buku']) ?>"</em> harus dikembalikan dalam waktu <strong><?= $mendekatiBatas[0]['sisa_hari'] == 0 ? 'hari ini' : $mendekatiBatas[0]['sisa_hari'] . ' hari lagi' ?></strong>.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="stat-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Sedang Dipinjam</span>
                <strong class="stat-value"><?= $aktif ?></strong>
                <span class="stat-desc">Buku masih di tangan</span>
            </div>
        </div>

        <div class="stat-card emerald">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Sudah Dikembalikan</span>
                <strong class="stat-value"><?= $kembali ?></strong>
                <span class="stat-desc">Peminjaman selesai</span>
            </div>
        </div>

        <div class="stat-card amber">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Transaksi</span>
                <strong class="stat-value"><?= $total ?></strong>
                <span class="stat-desc">Riwayat peminjaman</span>
            </div>
        </div>

        <div class="stat-card <?= $terlambatCount > 0 ? 'rose' : 'indigo' ?>">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Status Keterlambatan</span>
                <strong class="stat-value"><?= $terlambatCount ?></strong>
                <span class="stat-desc"><?= $terlambatCount > 0 ? 'Buku lewat tempo' : 'Tertib tepat waktu' ?></span>
            </div>
        </div>
    </div>

    <!-- BUKU SEDANG AKTIF DIPINJAM -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                        <path d="M12 9v6m-3-3h6"/>
                    </svg>
                    Buku yang Sedang Kamu Pinjam
                </h3>
                <p class="card-subtitle">Daftar buku yang aktif dipinjam dan harus dikembalikan tepat waktu.</p>
            </div>
            <a href="riwayat.php" class="btn btn-secondary btn-sm">
                Lihat Semua Riwayat &rarr;
            </a>
        </div>

        <?php if (!empty($pinjamanBerjalan)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Buku Dipinjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Batas Pengembalian</th>
                            <th>Sisa Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pinjamanBerjalan as $p): ?>
                            <?php $cover = cariCoverBuku($p['foto_buku'] ?? ''); ?>
                            <tr class="<?= !empty($p['is_terlambat']) ? 'baris-terlambat' : '' ?>">
                                <td>
                                    <div class="book-cell">
                                        <?php if ($cover !== ''): ?>
                                            <img src="<?= e($cover) ?>" class="book-thumb" alt="">
                                        <?php else: ?>
                                            <div style="width: 38px; height: 50px; border-radius: 4px; background: var(--primary-700); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; text-align: center; flex-shrink: 0;">
                                                Buku
                                            </div>
                                        <?php endif; ?>
                                        <div class="book-cell-info">
                                            <strong><?= e($p['nama_buku']) ?></strong>
                                            <small><?= (int)$p['jumlah'] ?> eksemplar &bull; Rak: <?= e($p['rak'] ?: 'Utama') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-400);"><?= e(substr($p['waktu_mulai'] ?? '00:00', 0, 5)) ?> WIB</small>
                                </td>
                                <td>
                                    <strong><?= date('d M Y', strtotime($p['tanggal_rencana_kembali'])) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-400);"><?= e(substr($p['waktu_rencana_kembali'] ?? '15:00', 0, 5)) ?> WIB</small>
                                </td>
                                <td>
                                    <?php if (!empty($p['is_terlambat'])): ?>
                                        <span style="color: var(--rose-600); font-weight: 800;">
                                            ⚠️ Lewat <?= $p['selisih_hari'] ?> hari
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--emerald-600); font-weight: 700;">
                                            <?= $p['sisa_hari'] == 0 ? 'Hari ini' : $p['sisa_hari'] . ' hari lagi' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['is_terlambat'])): ?>
                                        <span class="badge badge-terlambat">Terlambat</span>
                                    <?php else: ?>
                                        <span class="badge badge-<?= e($p['status']) ?>"><?= ucfirst(e($p['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 36px 20px; color: var(--slate-500);">
                <div style="font-size: 32px; margin-bottom: 8px;">📚</div>
                <strong style="display: block; font-size: 15px; color: var(--slate-800); margin-bottom: 4px;">Tidak ada buku yang sedang dipinjam</strong>
                <p style="font-size: 13px; max-width: 400px; margin: 0 auto 16px;">Semua buku pinjaman sudah dikembalikan atau kamu belum meminjam buku.</p>
                <a href="pinjam.php" class="btn btn-primary btn-sm">Mulai Pinjam Buku</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- REKOMENDASI BUKU UNTUK KELAS SISWA -->
    <?php if (!empty($rekomendasiBuku)): ?>
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber-500)" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        Rekomendasi Buku untuk Kelas <?= e($kelas) ?>
                    </h3>
                    <p class="card-subtitle">Koleksi buku kurikulum dan referensi yang relevan dengan tingkat kelasmu.</p>
                </div>
                <a href="buku.php?kelas=sesuai" class="btn btn-secondary btn-sm">
                    Lihat Semua Rekomendasi &rarr;
                </a>
            </div>

            <div class="catalog-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 0;">
                <?php foreach ($rekomendasiBuku as $rek): ?>
                    <?php $cover = cariCoverBuku($rek['foto'] ?? ''); ?>
                    <div class="book-card">
                        <div class="book-card-media" style="height: 180px;">
                            <?php if ($cover !== ''): ?>
                                <img src="<?= e($cover) ?>" class="book-card-cover" style="width: 105px; height: 155px;" alt="">
                            <?php else: ?>
                                <div class="book-card-fallback" style="width: 105px; height: 155px;">
                                    <div class="fallback-title" style="font-size: 11px;"><?= e($rek['nama_buku']) ?></div>
                                    <div class="fallback-meta">SMK Lib</div>
                                </div>
                            <?php endif; ?>
                            <span class="book-card-badge">Kelas <?= e($rek['kelas']) ?></span>
                        </div>

                        <div class="book-card-body">
                            <h4 class="book-title" style="font-size: 14px;" title="<?= e($rek['nama_buku']) ?>">
                                <?= e($rek['nama_buku']) ?>
                            </h4>
                            <small style="color: var(--slate-500); margin-bottom: 12px; display: block;">
                                <?= e($rek['pengarang'] ?: 'Perpustakaan SMK') ?>
                            </small>
                            <a href="pinjam.php?buku_id=<?= (int)$rek['id'] ?>" class="btn btn-primary btn-sm" style="width: 100%; margin-top: auto;">
                                Pinjam Buku
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>