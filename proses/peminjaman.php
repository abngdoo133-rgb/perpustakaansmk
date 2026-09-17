<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('siswa');

date_default_timezone_set('Asia/Jakarta');


/*
|--------------------------------------------------------------------------
| HARUS POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../siswa/buku.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| ID SISWA
|--------------------------------------------------------------------------
*/

$user_id = (int)($_SESSION['user']['id'] ?? 0);


if ($user_id <= 0) {

    header('Location: ../login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| FUNGSI KEMBALI
|--------------------------------------------------------------------------
*/

function kembali($pesan, $buku_id = 0)
{
    $url = '../siswa/pinjam.php?';

    if ($buku_id > 0) {

        $url .= 'buku_id=' . $buku_id . '&';
    }

    $url .= 'error=' . urlencode($pesan);

    header('Location: ' . $url);

    exit;
}


/*
|--------------------------------------------------------------------------
| TINGKAT KELAS
|--------------------------------------------------------------------------
*/

function tingkatKelas($kelas)
{
    $kelas = strtoupper(trim($kelas));

    if (preg_match('/^XII\b/', $kelas)) {

        return 'XII';
    }

    if (preg_match('/^XI\b/', $kelas)) {

        return 'XI';
    }

    if (preg_match('/^X\b/', $kelas)) {

        return 'X';
    }

    return '';
}


/*
|--------------------------------------------------------------------------
| CEK BUKU BOLEH DIPINJAM
|--------------------------------------------------------------------------
*/

function bukuBolehDipinjam($kelasSiswa, $kelasBuku)
{
    $kelasSiswa =
        strtoupper(trim($kelasSiswa));

    $kelasBuku =
        strtoupper(trim($kelasBuku));

    $tingkatSiswa =
        tingkatKelas($kelasSiswa);

    $tingkatBuku =
        tingkatKelas($kelasBuku);


    /*
    | Tingkat harus sama
    */

    if (
        $tingkatSiswa === '' ||
        $tingkatBuku === '' ||
        $tingkatSiswa !== $tingkatBuku
    ) {

        return false;
    }


    /*
    | Buku umum tingkat
    |
    | Contoh:
    | Siswa X boleh meminjam buku X
    | Siswa XI boleh meminjam buku XI
    | Siswa XII boleh meminjam buku XII
    */

    if ($kelasBuku === $tingkatBuku) {

        return true;
    }


    /*
    | Buku khusus kelas
    */

    return $kelasBuku === $kelasSiswa;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA SISWA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        nama,
        username,
        email,
        kelas,
        foto,
        profil_lengkap
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $user_id
]);

$profil =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$profil) {

    header('Location: ../login.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| PROFIL HARUS LENGKAP
|--------------------------------------------------------------------------
*/

if (
    (int)$profil['profil_lengkap'] !== 1 ||
    trim($profil['nama'] ?? '') === '' ||
    trim($profil['email'] ?? '') === '' ||
    trim($profil['kelas'] ?? '') === ''
) {

    header(
        'Location: ../siswa/profil.php?error=' .
        urlencode(
            'Lengkapi profil terlebih dahulu sebelum melakukan peminjaman buku.'
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DATA SISWA
|--------------------------------------------------------------------------
*/

$nama =
    trim($profil['nama']);

$kelas =
    strtoupper(
        trim($profil['kelas'])
    );


/*
|--------------------------------------------------------------------------
| AMBIL DATA FORM
|--------------------------------------------------------------------------
*/

$buku_id =
    (int)($_POST['buku_id'] ?? 0);

$jumlah =
    (int)($_POST['jumlah'] ?? 0);

$tanggal_pinjam =
    trim($_POST['tanggal_pinjam'] ?? '');

$waktu_mulai =
    trim($_POST['waktu_mulai'] ?? '');

$tanggal_kembali =
    trim($_POST['tanggal_rencana_kembali'] ?? '');

$waktu_kembali =
    trim($_POST['waktu_rencana_kembali'] ?? '');


/*
|--------------------------------------------------------------------------
| VALIDASI DASAR
|--------------------------------------------------------------------------
*/

if (
    $buku_id <= 0 ||
    $jumlah < 1 ||
    $tanggal_pinjam === '' ||
    $waktu_mulai === '' ||
    $tanggal_kembali === '' ||
    $waktu_kembali === ''
) {

    kembali(
        'Data peminjaman belum lengkap.',
        $buku_id
    );
}


/*
|--------------------------------------------------------------------------
| VALIDASI WAKTU
|--------------------------------------------------------------------------
*/

$mulai =
    strtotime(
        $tanggal_pinjam .
        ' ' .
        $waktu_mulai
    );


$kembali_waktu =
    strtotime(
        $tanggal_kembali .
        ' ' .
        $waktu_kembali
    );


if (
    $mulai === false ||
    $kembali_waktu === false
) {

    kembali(
        'Format tanggal atau waktu tidak valid.',
        $buku_id
    );
}


/*
|--------------------------------------------------------------------------
| TANGGAL HARI INI
|--------------------------------------------------------------------------
*/

$hariIni =
    date('Y-m-d');


if ($tanggal_pinjam < $hariIni) {

    kembali(
        'Tanggal mulai peminjaman tidak boleh sebelum hari ini.',
        $buku_id
    );
}


/*
|--------------------------------------------------------------------------
| WAKTU MULAI
|--------------------------------------------------------------------------
*/

if (
    $tanggal_pinjam === $hariIni &&
    $mulai < time()
) {

    kembali(
        'Waktu mulai peminjaman sudah lewat.',
        $buku_id
    );
}


/*
|--------------------------------------------------------------------------
| TANGGAL KEMBALI
|--------------------------------------------------------------------------
*/

if (
    $tanggal_kembali <
    $tanggal_pinjam
) {

    kembali(
        'Tanggal pengembalian tidak boleh sebelum tanggal mulai.',
        $buku_id
    );
}


/*
|--------------------------------------------------------------------------
| WAKTU KEMBALI
|--------------------------------------------------------------------------
*/

if (
    $kembali_waktu <=
    $mulai
) {

    kembali(
        'Waktu pengembalian harus lebih besar daripada waktu mulai peminjaman.',
        $buku_id
    );
}


/*
|--------------------------------------------------------------------------
| TRANSAKSI DATABASE
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | AMBIL BUKU BERDASARKAN ID
    |--------------------------------------------------------------------------
    |
    | INI BAGIAN PALING PENTING
    |
    | Tidak menggunakan nama buku.
    | Tidak menggunakan kelas untuk mencari buku.
    |
    | Langsung menggunakan ID buku.
    |
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama_buku,
            kelas,
            stok_total,
            stok_tersedia
        FROM buku
        WHERE id = ?
        FOR UPDATE
    ");

    $stmt->execute([
        $buku_id
    ]);

    $buku =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | BUKU TIDAK DITEMUKAN
    |--------------------------------------------------------------------------
    */

    if (!$buku) {

        throw new Exception(
            'Buku yang dipilih tidak ditemukan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CEK KELAS BUKU
    |--------------------------------------------------------------------------
    */

    $kelasBuku =
        strtoupper(
            trim($buku['kelas'] ?? '')
        );


    if (
        !bukuBolehDipinjam(
            $kelas,
            $kelasBuku
        )
    ) {

        $tingkatSiswa =
            tingkatKelas($kelas);

        $tingkatBuku =
            tingkatKelas($kelasBuku);


        if (
            $tingkatSiswa !==
            $tingkatBuku
        ) {

            throw new Exception(
                'Buku ini diperuntukkan untuk kelas ' .
                $kelasBuku .
                ', sedangkan kelas Anda adalah ' .
                $kelas .
                '.'
            );
        }


        throw new Exception(
            'Buku ini merupakan buku khusus untuk kelas ' .
            $kelasBuku .
            '.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CEK STOK
    |--------------------------------------------------------------------------
    */

    $stok =
        (int)$buku['stok_tersedia'];


    if ($stok <= 0) {

        throw new Exception(
            'Buku tersebut sedang tidak tersedia.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | JUMLAH BUKU
    |--------------------------------------------------------------------------
    */

    if ($jumlah > $stok) {

        throw new Exception(
            'Buku "' .
            $buku['nama_buku'] .
            '" hanya tersedia ' .
            $stok .
            ' buku.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | KURANGI STOK
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE buku
        SET stok_tersedia =
            stok_tersedia - ?
        WHERE id = ?
        AND stok_tersedia >= ?
    ");

    $stmt->execute([
        $jumlah,
        $buku_id,
        $jumlah
    ]);


    if ($stmt->rowCount() !== 1) {

        throw new Exception(
            'Stok buku gagal diperbarui.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN PEMINJAMAN
    |--------------------------------------------------------------------------
    */

    $catatan = trim($_POST['catatan'] ?? '');

    $stmt = $pdo->prepare("
        INSERT INTO peminjaman
        (
            user_id,
            buku_id,
            nama_siswa,
            kelas,
            nama_buku,
            jumlah,
            tanggal_pinjam,
            waktu_mulai,
            tanggal_rencana_kembali,
            waktu_rencana_kembali,
            catatan,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'disetujui'
        )
    ");

    $stmt->execute([
        $user_id,
        $buku_id,
        $nama,
        $kelas,
        $buku['nama_buku'],
        $jumlah,
        $tanggal_pinjam,
        $waktu_mulai,
        $tanggal_kembali,
        $waktu_kembali,
        $catatan !== '' ? $catatan : null
    ]);


    /*
    |--------------------------------------------------------------------------
    | AKTIVITAS
    |--------------------------------------------------------------------------
    */

    $aktivitas =
        'Meminjam buku "' .
        $buku['nama_buku'] .
        '" kelas ' .
        $kelas .
        ' sebanyak ' .
        $jumlah .
        ' buku. ' .
        'Waktu mulai ' .
        date(
            'd-m-Y H:i',
            $mulai
        ) .
        ' WIB, batas kembali ' .
        date(
            'd-m-Y H:i',
            $kembali_waktu
        ) .
        ' WIB.';


    $stmt = $pdo->prepare("
        INSERT INTO aktivitas
        (
            user_id,
            aktivitas
        )
        VALUES
        (
            ?,
            ?
        )
    ");

    $stmt->execute([
        $user_id,
        $aktivitas
    ]);


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: ../siswa/riwayat.php?success=' .
        urlencode(
            'Peminjaman buku "' . $buku['nama_buku'] . '" berhasil disetujui! Silakan ambil buku di perpustakaan.'
        )
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }


    kembali(
        $e->getMessage(),
        $buku_id
    );
}