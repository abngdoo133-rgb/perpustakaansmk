<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Profil Siswa';
$base = '../';

$id = (int)($_GET['id'] ?? 0);
$modeEdit = isset($_GET['edit']) && $_GET['edit'] === '1';

if ($id <= 0) {
    header('Location: siswa.php?error=' . urlencode('Data siswa tidak valid.'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, nama, username, email, kelas, foto, status, created_at
    FROM users
    WHERE id = ? AND role = 'siswa'
    LIMIT 1
");
$stmt->execute([$id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    header('Location: siswa.php?error=' . urlencode('Data siswa tidak ditemukan.'));
    exit;
}

$fotoNama = trim($siswa['foto'] ?? '');
$fotoUrl = '../assets/images/avatar.svg';

if ($fotoNama !== '') {
    $fotoNamaAman = basename($fotoNama);
    $fotoPath = __DIR__ . '/../uploads/profile/' . $fotoNamaAman;
    if (is_file($fotoPath)) {
        $fotoUrl = '../uploads/profile/' . rawurlencode($fotoNamaAman) . '?v=' . filemtime($fotoPath);
    }
}

// Riwayat Peminjaman Siswa
$stmtPinjam = $pdo->prepare("
    SELECT * FROM peminjaman WHERE user_id = ? ORDER BY id DESC LIMIT 10
");
$stmtPinjam->execute([$id]);
$riwayatPinjam = $stmtPinjam->fetchAll(PDO::FETCH_ASSOC);

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

    <div class="card" style="max-width: 900px; margin: 0 auto 24px;">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <circle cx="12" cy="8" r="5"/>
                        <path d="M20 21a8 8 0 1 0-16 0"/>
                    </svg>
                    <?= $modeEdit ? 'Edit Data Siswa' : 'Informasi Detail Siswa' ?>
                </h3>
                <p class="card-subtitle">Akun ID #<?= $siswa['id'] ?> &bull; Terdaftar sejak <?= date('d M Y', strtotime($siswa['created_at'])) ?></p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="siswa.php" class="btn btn-secondary btn-sm">
                    &larr; Data Siswa
                </a>
                <?php if (!$modeEdit): ?>
                    <a href="profil_siswa.php?id=<?= $id ?>&edit=1" class="btn btn-primary btn-sm">
                        ✏️ Edit Siswa
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($modeEdit): ?>
            <!-- FORM EDIT SISWA -->
            <form action="../proses/edit_profil_siswa.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= (int)$siswa['id'] ?>">

                <div style="display: flex; align-items: center; gap: 20px; padding: 18px; background: var(--slate-50); border-radius: var(--radius-md); margin-bottom: 24px; flex-wrap: wrap;">
                    <img src="<?= e($fotoUrl) ?>" alt="Foto" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: var(--shadow-sm);">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: 700; font-size: 13px; color: var(--slate-800); display: block; margin-bottom: 4px;">
                            Ganti Foto Profil Siswa
                        </label>
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding: 6px 12px; font-size: 12.5px;">
                        <small class="helper">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="nama" name="nama" value="<?= e($siswa['nama']) ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="username">NIS / Username</label>
                        <input type="text" id="username" value="<?= e($siswa['username']) ?>" class="form-control" disabled style="background: var(--slate-100);">
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?= e($siswa['email']) ?>" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas <span class="required">*</span></label>
                        <input type="text" id="kelas" name="kelas" value="<?= e($siswa['kelas']) ?>" class="form-control" required placeholder="Contoh: X RPL 1, XI TKJ">
                    </div>

                    <div class="form-group">
                        <label for="status">Status Akun Siswa <span class="required">*</span></label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="aktif" <?= $siswa['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $siswa['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="profil_siswa.php?id=<?= $id ?>" class="btn btn-secondary btn-lg">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </form>

        <?php else: ?>
            <!-- VIEW DETAIL SISWA -->
            <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 24px; flex-wrap: wrap;">
                <img src="<?= e($fotoUrl) ?>" alt="Foto" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: var(--shadow-md);">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--slate-900); margin-bottom: 4px;">
                        <?= e($siswa['nama']) ?>
                    </h2>
                    <p style="font-size: 13px; color: var(--slate-500);">
                        NIS / Username: <strong><?= e($siswa['username']) ?></strong> &bull; Email: <strong><?= e($siswa['email'] ?: '-') ?></strong>
                    </p>
                    <div style="margin-top: 8px; display: flex; gap: 8px;">
                        <span class="badge" style="background: var(--primary-50); color: var(--primary-700);">
                            Kelas <?= e($siswa['kelas']) ?>
                        </span>
                        <?php if ($siswa['status'] === 'aktif'): ?>
                            <span class="badge badge-dikembalikan">Akun Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-ditolak">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT PINJAM SISWA -->
            <h4 style="font-size: 15px; font-weight: 800; color: var(--slate-900); margin-bottom: 12px;">
                Riwayat Peminjaman Terakhir Siswa Ini
            </h4>

            <?php if (!empty($riwayatPinjam)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Batas Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayatPinjam as $rp): ?>
                                <tr>
                                    <td><strong><?= e($rp['nama_buku']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($rp['tanggal_pinjam'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($rp['tanggal_rencana_kembali'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= e($rp['status']) ?>">
                                            <?= ucfirst(e($rp['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="font-size: 13px; color: var(--slate-400); padding: 12px 0;">
                    Siswa ini belum memiliki riwayat peminjaman buku.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>