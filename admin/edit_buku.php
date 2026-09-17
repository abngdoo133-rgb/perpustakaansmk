<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Edit Buku';
$base = '../';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: buku.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    header('Location: buku.php');
    exit;
}

$sedangDipinjam = (int)$buku['stok_total'] - (int)$buku['stok_tersedia'];
if ($sedangDipinjam < 0) $sedangDipinjam = 0;

$fotoPath = '';
if (!empty($buku['foto'])) {
    $namaFoto = basename($buku['foto']);
    $pathBooks = __DIR__ . '/../uploads/books/' . $namaFoto;
    $pathBuku = __DIR__ . '/../uploads/buku/' . $namaFoto;

    if (is_file($pathBooks)) {
        $fotoPath = '../uploads/books/' . rawurlencode($namaFoto) . '?v=' . filemtime($pathBooks);
    } elseif (is_file($pathBuku)) {
        $fotoPath = '../uploads/buku/' . rawurlencode($namaFoto) . '?v=' . filemtime($pathBuku);
    }
}

$error = $_GET['error'] ?? '';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div><strong>Gagal Memperbarui</strong><p><?= e($error) ?></p></div>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Edit Data Buku #<?= $buku['id'] ?>
                </h3>
                <p class="card-subtitle">Perbarui informasi, kategori, stok, dan cover buku.</p>
            </div>
            <a href="buku.php" class="btn btn-secondary btn-sm">
                &larr; Kembali ke Data Buku
            </a>
        </div>

        <form action="../proses/edit_buku.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$buku['id'] ?>">

            <!-- COVER PREVIEW -->
            <div style="display: flex; gap: 20px; align-items: center; padding: 20px; background: var(--slate-50); border-radius: var(--radius-md); margin-bottom: 24px; flex-wrap: wrap;">
                <div style="width: 90px; height: 125px; border-radius: 6px; background: var(--slate-200); border: 2px dashed var(--slate-300); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;" id="previewContainer">
                    <?php if ($fotoPath !== ''): ?>
                        <img id="coverPreview" src="<?= e($fotoPath) ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <img id="coverPreview" src="" alt="Cover" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <span id="previewPlaceholder" style="font-size: 11px; color: var(--slate-400); text-align: center; padding: 6px;">Tanpa Cover</span>
                    <?php endif; ?>
                </div>

                <div style="flex: 1; min-width: 220px;">
                    <label style="font-weight: 700; font-size: 13px; color: var(--slate-800); display: block; margin-bottom: 4px;">
                        Ganti Foto / Sampul Buku
                    </label>
                    <input type="file" name="foto" id="inputFoto" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding: 6px 12px; font-size: 12.5px;" onchange="previewImage(this)">
                    <small class="helper">Format: JPG, PNG, atau WEBP. Maksimal 5 MB.</small>

                    <?php if ($fotoPath !== ''): ?>
                        <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; font-size: 12px; color: var(--rose-600); cursor: pointer;">
                            <input type="checkbox" name="hapus_foto" value="1" style="accent-color: var(--rose-600);">
                            <span>Hapus cover saat ini (gunakan cover standar)</span>
                        </label>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FORM INPUTS -->
            <div class="form-group">
                <label for="nama_buku">Judul Buku <span class="required">*</span></label>
                <input type="text" id="nama_buku" name="nama_buku" value="<?= e($buku['nama_buku']) ?>" class="form-control" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="kelas">Tingkat / Kelas Buku <span class="required">*</span></label>
                    <select id="kelas" name="kelas" class="form-control" required>
                        <option value="X" <?= $buku['kelas'] === 'X' ? 'selected' : '' ?>>Kelas X</option>
                        <option value="XI" <?= $buku['kelas'] === 'XI' ? 'selected' : '' ?>>Kelas XI</option>
                        <option value="XII" <?= $buku['kelas'] === 'XII' ? 'selected' : '' ?>>Kelas XII</option>
                        <option value="Umum" <?= $buku['kelas'] === 'Umum' ? 'selected' : '' ?>>Umum</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori Buku <span class="required">*</span></label>
                    <select id="kategori" name="kategori" class="form-control" required>
                        <?php foreach (['Umum', 'Produktif', 'Normatif', 'Adaptif', 'Lainnya'] as $kat): ?>
                            <option value="<?= $kat ?>" <?= ($buku['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= $kat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mata_pelajaran">Mata Pelajaran</label>
                    <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="<?= e($buku['mata_pelajaran'] ?? '') ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="pengarang">Pengarang / Penulis</label>
                    <input type="text" id="pengarang" name="pengarang" value="<?= e($buku['pengarang'] ?? '') ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="penerbit">Penerbit</label>
                    <input type="text" id="penerbit" name="penerbit" value="<?= e($buku['penerbit'] ?? '') ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="tahun_perolehan">Tahun Perolehan</label>
                    <input type="number" id="tahun_perolehan" name="tahun_perolehan" value="<?= e($buku['tahun_perolehan'] ?? '') ?>" class="form-control" min="1900" max="2100">
                </div>

                <div class="form-group">
                    <label for="stok_total">Total Stok Buku <span class="required">*</span></label>
                    <input type="number" id="stok_total" name="stok_total" value="<?= (int)$buku['stok_total'] ?>" class="form-control" required min="<?= max(1, $sedangDipinjam) ?>">
                    <?php if ($sedangDipinjam > 0): ?>
                        <small class="helper" style="color: var(--amber-600); font-weight: 600;">
                            Catatan: <?= $sedangDipinjam ?> buku sedang dipinjam siswa. Stok minimal <?= $sedangDipinjam ?>.
                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="buku.php" class="btn btn-secondary btn-lg">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Simpan Perubahan Buku
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
    }
}
</script>

<?php include '../includes/footer.php'; ?>