<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


/*
|--------------------------------------------------------------------------
| HARUS POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        'Location: ../admin/peminjaman.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DATA FORM
|--------------------------------------------------------------------------
*/

$id =
    (int)($_POST['id'] ?? 0);

$statusBaru =
    trim($_POST['status'] ?? '');


$allowed = [
    'menunggu',
    'disetujui',
    'dipinjam',
    'dikembalikan',
    'ditolak'
];


if (
    $id <= 0 ||
    !in_array(
        $statusBaru,
        $allowed,
        true
    )
) {

    header(
        'Location: ../admin/peminjaman.php'
    );

    exit;
}


try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA PEMINJAMAN
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM peminjaman
        WHERE id = ?
        FOR UPDATE
    ");

    $stmt->execute([
        $id
    ]);

    $pinjam =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$pinjam) {

        throw new Exception(
            'Data peminjaman tidak ditemukan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DATA LAMA
    |--------------------------------------------------------------------------
    */

    $statusLama =
        $pinjam['status'];


    $jumlah =
        (int)$pinjam['jumlah'];


    /*
    |--------------------------------------------------------------------------
    | ID BUKU
    |--------------------------------------------------------------------------
    */

    $bukuId =
        (int)($pinjam['buku_id'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | STATUS YANG MENGGUNAKAN STOK
    |--------------------------------------------------------------------------
    */

    $statusPakaiStok = [
        'menunggu',
        'disetujui',
        'dipinjam'
    ];


    $lamaPakaiStok =
        in_array(
            $statusLama,
            $statusPakaiStok,
            true
        );


    $baruPakaiStok =
        in_array(
            $statusBaru,
            $statusPakaiStok,
            true
        );


    /*
    |--------------------------------------------------------------------------
    | CARI BUKU DENGAN buku_id
    |--------------------------------------------------------------------------
    */

    $buku = null;


    if ($bukuId > 0) {

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
            $bukuId
        ]);

        $buku =
            $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | DATA LAMA YANG BELUM MEMILIKI buku_id
    |--------------------------------------------------------------------------
    |
    | Ini hanya untuk peminjaman lama.
    |
    */

    if (!$buku) {

        $namaBuku =
            trim($pinjam['nama_buku']);


        $kelasSiswa =
            strtoupper(
                trim($pinjam['kelas'])
            );


        /*
        | Tentukan tingkat siswa
        */

        if (
            preg_match(
                '/^XII\b/',
                $kelasSiswa
            )
        ) {

            $tingkat =
                'XII';

        } elseif (
            preg_match(
                '/^XI\b/',
                $kelasSiswa
            )
        ) {

            $tingkat =
                'XI';

        } elseif (
            preg_match(
                '/^X\b/',
                $kelasSiswa
            )
        ) {

            $tingkat =
                'X';

        } else {

            $tingkat =
                'Umum';
        }


        /*
        |--------------------------------------------------------------------------
        | CARI BERDASARKAN NAMA + TINGKAT
        |--------------------------------------------------------------------------
        |
        | Hanya sebagai cadangan untuk data lama.
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
            WHERE TRIM(nama_buku) = ?
            ORDER BY
                CASE
                    WHEN TRIM(kelas) = ? THEN 1
                    WHEN TRIM(kelas) = ? THEN 2
                    WHEN TRIM(kelas) = 'Umum' THEN 3
                    ELSE 4
                END,
                id ASC
            LIMIT 1
            FOR UPDATE
        ");

        $stmt->execute([
            $namaBuku,
            $kelasSiswa,
            $tingkat
        ]);

        $buku =
            $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | KEMBALIKAN BUKU
    |--------------------------------------------------------------------------
    */

    if (
        $statusBaru ===
        'dikembalikan'
    ) {


        /*
        | Hanya kembalikan stok kalau
        | status sebelumnya memang memakai stok.
        */

        if ($lamaPakaiStok) {

            if (!$buku) {

                throw new Exception(
                    'Buku "' .
                    $pinjam['nama_buku'] .
                    '" tidak ditemukan.'
                );
            }


            $stokTotal =
                (int)$buku['stok_total'];


            $stokSekarang =
                (int)$buku['stok_tersedia'];


            /*
            | TAMBAHKAN STOK
            */

            $stokBaru =
                $stokSekarang +
                $jumlah;


            /*
            | Jangan melebihi stok total
            */

            if (
                $stokBaru >
                $stokTotal
            ) {

                $stokBaru =
                    $stokTotal;
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE STOK BUKU
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE buku
                SET stok_tersedia = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $stokBaru,
                $buku['id']
            ]);


            /*
            |--------------------------------------------------------------------------
            | CEK HASIL UPDATE
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT stok_tersedia
                FROM buku
                WHERE id = ?
            ");

            $stmt->execute([
                $buku['id']
            ]);

            $stokSesudah =
                (int)$stmt->fetchColumn();


            if (
                $stokSesudah !==
                $stokBaru
            ) {

                throw new Exception(
                    'Stok buku gagal dikembalikan.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CATAT AKTIVITAS
        |--------------------------------------------------------------------------
        */

        $aktivitas =
            'Buku "' .
            $pinjam['nama_buku'] .
            '" milik ' .
            $pinjam['nama_siswa'] .
            ' telah dikembalikan sebanyak ' .
            $jumlah .
            ' buku. Stok telah ditambahkan kembali.';


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
            $pinjam['user_id'],
            $aktivitas
        ]);


        /*
        |--------------------------------------------------------------------------
        | TANDAI PENGEMBALIAN - UPDATE STATUS (jangan dihapus agar histori terjaga)
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE peminjaman
            SET
                status = 'dikembalikan',
                tanggal_kembali = CURDATE(),
                tanggal_waktu_kembali = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$id]);


        /*
        |--------------------------------------------------------------------------
        | SELESAI
        |--------------------------------------------------------------------------
        */

        $pdo->commit();


        header(
            'Location: ../admin/peminjaman.php?success=' .
            urlencode(
                'Buku berhasil ditandai dikembalikan dan stok sudah ditambahkan kembali.'
            )
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS TIDAK MEMAKAI STOK
    | → STATUS MEMAKAI STOK
    |--------------------------------------------------------------------------
    */

    if (
        !$lamaPakaiStok &&
        $baruPakaiStok
    ) {

        if (!$buku) {

            throw new Exception(
                'Data buku tidak ditemukan.'
            );
        }


        $stok =
            (int)$buku['stok_tersedia'];


        if (
            $jumlah >
            $stok
        ) {

            throw new Exception(
                'Stok buku "' .
                $buku['nama_buku'] .
                '" hanya tersisa ' .
                $stok .
                ' buku.'
            );
        }


        $stmt = $pdo->prepare("
            UPDATE buku
            SET stok_tersedia =
                stok_tersedia - ?
            WHERE id = ?
            AND stok_tersedia >= ?
        ");

        $stmt->execute([
            $jumlah,
            $buku['id'],
            $jumlah
        ]);


        if (
            $stmt->rowCount() !==
            1
        ) {

            throw new Exception(
                'Stok buku gagal dikurangi.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS MEMAKAI STOK
    | → STATUS TIDAK MEMAKAI STOK
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | dipinjam → ditolak
    |
    */

    if (
        $lamaPakaiStok &&
        !$baruPakaiStok
    ) {

        if (!$buku) {

            throw new Exception(
                'Data buku tidak ditemukan.'
            );
        }


        $stokTotal =
            (int)$buku['stok_total'];


        $stokSekarang =
            (int)$buku['stok_tersedia'];


        $stokBaru =
            $stokSekarang +
            $jumlah;


        if (
            $stokBaru >
            $stokTotal
        ) {

            $stokBaru =
                $stokTotal;
        }


        $stmt = $pdo->prepare("
            UPDATE buku
            SET stok_tersedia = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $stokBaru,
            $buku['id']
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS PEMINJAMAN
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE peminjaman
        SET status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $statusBaru,
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | CATAT AKTIVITAS
    |--------------------------------------------------------------------------
    */

    $aktivitas =
        'Status peminjaman "' .
        $pinjam['nama_buku'] .
        '" milik ' .
        $pinjam['nama_siswa'] .
        ' diubah menjadi ' .
        $statusBaru;


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
        $pinjam['user_id'],
        $aktivitas
    ]);


    /*
    |--------------------------------------------------------------------------
    | SIMPAN
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: ../admin/peminjaman.php?success=' .
        urlencode(
            'Status peminjaman berhasil diperbarui.'
        )
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();
    }


    header(
        'Location: ../admin/peminjaman.php?error=' .
        urlencode(
            'Terjadi kesalahan: ' .
            $e->getMessage()
        )
    );

    exit;
}