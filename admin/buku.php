<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Data Buku';
$base = '../';

$cari = trim($_GET['cari'] ?? '');
$filterKelas = trim($_GET['kelas'] ?? '');

$sql = "SELECT * FROM buku WHERE 1=1";
$params = [];

if ($cari !== '') {
    $sql .= " AND (nama_buku LIKE ? OR mata_pelajaran LIKE ? OR penerbit LIKE ? OR pengarang LIKE ? OR rak LIKE ?)";
    $kw = '%' . $cari . '%';
    $params = array_merge($params, [$kw, $kw, $kw, $kw, $kw]);
}

if ($filterKelas !== '') {
    $sql .= " AND (kelas = ? OR kelas LIKE ?)";
    $params = array_merge($params, [$filterKelas, $filterKelas . ' %']);
}

$sql .= " ORDER BY nama_buku ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$buku = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ringkasan
$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total_judul,
        COALESCE(SUM(stok_total), 0) AS total_stok,
        COALESCE(SUM(stok_tersedia), 0) AS stok_tersedia
    FROM buku
");
$ringkasan = $stmt->fetch(PDO::FETCH_ASSOC);

function cariFotoBuku($foto) {
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

    <?php if (!empty($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'hapus_sukses'): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <div><strong>Berhasil</strong><p>Buku berhasil dihapus dari katalog perpustakaan.</p></div>
            </div>
        <?php elseif ($_GET['status'] === 'edit_sukses'): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <div><strong>Berhasil</strong><p>Data buku berhasil diperbarui.</p></div>
            </div>
        <?php elseif ($_GET['status'] === 'tambah_sukses'): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <div><strong>Berhasil</strong><p>Buku baru berhasil ditambahkan ke katalog perpustakaan.</p></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div><strong>Peringatan</strong><p><?= e($_GET['error']) ?></p></div>
        </div>
    <?php endif; ?>

    <!-- STAT SUMMARY CARDS -->
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Judul</span>
                <strong class="stat-value"><?= number_format($ringkasan['total_judul'] ?? 0) ?></strong>
                <span class="stat-desc">Koleksi terdaftar</span>
            </div>
        </div>

        <div class="stat-card emerald">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Stok Tersedia</span>
                <strong class="stat-value"><?= number_format($ringkasan['stok_tersedia'] ?? 0) ?></strong>
                <span class="stat-desc">Siap untuk dipinjam</span>
            </div>
        </div>

        <div class="stat-card amber">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Eksemplar</span>
                <strong class="stat-value"><?= number_format($ringkasan['total_stok'] ?? 0) ?></strong>
                <span class="stat-desc">Seluruh stok fisik</span>
            </div>
        </div>
    </div>

    <!-- MAIN CARD & TABLE -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                    </svg>
                    Katalog Data Buku Perpustakaan
                </h3>
                <p class="card-subtitle">Kelola judul buku, penempatan rak, jumlah stok, dan foto cover.</p>
            </div>
            <a href="tambah_buku.php" class="btn btn-primary">
                ➕ Tambah Buku Baru
            </a>
        </div>

        <!-- FILTER & SEARCH BAR -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="buku.php<?= $cari ? '?cari='.urlencode($cari) : '' ?>" class="filter-tab <?= $filterKelas === '' ? 'active' : '' ?>">Semua Kelas</a>
                <a href="buku.php?kelas=X<?= $cari ? '&cari='.urlencode($cari) : '' ?>" class="filter-tab <?= $filterKelas === 'X' ? 'active' : '' ?>">Kelas X</a>
                <a href="buku.php?kelas=XI<?= $cari ? '&cari='.urlencode($cari) : '' ?>" class="filter-tab <?= $filterKelas === 'XI' ? 'active' : '' ?>">Kelas XI</a>
                <a href="buku.php?kelas=XII<?= $cari ? '&cari='.urlencode($cari) : '' ?>" class="filter-tab <?= $filterKelas === 'XII' ? 'active' : '' ?>">Kelas XII</a>
            </div>

            <form method="GET" action="buku.php" class="search-box">
                <?php if ($filterKelas !== ''): ?>
                    <input type="hidden" name="kelas" value="<?= e($filterKelas) ?>">
                <?php endif; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari judul, pengarang, rak..." class="form-control">
            </form>
        </div>

        <!-- TABEL BUKU -->
        <?php if (!empty($buku)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Cover & Judul Buku</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Pengarang / Penerbit</th>
                            <th>Lokasi Rak</th>
                            <th>Stok (Tersedia / Total)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buku as $index => $row): ?>
                            <?php
                            $cover = cariFotoBuku($row['foto'] ?? '');
                            $stokTot = (int)($row['stok_total'] ?? 0);
                            $stokTers = (int)($row['stok_tersedia'] ?? 0);
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
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
                                            <strong><?= e($row['nama_buku']) ?></strong>
                                            <small>Tahun: <?= e($row['tahun_perolehan'] ?: '-') ?> &bull; ID #<?= $row['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: var(--slate-100); color: var(--slate-800);">
                                        Kelas <?= e($row['kelas']) ?>
                                    </span>
                                </td>
                                <td><?= e($row['mata_pelajaran'] ?: '-') ?></td>
                                <td>
                                    <small style="color: var(--slate-700); display: block;">
                                        <strong><?= e($row['pengarang'] ?: '-') ?></strong>
                                    </small>
                                    <small style="color: var(--slate-400);">
                                        <?= e($row['penerbit'] ?: '-') ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= e($row['rak'] ?: '-') ?></strong>
                                    <?php if (!empty($row['nomor_rak'])): ?>
                                        <small style="color: var(--slate-500);">(<?= e($row['nomor_rak']) ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($stokTers > 0): ?>
                                        <span style="color: var(--emerald-600); font-weight: 700;"><?= $stokTers ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--rose-600); font-weight: 700;">0 (Habis)</span>
                                    <?php endif; ?>
                                    <small style="color: var(--slate-400);">/ <?= $stokTot ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_buku.php?id=<?= (int)$row['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Buku">
                                            ✏️ Edit
                                        </a>
                                        <a href="../proses/hapus_buku.php?id=<?= (int)$row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus buku <?= e($row['nama_buku']) ?> dari katalog?')" title="Hapus Buku">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: var(--slate-500);">
                Tidak ada data buku yang sesuai dengan pencarian atau filter kelas.
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>