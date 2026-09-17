<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

require_role('siswa');

date_default_timezone_set('Asia/Jakarta');

$page_title = 'Riwayat Peminjaman';
$base = '../';

$userId = (int)($_SESSION['user']['id'] ?? 0);
$sekarang = time();

// Filter status & pencarian
$statusFilter = trim($_GET['status'] ?? 'semua');
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT p.*, b.foto AS foto_buku, b.rak, b.nomor_rak
    FROM peminjaman p
    LEFT JOIN buku b ON b.id = p.buku_id
    WHERE p.user_id = ?
";
$params = [$userId];

if ($search !== '') {
    $sql .= " AND p.nama_buku LIKE ?";
    $params[] = '%' . $search . '%';
}

if ($statusFilter === 'aktif') {
    $sql .= " AND p.status IN ('disetujui', 'dipinjam')";
} elseif ($statusFilter === 'menunggu') {
    $sql .= " AND p.status = 'menunggu'";
} elseif ($statusFilter === 'dikembalikan') {
    $sql .= " AND p.status = 'dikembalikan'";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process overdue calculation
$terlambatCount = 0;
$filteredRows = [];

foreach ($rows as $r) {
    $isOverdue = false;
    $daysDiff = 0;

    if (in_array(strtolower(trim($r['status'])), ['disetujui', 'dipinjam'], true)) {
        $waktuBatas = !empty($r['waktu_rencana_kembali']) ? $r['waktu_rencana_kembali'] : '23:59:59';
        $batasKembali = strtotime($r['tanggal_rencana_kembali'] . ' ' . $waktuBatas);

        if ($batasKembali !== false && $batasKembali < $sekarang) {
            $isOverdue = true;
            $terlambatCount++;
            $daysDiff = ceil(($sekarang - $batasKembali) / 86400);
        } else {
            $daysDiff = ceil(($batasKembali - $sekarang) / 86400);
        }
    }

    $r['is_overdue'] = $isOverdue;
    $r['days_diff'] = $daysDiff;

    if ($statusFilter === 'terlambat') {
        if ($isOverdue) $filteredRows[] = $r;
    } else {
        $filteredRows[] = $r;
    }
}

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

$success = $_GET['success'] ?? '';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <!-- ALERT PESAN SUKSES -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <div>
                <strong>Berhasil!</strong>
                <p><?= e($success) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Riwayat Transaksi Peminjaman
                </h3>
                <p class="card-subtitle">Pantau seluruh aktivitas peminjaman buku kamu di perpustakaan SMK.</p>
            </div>
            <a href="pinjam.php" class="btn btn-primary btn-sm">
                ➕ Pinjam Buku Baru
            </a>
        </div>

        <!-- FILTER TABS & SEARCH BAR -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="riwayat.php?status=semua<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'semua' ? 'active' : '' ?>">
                    Semua
                </a>
                <a href="riwayat.php?status=aktif<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'aktif' ? 'active' : '' ?>">
                    Sedang Dipinjam
                </a>
                <a href="riwayat.php?status=terlambat<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'terlambat' ? 'active' : '' ?>">
                    Terlambat <?= $terlambatCount > 0 ? "($terlambatCount)" : '' ?>
                </a>
                <a href="riwayat.php?status=dikembalikan<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'dikembalikan' ? 'active' : '' ?>">
                    Selesai
                </a>
            </div>

            <!-- SEARCH -->
            <form method="GET" action="riwayat.php" class="search-box">
                <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari judul buku..." class="form-control">
            </form>
        </div>

        <!-- TABEL RIWAYAT -->
        <?php if (!empty($filteredRows)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Buku</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Status & Sisa Waktu</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredRows as $index => $r): ?>
                            <?php $cover = cariCoverBuku($r['foto_buku'] ?? ''); ?>
                            <tr class="<?= !empty($r['is_overdue']) ? 'baris-terlambat' : '' ?>">
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="book-cell">
                                        <?php if ($cover !== ''): ?>
                                            <img src="<?= e($cover) ?>" class="book-thumb" alt="">
                                        <?php else: ?>
                                            <div style="width: 36px; height: 48px; border-radius: 4px; background: var(--primary-700); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800; text-align: center; flex-shrink: 0;">
                                                Buku
                                            </div>
                                        <?php endif; ?>
                                        <div class="book-cell-info">
                                            <strong><?= e($r['nama_buku']) ?></strong>
                                            <small>ID Pinjam #<?= $r['id'] ?> &bull; Rak: <?= e($r['rak'] ?: 'Koleksi') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?= (int)$r['jumlah'] ?></strong></td>
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
                                    <?php if (!empty($r['is_overdue'])): ?>
                                        <span class="badge badge-terlambat">Terlambat</span>
                                        <br>
                                        <small style="color: var(--rose-600); font-weight: 700;">Lewat <?= $r['days_diff'] ?> hari</small>
                                    <?php elseif ($r['status'] === 'dikembalikan'): ?>
                                        <span class="badge badge-dikembalikan">Dikembalikan</span>
                                    <?php elseif ($r['status'] === 'menunggu'): ?>
                                        <span class="badge badge-menunggu">Menunggu</span>
                                    <?php else: ?>
                                        <span class="badge badge-dipinjam"><?= ucfirst(e($r['status'])) ?></span>
                                        <br>
                                        <small style="color: var(--emerald-600); font-weight: 600;">
                                            <?= $r['days_diff'] == 0 ? 'Batas hari ini' : 'Sisa ' . $r['days_diff'] . ' hari' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small style="color: var(--slate-600);">
                                        <?= e($r['catatan'] ?: '-') ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: var(--slate-500);">
                <div style="font-size: 32px; margin-bottom: 8px;">📖</div>
                <strong style="display: block; font-size: 15px; color: var(--slate-800); margin-bottom: 4px;">Tidak ada riwayat peminjaman ditemukan</strong>
                <p style="font-size: 13px; max-width: 400px; margin: 0 auto 16px;">Belum ada data peminjaman yang cocok dengan filter yang dipilih.</p>
                <a href="buku.php" class="btn btn-primary btn-sm">Lihat Katalog Buku</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>