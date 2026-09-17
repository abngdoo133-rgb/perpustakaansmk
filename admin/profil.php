<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Profil Administrator';
$base = '../';

$userId = (int)($_SESSION['user']['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

$fotoNama = trim($u['foto'] ?? '');
$fotoUrl = '../assets/images/avatar.svg';

if ($fotoNama !== '') {
    $fotoNamaAman = basename($fotoNama);
    $fotoPath = __DIR__ . '/../uploads/profile/' . $fotoNamaAman;
    if (is_file($fotoPath)) {
        $fotoUrl = '../uploads/profile/' . rawurlencode($fotoNamaAman) . '?v=' . filemtime($fotoPath);
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
            <div><strong>Berhasil</strong><p><?= e($success) ?></p></div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div><strong>Gagal</strong><p><?= e($error) ?></p></div>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 750px; margin: 0 auto;">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <circle cx="12" cy="8" r="5"/>
                        <path d="M20 21a8 8 0 1 0-16 0"/>
                    </svg>
                    Profil Administrator Perpustakaan
                </h3>
                <p class="card-subtitle">Kelola data informasi identitas akun administrator.</p>
            </div>
            <span class="badge badge-disetujui">Administrator</span>
        </div>

        <form method="post" action="../proses/profil.php" enctype="multipart/form-data">
            <!-- FOTO -->
            <div style="display: flex; align-items: center; gap: 20px; padding: 18px; background: var(--slate-50); border-radius: var(--radius-md); margin-bottom: 24px; flex-wrap: wrap;">
                <img src="<?= e($fotoUrl) ?>" alt="Foto" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: var(--shadow-sm);">
                <div style="flex: 1; min-width: 200px;">
                    <label style="font-weight: 700; font-size: 13px; color: var(--slate-800); display: block; margin-bottom: 4px;">
                        Foto Profil
                    </label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding: 6px 12px; font-size: 12.5px;">
                    <small class="helper">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input class="form-control" id="nama" name="nama" value="<?= e($u['nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input class="form-control" id="username" value="<?= e($u['username']) ?>" disabled style="background: var(--slate-100);">
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input class="form-control" type="email" id="email" name="email" value="<?= e($u['email'] ?? '') ?>">
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
