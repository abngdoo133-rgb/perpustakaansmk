<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('siswa');

$page_title = 'Ganti Password';
$base = '../';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div>
                <strong>Gagal Mengubah Password</strong>
                <p><?= e($_GET['error']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <div>
                <strong>Berhasil</strong>
                <p><?= e($_GET['success']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 550px; margin: 0 auto;">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Keamanan & Password Akun
                </h3>
                <p class="card-subtitle">Ganti password akun kamu secara berkala untuk menjaga keamanan data.</p>
            </div>
        </div>

        <form method="post" action="../proses/password.php">
            <div class="form-group">
                <label for="password_lama">Password Saat Ini <span class="required">*</span></label>
                <input class="form-control" type="password" id="password_lama" name="password_lama" required placeholder="Masukkan password lama">
            </div>

            <div class="form-group">
                <label for="password_baru">Password Baru <span class="required">*</span></label>
                <input class="form-control" type="password" id="password_baru" name="password_baru" minlength="6" required placeholder="Minimal 6 karakter">
                <small class="helper">Gunakan kombinasi huruf dan angka agar lebih kuat.</small>
            </div>

            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Password Baru <span class="required">*</span></label>
                <input class="form-control" type="password" id="konfirmasi" name="konfirmasi" required placeholder="Ulangi password baru">
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    🔐 Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
