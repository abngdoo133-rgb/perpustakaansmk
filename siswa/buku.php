<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

require_role('siswa');

date_default_timezone_set('Asia/Jakarta');

$page_title = 'Katalog Buku';
$base = '../';

$userId = (int)($_SESSION['user']['id'] ?? 0);
$kelasSiswa = strtoupper(trim($_SESSION['user']['kelas'] ?? ''));

// Function tingkat kelas
function tingkatKelas($k) {
    $k = strtoupper(trim($k));
    if (str_starts_with($k, 'XII')) return 'XII';
    if (str_starts_with($k, 'XI')) return 'XI';
    if (str_starts_with($k, 'X')) return 'X';
    return '';
}

$tingkatUser = tingkatKelas($kelasSiswa);

// Function foto cover
function cariCoverBuku($foto) {
    if (empty($foto)) return '';
    $nama = basename(trim($foto));
    if ($nama === '') return '';

    $paths = [
        __DIR__ . '/../uploads/books/' . $nama => '../uploads/books/' . rawurlencode($nama),
        __DIR__ . '/../uploads/buku/' . $nama  => '../uploads/buku/' . rawurlencode($nama),
    ];

    foreach ($paths as $file => $url) {
        if (is_file($file)) {
            return $url . '?v=' . filemtime($file);
        }
    }
    return '';
}

// Search & Filter
$keyword = trim($_GET['q'] ?? '');
$filterKelas = trim($_GET['kelas'] ?? '');
$filterHanyaTersedia = isset($_GET['tersedia']) && $_GET['tersedia'] === '1';

$sql = "SELECT * FROM buku WHERE 1=1";
$params = [];

if ($keyword !== '') {
    $sql .= " AND (nama_buku LIKE ? OR pengarang LIKE ? OR penerbit LIKE ? OR mata_pelajaran LIKE ?)";
    $kw = '%' . $keyword . '%';
    $params = array_merge($params, [$kw, $kw, $kw, $kw]);
}

if ($filterKelas === 'sesuai') {
    if ($tingkatUser !== '') {
        $sql .= " AND (kelas = ? OR kelas = ? OR kelas LIKE ?)";
        $params = array_merge($params, [$tingkatUser, $kelasSiswa, $tingkatUser . ' %']);
    }
} elseif (in_array($filterKelas, ['X', 'XI', 'XII'])) {
    $sql .= " AND (kelas = ? OR kelas LIKE ?)";
    $params = array_merge($params, [$filterKelas, $filterKelas . ' %']);
}

if ($filterHanyaTersedia) {
    $sql .= " AND stok_tersedia > 0";
}

$sql .= " ORDER BY nama_buku ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bukuList = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <!-- HERO SEARCH CATALOG -->
    <div class="card" style="padding: 24px; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; margin-bottom: 24px;">
        <div style="max-width: 600px;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #bfdbfe;">
                Koleksi Perpustakaan SMK
            </span>
            <h1 style="font-size: 24px; font-weight: 800; margin: 4px 0 8px;">
                Eksplorasi Katalog Buku
            </h1>
            <p style="font-size: 13.5px; color: #e0e7ff; line-height: 1.5; margin-bottom: 18px;">
                Temukan buku paket mata pelajaran, modul kejuruan, dan buku referensi untuk mendukung kegiatan belajarmu.
            </p>
        </div>

        <!-- SEARCH BOX -->
        <form method="GET" action="buku.php" style="display: flex; gap: 10px; max-width: 640px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 260px;">
                <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Cari judul buku, mata pelajaran, atau pengarang..." class="form-control" style="background: white; border: none; padding-left: 40px; height: 44px; font-size: 14px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                <svg style="position: absolute; left: 14px; top: 13px; width: 18px; height: 18px; color: var(--slate-400);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <?php if ($filterKelas !== ''): ?>
                <input type="hidden" name="kelas" value="<?= e($filterKelas) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-white" style="height: 44px; padding: 0 22px; font-weight: 700;">
                Cari Buku
            </button>
            <?php if ($keyword !== '' || $filterKelas !== '' || $filterHanyaTersedia): ?>
                <a href="buku.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); height: 44px;">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <div class="filter-tabs">
            <a href="buku.php<?= $keyword ? '?q='.urlencode($keyword) : '' ?>" class="filter-tab <?= $filterKelas === '' ? 'active' : '' ?>">
                Semua Koleksi
            </a>
            <?php if ($tingkatUser !== ''): ?>
                <a href="buku.php?kelas=sesuai<?= $keyword ? '&q='.urlencode($keyword) : '' ?>" class="filter-tab <?= $filterKelas === 'sesuai' ? 'active' : '' ?>">
                    ⭐ Sesuai Kelasku (<?= e($kelasSiswa) ?>)
                </a>
            <?php endif; ?>
            <a href="buku.php?kelas=X<?= $keyword ? '&q='.urlencode($keyword) : '' ?>" class="filter-tab <?= $filterKelas === 'X' ? 'active' : '' ?>">
                Kelas X
            </a>
            <a href="buku.php?kelas=XI<?= $keyword ? '&q='.urlencode($keyword) : '' ?>" class="filter-tab <?= $filterKelas === 'XI' ? 'active' : '' ?>">
                Kelas XI
            </a>
            <a href="buku.php?kelas=XII<?= $keyword ? '&q='.urlencode($keyword) : '' ?>" class="filter-tab <?= $filterKelas === 'XII' ? 'active' : '' ?>">
                Kelas XII
            </a>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 13px; font-weight: 600; color: var(--slate-500);">
                Ditemukan <strong><?= count($bukuList) ?></strong> buku
            </span>
        </div>
    </div>

    <!-- BOOK CATALOG GRID -->
    <?php if (!empty($bukuList)): ?>
        <div class="catalog-grid">
            <?php foreach ($bukuList as $item): ?>
                <?php
                $cover = cariCoverBuku($item['foto'] ?? '');
                $stokTersedia = (int)($item['stok_tersedia'] ?? 0);
                $isMatch = ($tingkatUser !== '' && (
                    $item['kelas'] === $tingkatUser ||
                    $item['kelas'] === $kelasSiswa ||
                    str_starts_with($item['kelas'], $tingkatUser . ' ')
                ));
                ?>
                <div class="book-card">
                    <!-- MEDIA COVER -->
                    <div class="book-card-media">
                        <?php if ($cover !== ''): ?>
                            <img src="<?= e($cover) ?>" alt="<?= e($item['nama_buku']) ?>" class="book-card-cover" loading="lazy">
                        <?php else: ?>
                            <div class="book-card-fallback">
                                <div class="fallback-title"><?= e($item['nama_buku']) ?></div>
                                <div class="fallback-meta"><?= e($item['penerbit'] ?: 'SMK Library') ?></div>
                            </div>
                        <?php endif; ?>

                        <span class="book-card-badge">
                            Kelas <?= e($item['kelas']) ?>
                        </span>
                    </div>

                    <!-- BODY -->
                    <div class="book-card-body">
                        <?php if ($isMatch): ?>
                            <span class="book-class-tag match">
                                ✓ Cocok untuk kelasku
                            </span>
                        <?php else: ?>
                            <span class="book-class-tag">
                                <?= e($item['kategori'] ?? 'Umum') ?>
                            </span>
                        <?php endif; ?>

                        <h3 class="book-title" title="<?= e($item['nama_buku']) ?>">
                            <?= e($item['nama_buku']) ?>
                        </h3>

                        <div class="book-meta">
                            <?php if (!empty($item['pengarang'])): ?>
                                <div class="book-meta-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span><?= e($item['pengarang']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="book-meta-row">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                                </svg>
                                <span>Rak: <?= e($item['rak'] ?: '-') ?> <?= e($item['nomor_rak'] ? '(' . $item['nomor_rak'] . ')' : '') ?></span>
                            </div>
                        </div>

                        <!-- STOCK & ACTION -->
                        <div class="book-stock-bar">
                            <div class="stock-indicator">
                                <?php if ($stokTersedia > 0): ?>
                                    <strong style="color: var(--emerald-600);"><?= $stokTersedia ?> Tersedia</strong>
                                    <small>dari <?= (int)$item['stok_total'] ?> buku</small>
                                <?php else: ?>
                                    <strong style="color: var(--rose-600);">Stok Habis</strong>
                                    <small>sedang dipinjam</small>
                                <?php endif; ?>
                            </div>

                            <?php if ($stokTersedia > 0): ?>
                                <a href="pinjam.php?buku_id=<?= (int)$item['id'] ?>" class="btn btn-primary btn-sm">
                                    Pinjam Buku &rarr;
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-light btn-sm" disabled style="opacity: 0.6; cursor: not-allowed;">
                                    Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 48px 24px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--slate-100); color: var(--slate-400); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-bottom: 4px;">
                Buku Tidak Ditemukan
            </h3>
            <p style="font-size: 13px; color: var(--slate-500); margin-bottom: 16px;">
                Coba gunakan kata kunci lain atau reset filter kelas Anda.
            </p>
            <a href="buku.php" class="btn btn-primary">
                Tampilkan Semua Buku
            </a>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>