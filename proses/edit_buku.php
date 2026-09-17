<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| CEK REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../admin/buku.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| DATA FORM
|--------------------------------------------------------------------------
*/

$id =
    (int) ($_POST['id'] ?? 0);

$nama_buku =
    trim($_POST['nama_buku'] ?? '');

$kategori =
    trim($_POST['kategori'] ?? 'Umum');

$kelas =
    trim($_POST['kelas'] ?? '');

$mata_pelajaran =
    trim($_POST['mata_pelajaran'] ?? '');

$penerbit =
    trim($_POST['penerbit'] ?? '');

$pengarang =
    trim($_POST['pengarang'] ?? '');

$tahun_perolehan =
    trim($_POST['tahun_perolehan'] ?? '');

$stok_total =
    (int) ($_POST['stok_total'] ?? 0);

$hapus_foto =
    (int) ($_POST['hapus_foto'] ?? 0);


/*
|--------------------------------------------------------------------------
| VALIDASI ID
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    kembali(
        'Data buku tidak ditemukan.'
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI JUDUL
|--------------------------------------------------------------------------
*/

if ($nama_buku === '') {

    kembali(
        'Judul buku wajib diisi.',
        $id
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI KATEGORI
|--------------------------------------------------------------------------
*/

$kategoriDiizinkan = [
    'Umum',
    'Produktif',
    'Normatif',
    'Adaptif',
    'Lainnya'
];


if (
    !in_array(
        $kategori,
        $kategoriDiizinkan,
        true
    )
) {

    kembali(
        'Kategori buku tidak valid.',
        $id
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI KELAS
|--------------------------------------------------------------------------
*/

$kelasDiizinkan = [
    'X',
    'XI',
    'XII',
    'Umum'
];


if (
    !in_array(
        $kelas,
        $kelasDiizinkan,
        true
    )
) {

    kembali(
        'Kelas buku tidak valid.',
        $id
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI STOK
|--------------------------------------------------------------------------
*/

if ($stok_total < 1) {

    kembali(
        'Jumlah stok minimal 1 buku.',
        $id
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI TAHUN
|--------------------------------------------------------------------------
*/

if ($tahun_perolehan !== '') {

    $tahun =
        (int) $tahun_perolehan;

    if (
        $tahun < 1900 ||
        $tahun > 2100
    ) {

        kembali(
            'Tahun perolehan tidak valid.',
            $id
        );
    }

} else {

    $tahun_perolehan = null;
}


/*
|--------------------------------------------------------------------------
| VARIABEL FOTO
|--------------------------------------------------------------------------
*/

$namaFotoBaru = null;

$fotoLama = null;


/*
|--------------------------------------------------------------------------
| AMBIL FOTO LAMA
|--------------------------------------------------------------------------
*/

try {

    $stmtFoto =
        $pdo->prepare("
            SELECT foto
            FROM buku
            WHERE id = ?
            LIMIT 1
        ");

    $stmtFoto->execute([
        $id
    ]);

    $dataFoto =
        $stmtFoto->fetch(PDO::FETCH_ASSOC);


    if (!$dataFoto) {

        kembali(
            'Buku tidak ditemukan.',
            $id
        );
    }


    $fotoLama =
        $dataFoto['foto'] ?? null;


} catch (Throwable $e) {

    kembali(
        'Gagal mengambil data buku.',
        $id
    );
}


/*
|--------------------------------------------------------------------------
| UPLOAD FOTO BARU
|--------------------------------------------------------------------------
*/

if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error']
    !== UPLOAD_ERR_NO_FILE
) {

    if (
        $_FILES['foto']['error']
        !== UPLOAD_ERR_OK
    ) {

        kembali(
            'Foto buku gagal diupload.',
            $id
        );
    }


    if (
        $_FILES['foto']['size']
        > 5 * 1024 * 1024
    ) {

        kembali(
            'Ukuran foto maksimal 5 MB.',
            $id
        );
    }


    $tipe =
        mime_content_type(
            $_FILES['foto']['tmp_name']
        );


    $tipeDiizinkan = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];


    if (
        !in_array(
            $tipe,
            $tipeDiizinkan,
            true
        )
    ) {

        kembali(
            'Foto harus berformat JPG, PNG, atau WEBP.',
            $id
        );
    }


    $folderUpload =
        __DIR__
        . '/../uploads/books/';


    if (!is_dir($folderUpload)) {

        mkdir(
            $folderUpload,
            0755,
            true
        );
    }


    $ekstensi =
        match ($tipe) {

            'image/jpeg' => 'jpg',

            'image/png' => 'png',

            'image/webp' => 'webp',

            default => 'jpg'

        };


    $namaFotoBaru =
        'buku_'
        . time()
        . '_'
        . bin2hex(
            random_bytes(5)
        )
        . '.'
        . $ekstensi;


    $tujuanFoto =
        $folderUpload
        . $namaFotoBaru;


    if (
        !move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            $tujuanFoto
        )
    ) {

        kembali(
            'Foto buku gagal disimpan.',
            $id
        );
    }
}


/*
|--------------------------------------------------------------------------
| PROSES DATABASE
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | KUNCI DATA BUKU
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare("
            SELECT
                id,
                nama_buku,
                foto,
                stok_total,
                stok_tersedia
            FROM buku
            WHERE id = ?
            FOR UPDATE
        ");

    $stmt->execute([
        $id
    ]);


    $buku =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$buku) {

        throw new Exception(
            'Buku tidak ditemukan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HITUNG YANG SEDANG DIPINJAM
    |--------------------------------------------------------------------------
    */

    $sedangDipinjam =
        (int) $buku['stok_total']
        -
        (int) $buku['stok_tersedia'];


    /*
    |--------------------------------------------------------------------------
    | STOK BARU
    |--------------------------------------------------------------------------
    */

    if (
        $stok_total
        <
        $sedangDipinjam
    ) {

        throw new Exception(
            'Stok total tidak boleh lebih kecil dari jumlah buku yang sedang dipinjam.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CEK DUPLIKAT JUDUL + KELAS
    |--------------------------------------------------------------------------
    */

    $cek =
        $pdo->prepare("
            SELECT id
            FROM buku
            WHERE nama_buku = ?
              AND kelas = ?
              AND id != ?
            LIMIT 1
        ");


    $cek->execute([
        $nama_buku,
        $kelas,
        $id
    ]);


    if ($cek->fetch()) {

        throw new Exception(
            'Buku dengan judul dan kelas tersebut sudah ada.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOK TERSEDIA BARU
    |--------------------------------------------------------------------------
    */

    $stok_tersedia_baru =
        $stok_total
        -
        $sedangDipinjam;


    /*
    |--------------------------------------------------------------------------
    | TENTUKAN FOTO
    |--------------------------------------------------------------------------
    */

    if ($namaFotoBaru !== null) {

        /*
        | Ada foto baru
        */

        $fotoUntukDatabase =
            $namaFotoBaru;

    }

    elseif ($hapus_foto === 1) {

        /*
        | Foto dihapus
        */

        $fotoUntukDatabase =
            null;

    }

    else {

        /*
        | Tetap gunakan foto lama
        */

        $fotoUntukDatabase =
            $fotoLama;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DATA
    |--------------------------------------------------------------------------
    */

    $update =
        $pdo->prepare("
            UPDATE buku
            SET
                nama_buku = ?,
                kategori = ?,
                kelas = ?,
                mata_pelajaran = ?,
                penerbit = ?,
                pengarang = ?,
                tahun_perolehan = ?,
                foto = ?,
                stok_total = ?,
                stok_tersedia = ?
            WHERE id = ?
        ");


    $update->execute([

        $nama_buku,

        $kategori,

        $kelas,

        $mata_pelajaran !== ''
            ? $mata_pelajaran
            : null,

        $penerbit !== ''
            ? $penerbit
            : null,

        $pengarang !== ''
            ? $pengarang
            : null,

        $tahun_perolehan,

        $fotoUntukDatabase,

        $stok_total,

        $stok_tersedia_baru,

        $id

    ]);


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | HAPUS FOTO LAMA
    |--------------------------------------------------------------------------
    |
    | Foto lama dihapus hanya jika:
    |
    | 1. Ada foto baru
    | atau
    | 2. User memilih Hapus Cover
    |
    */

    if (
        !empty($fotoLama)
        &&
        (
            $namaFotoBaru !== null
            ||
            $hapus_foto === 1
        )
    ) {

        $fotoLamaPath =
            __DIR__
            . '/../uploads/books/'
            . basename($fotoLama);


        if (
            is_file($fotoLamaPath)
        ) {

            unlink($fotoLamaPath);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BERHASIL
    |--------------------------------------------------------------------------
    */

    header(
        'Location: ../admin/buku.php?status=edit_sukses'
    );

    exit;


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS FOTO BARU JIKA GAGAL
    |--------------------------------------------------------------------------
    */

    if (
        $namaFotoBaru !== null
    ) {

        $fotoBaruPath =
            __DIR__
            . '/../uploads/books/'
            . basename($namaFotoBaru);


        if (
            is_file($fotoBaruPath)
        ) {

            unlink($fotoBaruPath);
        }
    }


    kembali(
        $e->getMessage(),
        $id
    );
}


/*
|--------------------------------------------------------------------------
| REDIRECT ERROR
|--------------------------------------------------------------------------
*/

function kembali(
    $pesan,
    $id = 0
) {

    $url =
        '../admin/edit_buku.php';


    if ($id > 0) {

        $url .=
            '?id='
            . $id
            . '&error='
            . urlencode($pesan);

    } else {

        $url .=
            '?error='
            . urlencode($pesan);
    }


    header(
        'Location: ' . $url
    );

    exit;
}