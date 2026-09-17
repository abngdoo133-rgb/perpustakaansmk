<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Tambah Buku Baru';
$base = '../';

$error = $_GET['error'] ?? '';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div>
                <strong>Gagal Menambahkan Buku</strong>
                <p><?= e($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                        <path d="M12 9v6m-3-3h6"/>
                    </svg>
                    Tambah Buku ke Katalog Perpustakaan
                </h3>
                <p class="card-subtitle">Lengkapi formulir di bawah ini untuk mendaftarkan buku baru ke dalam sistem.</p>
            </div>
            <a href="buku.php" class="btn btn-secondary btn-sm">
                &larr; Kembali ke Data Buku
            </a>
        </div>

        <form action="../proses/tambah_buku.php" method="POST" enctype="multipart/form-data">
            <!-- PREVIEW COVER -->
            <div style="display: flex; gap: 20px; align-items: center; padding: 20px; background: var(--slate-50); border-radius: var(--radius-md); margin-bottom: 24px; flex-wrap: wrap;">
                <div style="width: 90px; height: 125px; border-radius: 6px; background: var(--slate-200); border: 2px dashed var(--slate-300); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;" id="previewContainer">
                    <img id="coverPreview" src="" alt="Preview Cover" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    <span id="previewPlaceholder" style="font-size: 11px; color: var(--slate-400); text-align: center; padding: 6px;">Cover Buku</span>
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <label style="font-weight: 700; font-size: 13px; color: var(--slate-800); display: block; margin-bottom: 4px;">
                        Foto / Sampul Buku
                    </label>
                    <input type="file" name="foto" id="inputFoto" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding: 6px 12px; font-size: 12.5px;" onchange="previewImage(this)">
                    <small class="helper">Format: JPG, PNG, atau WEBP. Maksimal 5 MB. Boleh dikosongkan jika belum ada cover.</small>
                </div>
            </div>

            <!-- FORM GRID -->
            <div class="form-group">
                <label for="nama_buku">Judul Buku <span class="required">*</span></label>
                <input type="text" id="nama_buku" name="nama_buku" class="form-control" required placeholder="Contoh: Pemrograman Berorientasi Objek dengan Java">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="kelas">Tingkat / Kelas Buku <span class="required">*</span></label>
                    <select id="kelas" name="kelas" class="form-control" required>
                        <option value="">-- Pilih Tingkat Kelas --</option>
                        <option value="X">Kelas X (Semua Jurusan)</option>
                        <option value="XI">Kelas XI (Semua Jurusan)</option>
                        <option value="XII">Kelas XII (Semua Jurusan)</option>
                        <option value="Umum">Umum / Semua Tingkat</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori Buku</label>
                    <input type="text" id="kategori" name="kategori" class="form-control" placeholder="Contoh: Paket, Kejuruan, Referensi, Novel" value="Umum">
                </div>

                <div class="form-group">
                    <label for="mata_pelajaran">Mata Pelajaran</label>
                    <input type="text" id="mata_pelajaran" name="mata_pelajaran" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak, Bahasa Indonesia">
                </div>

                <div class="form-group">
                    <label for="pengarang">Nama Pengarang / Penulis</label>
                    <input type="text" id="pengarang" name="pengarang" class="form-control" placeholder="Contoh: Prof. Dr. Andi Susanto">
                </div>

                <div class="form-group">
                    <label for="penerbit">Penerbit</label>
                    <input type="text" id="penerbit" name="penerbit" class="form-control" placeholder="Contoh: Kemendikbud, Erlangga">
                </div>

                <div class="form-group">
                    <label for="tahun_perolehan">Tahun Terbit / Perolehan</label>
                    <input type="number" id="tahun_perolehan" name="tahun_perolehan" class="form-control" placeholder="<?= date('Y') ?>" min="1900" max="2100" value="<?= date('Y') ?>">
                </div>

                <div class="form-group">
                    <label for="rak">Lokasi Rak</label>
                    <input type="text" id="rak" name="rak" class="form-control" placeholder="Contoh: Rak A, Rak RPL, Rak Umum">
                </div>

                <div class="form-group">
                    <label for="nomor_rak">Nomor Rak / Baris</label>
                    <input type="text" id="nomor_rak" name="nomor_rak" class="form-control" placeholder="Contoh: 01, B-03">
                </div>

                <div class="form-group">
                    <label for="stok_total">Jumlah Total Stok Buku <span class="required">*</span></label>
                    <input type="number" id="stok_total" name="stok_total" class="form-control" required min="1" value="50">
                    <small class="helper">Stok tersedia otomatis akan sama dengan total stok pada awal pendaftaran.</small>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="buku.php" class="btn btn-secondary btn-lg">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Simpan Buku ke Katalog
                </button>
            </div>
        </form>
    </div>
</main>

<script>
function previewImage(input) {
    const preview = document.getElementById('coverPreview');
    const placeholder = document.getElementById('previewPlaceholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
    }
}
</script>

<?php include '../includes/footer.php'; ?>