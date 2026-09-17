<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Data Siswa';
$base = '../';

$search = trim($_GET['q'] ?? '');
$filterKelas = trim($_GET['kelas'] ?? '');

$sql = "SELECT id, nama, username, email, kelas, foto, status, created_at FROM users WHERE role = 'siswa'";
$params = [];

if ($search !== '') {
    $sql .= " AND (nama LIKE ? OR username LIKE ? OR email LIKE ? OR kelas LIKE ?)";
    $kw = '%' . $search . '%';
    $params = array_merge($params, [$kw, $kw, $kw, $kw]);
}

if ($filterKelas !== '') {
    $sql .= " AND (kelas = ? OR kelas LIKE ?)";
    $params = array_merge($params, [$filterKelas, $filterKelas . ' %']);
}

$sql .= " ORDER BY nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <div><strong>Berhasil</strong><p><?= e($success) ?></p></div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div><strong>Gagal</strong><p><?= e($error) ?></p></div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Daftar Siswa Perpustakaan
                </h3>
                <p class="card-subtitle">Data akun siswa yang terdaftar untuk sirkulasi perpustakaan.</p>
            </div>
            <span class="badge" style="background: var(--primary-50); color: var(--primary-700); font-size: 13px;">
                Total: <?= count($siswa) ?> Siswa
            </span>
        </div>

        <!-- FILTER & SEARCH -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="siswa.php<?= $search ? '?q='.urlencode($search) : '' ?>" class="filter-tab <?= $filterKelas === '' ? 'active' : '' ?>">Semua Kelas</a>
                <a href="siswa.php?kelas=X<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $filterKelas === 'X' ? 'active' : '' ?>">Kelas X</a>
                <a href="siswa.php?kelas=XI<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $filterKelas === 'XI' ? 'active' : '' ?>">Kelas XI</a>
                <a href="siswa.php?kelas=XII<?= $search ? '&q='.urlencode($search) : '' ?>" class="filter-tab <?= $filterKelas === 'XII' ? 'active' : '' ?>">Kelas XII</a>
            </div>

            <form method="GET" action="siswa.php" class="search-box">
                <?php if ($filterKelas !== ''): ?>
                    <input type="hidden" name="kelas" value="<?= e($filterKelas) ?>">
                <?php endif; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, NIS, kelas..." class="form-control">
            </form>
        </div>

        <!-- TABEL SISWA -->
        <?php if (!empty($siswa)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Profil & Nama Siswa</th>
                            <th>NIS / Username</th>
                            <th>Email</th>
                            <th>Kelas</th>
                            <th>Status Akun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="user-cell">
                                        <img src="<?= !empty($row['foto']) ? '../uploads/profile/' . e($row['foto']) : '../assets/images/avatar.svg' ?>" class="user-cell-avatar" alt="">
                                        <div class="user-cell-meta">
                                            <strong><?= e($row['nama']) ?></strong>
                                            <small>Bergabung: <?= date('d M Y', strtotime($row['created_at'] ?? 'now')) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($row['username']) ?></td>
                                <td><?= e($row['email'] ?: '-') ?></td>
                                <td>
                                    <span class="badge" style="background: var(--slate-100); color: var(--slate-700);">
                                        <?= e($row['kelas'] ?: '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'aktif'): ?>
                                        <span class="badge badge-dikembalikan">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-ditolak">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="profil_siswa.php?id=<?= (int)$row['id'] ?>" class="btn btn-secondary btn-sm">
                                            👁️ Detail
                                        </a>
                                        <a href="../proses/hapus_siswa.php?id=<?= (int)$row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus siswa <?= e($row['nama']) ?>?')" title="Hapus Siswa">
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
                Tidak ada data siswa yang sesuai dengan filter atau pencarian.
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>