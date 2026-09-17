<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

date_default_timezone_set('Asia/Jakarta');

$page_title = 'Kelola Peminjaman';
$base = '../';

$sekarang = time();
$statusFilter = trim($_GET['status'] ?? 'semua');
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT p.*, u.foto, u.username
    FROM peminjaman p
    JOIN users u ON u.id = p.user_id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.nama_siswa LIKE ? OR p.nama_buku LIKE ? OR p.kelas LIKE ? OR u.username LIKE ?)";
    $kw = '%' . $search . '%';
    $params = array_merge($params, [$kw, $kw, $kw, $kw]);
}

if ($statusFilter === 'menunggu') {
    $sql .= " AND p.status = 'menunggu'";
} elseif ($statusFilter === 'dipinjam') {
    $sql .= " AND p.status IN ('disetujui', 'dipinjam')";
} elseif ($statusFilter === 'dikembalikan') {
    $sql .= " AND p.status = 'dikembalikan'";
} elseif ($statusFilter === 'ditolak') {
    $sql .= " AND p.status = 'ditolak'";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count overdue
$terlambatCount = 0;
$filteredRows = [];

foreach ($rows as $r) {
    $isOverdue = false;
    if (in_array($r['status'], ['disetujui', 'dipinjam'], true)) {
        $waktuBatas = !empty($r['waktu_rencana_kembali']) ? $r['waktu_rencana_kembali'] : '23:59:59';
        $batasKembali = strtotime($r['tanggal_rencana_kembali'] . ' ' . $waktuBatas);
        if ($batasKembali !== false && $batasKembali < $sekarang) {
            $isOverdue = true;
            $terlambatCount++;
        }
    }
    $r['is_overdue'] = $isOverdue;

    if ($statusFilter === 'terlambat') {
        if ($isOverdue) $filteredRows[] = $r;
    } else {
        $filteredRows[] = $r;
    }
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <div>
                <strong>Berhasil</strong>
                <p><?= e($success) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div>
                <strong>Peringatan</strong>
                <p><?= e($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- PERINGATAN TERLAMBAT JIKA ADA -->
    <?php if ($terlambatCount > 0): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <span class="alert-icon">!</span>
            <div style="flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <div>
                    <strong>Peringatan Peminjaman Terlambat</strong>
                    <p>Ada <strong><?= $terlambatCount ?> peminjaman</strong> yang telah melewati batas waktu pengembalian.</p>
                </div>
                <a href="terlambat.php" class="btn btn-danger btn-sm">
                    Lihat Daftar Terlambat &rarr;
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                    </svg>
                    Daftar Sirkulasi Peminjaman Buku
                </h3>
                <p class="card-subtitle">Kelola dan perbarui status peminjaman buku oleh siswa.</p>
            </div>
        </div>

        <!-- FILTER TABS & SEARCH -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="peminjaman.php?status=semua<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'semua' ? 'active' : '' ?>">
                    Semua
                </a>
                <a href="peminjaman.php?status=menunggu<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'menunggu' ? 'active' : '' ?>">
                    Menunggu
                </a>
                <a href="peminjaman.php?status=dipinjam<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'dipinjam' ? 'active' : '' ?>">
                    Sedang Dipinjam
                </a>
                <a href="peminjaman.php?status=terlambat<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'terlambat' ? 'active' : '' ?>">
                    Terlambat <?= $terlambatCount > 0 ? "($terlambatCount)" : '' ?>
                </a>
                <a href="peminjaman.php?status=dikembalikan<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'dikembalikan' ? 'active' : '' ?>">
                    Dikembalikan
                </a>
                <a href="peminjaman.php?status=ditolak<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $statusFilter === 'ditolak' ? 'active' : '' ?>">
                    Ditolak
                </a>
            </div>

            <form method="GET" action="peminjaman.php" class="search-box">
                <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari siswa, kelas, judul buku..." class="form-control">
            </form>
        </div>

        <!-- TABEL DATA PEMINJAMAN -->
        <?php if (!empty($filteredRows)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Buku Dipinjam</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Status</th>
                            <th style="min-width: 220px;">Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredRows as $r): ?>
                            <tr class="<?= !empty($r['is_overdue']) ? 'baris-terlambat' : '' ?>">
                                <td>
                                    <div class="user-cell">
                                        <img src="<?= !empty($r['foto']) ? '../uploads/profile/' . e($r['foto']) : '../assets/images/avatar.svg' ?>" class="user-cell-avatar" alt="">
                                        <div class="user-cell-meta">
                                            <strong><?= e($r['nama_siswa']) ?></strong>
                                            <small><?= e($r['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: var(--slate-100); color: var(--slate-700);">
                                        <?= e($r['kelas']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= e($r['nama_buku']) ?></strong>
                                    <?php if (!empty($r['catatan'])): ?>
                                        <br>
                                        <small style="color: var(--slate-500); font-style: italic;">"<?= e($r['catatan']) ?>"</small>
                                    <?php endif; ?>
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
                                    <?php else: ?>
                                        <span class="badge badge-<?= e($r['status']) ?>">
                                            <?= ucfirst(e($r['status'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="../proses/status_peminjaman.php" style="display: flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <select name="status" class="form-control select" style="width: 140px; padding: 6px 10px; font-size: 12px; height: 34px;">
                                            <option value="menunggu" <?= $r['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                            <option value="disetujui" <?= $r['status'] === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                            <option value="dipinjam" <?= $r['status'] === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                            <option value="dikembalikan" <?= $r['status'] === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                                            <option value="ditolak" <?= $r['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm" style="height: 34px; padding: 0 12px;">
                                            Simpan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: var(--slate-500);">
                Tidak ada data peminjaman yang sesuai dengan pencarian atau filter status.
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>