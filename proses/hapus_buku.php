<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| CEK ID BUKU
|--------------------------------------------------------------------------
*/

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    kembali(
        'ID buku tidak valid.'
    );
}


/*
|--------------------------------------------------------------------------
| PROSES HAPUS
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | MULAI TRANSAKSI
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA BUKU
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama_buku,
            kelas,
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

    $buku = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    /*
    |--------------------------------------------------------------------------
    | BUKU TIDAK DITEMUKAN
    |--------------------------------------------------------------------------
    */

    if (!$buku) {

        throw new Exception(
            'Buku tidak ditemukan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HITUNG BUKU YANG SEDANG DIPINJAM
    |--------------------------------------------------------------------------
    */

    $sedangDipinjam =
        (int) $buku['stok_total']
        -
        (int) $buku['stok_tersedia'];


    /*
    |--------------------------------------------------------------------------
    | JIKA MASIH DIPINJAM
    |--------------------------------------------------------------------------
    */

    if ($sedangDipinjam > 0) {

        throw new Exception(
            'Buku tidak dapat dihapus karena masih ada '
            . $sedangDipinjam
            . ' buku yang sedang dipinjam.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CEK DATA PEMINJAMAN AKTIF
    |--------------------------------------------------------------------------
    |
    | Pemeriksaan ini menjadi pengaman tambahan.
    |
    */

    $cekPeminjaman = $pdo->prepare("
        SELECT COUNT(*)
        FROM peminjaman
        WHERE nama_buku = ?
          AND status IN (
              'menunggu',
              'disetujui',
              'dipinjam'
          )
    ");

    $cekPeminjaman->execute([
        $buku['nama_buku']
    ]);

    $jumlahPeminjamanAktif =
        (int) $cekPeminjaman->fetchColumn();


    if ($jumlahPeminjamanAktif > 0) {

        throw new Exception(
            'Buku tidak dapat dihapus karena masih memiliki peminjaman aktif.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN NAMA FOTO
    |--------------------------------------------------------------------------
    */

    $foto =
        $buku['foto'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | HAPUS DATA BUKU
    |--------------------------------------------------------------------------
    */

    $hapus = $pdo->prepare("
        DELETE FROM buku
        WHERE id = ?
    ");

    $hapus->execute([
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | CEK HASIL DELETE
    |--------------------------------------------------------------------------
    */

    if ($hapus->rowCount() !== 1) {

        throw new Exception(
            'Data buku gagal dihapus.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | HAPUS FOTO BUKU
    |--------------------------------------------------------------------------
    |
    | Foto dihapus setelah data database berhasil dihapus.
    |
    */

    if (!empty($foto)) {

        $namaFoto =
            basename($foto);

        $pathFoto =
            __DIR__
            . '/../uploads/books/'
            . $namaFoto;


        if (
            file_exists($pathFoto)
        ) {

            unlink($pathFoto);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BERHASIL
    |--------------------------------------------------------------------------
    */

    header(
        'Location: ../admin/buku.php?status=hapus_sukses'
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
    | KEMBALI KE DAFTAR BUKU
    |--------------------------------------------------------------------------
    */

    kembali(
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| FUNGSI REDIRECT
|--------------------------------------------------------------------------
*/

function kembali(
    $pesan
) {

    header(
        'Location: ../admin/buku.php?error='
        . urlencode($pesan)
    );

    exit;
}