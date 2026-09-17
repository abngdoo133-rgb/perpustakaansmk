<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


// ===============================
// CEK METHOD
// ===============================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/buku.php');
    exit;
}


// ===============================
// AMBIL DATA FORM
// ===============================

$nama_buku = trim($_POST['nama_buku'] ?? '');
$kategori = trim($_POST['kategori'] ?? 'Umum');
$kelas = trim($_POST['kelas'] ?? '');
$mata_pelajaran = trim($_POST['mata_pelajaran'] ?? '');
$rak = trim($_POST['rak'] ?? '');
$nomor_rak = trim($_POST['nomor_rak'] ?? '');
$penerbit = trim($_POST['penerbit'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$tahun_perolehan = trim($_POST['tahun_perolehan'] ?? '');
$stok_total = (int) ($_POST['stok_total'] ?? 0);


// ===============================
// VALIDASI
// ===============================

if ($nama_buku === '') {
    kembali('Judul buku wajib diisi.');
}

if ($kelas === '') {
    kembali('Kelas buku wajib dipilih.');
}

if ($stok_total < 1) {
    kembali('Jumlah stok minimal 1 buku.');
}

if ($tahun_perolehan !== '') {

    $tahun = (int) $tahun_perolehan;

    if ($tahun < 1900 || $tahun > 2100) {
        kembali('Tahun perolehan tidak valid.');
    }

} else {

    $tahun_perolehan = null;
}


// ===============================
// FOTO
// ===============================

$namaFoto = null;

if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        kembali('Foto buku gagal diupload.');
    }


    // Maksimal 5 MB
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        kembali('Ukuran foto maksimal 5 MB.');
    }


    // Cek tipe file
    $tipe = mime_content_type($_FILES['foto']['tmp_name']);

    $tipeDiizinkan = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!in_array($tipe, $tipeDiizinkan, true)) {
        kembali('Foto harus berformat JPG, PNG, atau WEBP.');
    }


    // Buat folder jika belum ada
    $folderUpload = __DIR__ . '/../uploads/books/';

    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0755, true);
    }


    // Tentukan ekstensi
    $ekstensi = match ($tipe) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg'
    };


    // Nama file unik
    $namaFoto = 'buku_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ekstensi;

    $tujuanFoto = $folderUpload . $namaFoto;


    // Pindahkan foto
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuanFoto)) {
        kembali('Foto buku gagal disimpan.');
    }
}


// ===============================
// SIMPAN DATABASE
// ===============================

try {

    $pdo->beginTransaction();


    // Cek apakah judul + kelas sudah ada
    $cek = $pdo->prepare("
        SELECT id
        FROM buku
        WHERE nama_buku = ?
          AND kelas = ?
        LIMIT 1
    ");

    $cek->execute([
        $nama_buku,
        $kelas
    ]);

    if ($cek->fetch()) {

        $pdo->rollBack();

        // Hapus foto jika tadi sudah berhasil diupload
        if ($namaFoto !== null) {

            $fileFoto = __DIR__ . '/../uploads/books/' . $namaFoto;

            if (file_exists($fileFoto)) {
                unlink($fileFoto);
            }

        }

        kembali('Buku dengan judul dan kelas tersebut sudah ada.');
    }


    // ===============================
    // INSERT BUKU
    // ===============================

    $stmt = $pdo->prepare("
        INSERT INTO buku (
            nama_buku,
            kategori,
            kelas,
            mata_pelajaran,
            rak,
            nomor_rak,
            penerbit,
            pengarang,
            tahun_perolehan,
            foto,
            stok_total,
            stok_tersedia
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nama_buku,
        $kategori !== '' ? $kategori : 'Umum',
        $kelas,
        $mata_pelajaran !== '' ? $mata_pelajaran : null,
        $rak !== '' ? $rak : null,
        $nomor_rak !== '' ? $nomor_rak : null,
        $penerbit !== '' ? $penerbit : null,
        $pengarang !== '' ? $pengarang : null,
        $tahun_perolehan,
        $namaFoto,
        $stok_total,
        $stok_total
    ]);


    // ===============================
    // COMMIT
    // ===============================

    $pdo->commit();


    header('Location: ../admin/buku.php?status=tambah_sukses');
    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    // Hapus foto jika database gagal
    if ($namaFoto !== null) {

        $fileFoto = __DIR__ . '/../uploads/books/' . $namaFoto;

        if (file_exists($fileFoto)) {
            unlink($fileFoto);
        }

    }


    kembali('Buku gagal ditambahkan. Silakan coba lagi.');
}


// ===============================
// FUNGSI KEMBALI
// ===============================

function kembali($pesan)
{
    header(
        'Location: ../admin/tambah_buku.php?error=' .
        urlencode($pesan)
    );

    exit;
}