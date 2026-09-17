<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('siswa');

date_default_timezone_set('Asia/Jakarta');

$userId = (int)($_SESSION['user']['id'] ?? 0);
if ($userId <= 0) {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, nama, username, email, kelas, foto, profil_lengkap
    FROM users
    WHERE id = ? AND role = 'siswa'
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../logout.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$fotoNama = trim($user['foto'] ?? '');
$fotoUrl = '../assets/images/avatar.svg';

if ($fotoNama !== '') {
    $fotoNamaAman = basename($fotoNama);
    $fotoPath = __DIR__ . '/../uploads/profile/' . $fotoNamaAman;

    if (is_file($fotoPath)) {
        $versiFoto = filemtime($fotoPath);
        $fotoUrl = '../uploads/profile/' . rawurlencode($fotoNamaAman) . '?v=' . $versiFoto . '&siswa=' . $userId;
    }
}

$page_title = 'Profil Saya';
$base = '../';

require '../includes/header.php';
require '../includes/sidebar.php';
?>

<main class="main-content">
    <?php require '../includes/topbar.php'; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <div>
                <strong>Berhasil!</strong>
                <p><?= e($success) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div>
                <strong>Terjadi Kesalahan</strong>
                <p><?= e($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <circle cx="12" cy="8" r="5"/>
                        <path d="M20 21a8 8 0 1 0-16 0"/>
                    </svg>
                    Informasi Akun Siswa
                </h3>
                <p class="card-subtitle">Perbarui data profil, kelas, dan foto Anda untuk kebutuhan administrasi perpustakaan.</p>
            </div>
            <div>
                <?php if ((int)($user['profil_lengkap'] ?? 0) === 1): ?>
                    <span class="badge badge-dikembalikan">✓ Profil Lengkap</span>
                <?php else: ?>
                    <span class="badge badge-terlambat">⚠️ Perlu Dilengkapi</span>
                <?php endif; ?>
            </div>
        </div>

        <form action="../proses/profil.php" method="POST" enctype="multipart/form-data">
            <!-- FOTO PROFIL -->
            <div style="display: flex; align-items: center; gap: 20px; padding: 18px; background: var(--slate-50); border-radius: var(--radius-md); margin-bottom: 24px; flex-wrap: wrap;">
                <img src="<?= e($fotoUrl) ?>" alt="Foto Profil" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: var(--shadow-sm);">
                
                <div style="flex: 1; min-width: 200px;">
                    <label style="font-weight: 700; font-size: 13px; color: var(--slate-800); display: block; margin-bottom: 4px;">
                        Ganti Foto Profil
                    </label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding: 6px 12px; font-size: 12.5px;">
                    <small class="helper">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                </div>

                <?php if ($fotoNama !== ''): ?>
                    <button type="submit" name="hapus_foto" value="1" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus foto profil ini?')">
                        Hapus Foto
                    </button>
                <?php endif; ?>
            </div>

            <!-- GRID INPUT -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama" value="<?= e($user['nama'] ?? '') ?>" class="form-control" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label for="username">NIS / Username</label>
                    <input type="text" id="username" value="<?= e($user['username'] ?? '') ?>" class="form-control" disabled style="background: var(--slate-100);">
                    <small class="helper">Username tidak dapat diubah.</small>
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>" class="form-control" required placeholder="nama@email.com">
                </div>

                <div class="form-group">
                    <label for="kelas">Tingkat / Kelas <span class="required">*</span></label>
                    <input type="text" id="kelas" name="kelas" value="<?= e($user['kelas'] ?? '') ?>" class="form-control" required placeholder="Contoh: X RPL 1, XI TKJ 2, XII BDP">
                    <small class="helper">Pastikan diawali dengan X, XI, atau XII.</small>
                </div>
            </div>

            <div style="margin-top: 12px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>