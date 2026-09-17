<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


/* ==============================
   AMBIL ID SISWA
============================== */

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {

    header(
        'Location: ../admin/siswa.php?error=' .
        urlencode('Data siswa tidak valid.')
    );

    exit;
}


try {

    $pdo->beginTransaction();


    /* ==============================
       CARI SISWA
    ============================== */

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        AND role = 'siswa'
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$siswa) {

        $pdo->rollBack();

        header(
            'Location: ../admin/siswa.php?error=' .
            urlencode('Data siswa tidak ditemukan.')
        );

        exit;
    }


    /* ==============================
       AMBIL PEMINJAMAN AKTIF
    ============================== */

    $stmt = $pdo->prepare("
        SELECT *
        FROM peminjaman
        WHERE user_id = ?
        AND status IN (
            'menunggu',
            'disetujui',
            'dipinjam'
        )
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $peminjaman =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    /* ==============================
       KEMBALIKAN STOK
    ============================== */

    foreach ($peminjaman as $pinjam) {

        $namaBuku =
            trim($pinjam['nama_buku']);

        $kelas =
            trim($pinjam['kelas']);

        $jumlah =
            (int)$pinjam['jumlah'];


        $stmt = $pdo->prepare("
            SELECT *
            FROM buku
            WHERE nama_buku = ?
            AND kelas = ?
            FOR UPDATE
        ");

        $stmt->execute([
            $namaBuku,
            $kelas
        ]);

        $buku =
            $stmt->fetch(PDO::FETCH_ASSOC);


        if ($buku) {

            $stmt = $pdo->prepare("
                UPDATE buku
                SET stok_tersedia =
                    LEAST(
                        stok_total,
                        stok_tersedia + ?
                    )
                WHERE id = ?
            ");

            $stmt->execute([
                $jumlah,
                $buku['id']
            ]);
        }
    }


    /* ==============================
       CATAT AKTIVITAS
    ============================== */

    $aktivitas =
        "Data siswa "
        . $siswa['nama']
        . " kelas "
        . ($siswa['kelas'] ?? '-')
        . " telah dihapus oleh administrator.";


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
        $id,
        $aktivitas
    ]);


    /* ==============================
       HAPUS SISWA
    ============================== */

    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE id = ?
        AND role = 'siswa'
    ");

    $stmt->execute([$id]);


    $pdo->commit();


    /* ==============================
       HAPUS FOTO LAMA
       SETELAH DATABASE BERHASIL
    ============================== */

    $foto =
        trim($siswa['foto'] ?? '');

    if ($foto !== '') {

        $fotoPath =
            __DIR__
            . '/../uploads/profile/'
            . $foto;

        if (
            file_exists($fotoPath)
        ) {

            @unlink($fotoPath);

        }
    }


    header(
        'Location: ../admin/siswa.php?success=' .
        urlencode(
            'Data siswa berhasil dihapus.'
        )
    );

    exit;


} catch (Throwable $e) {

    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();

    }


    header(
        'Location: ../admin/siswa.php?error=' .
        urlencode(
            'Data siswa gagal dihapus.'
        )
    );

    exit;
}