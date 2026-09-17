<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


/* ========================================
   AMBIL DATA
======================================== */

$id =
    (int)($_POST['id'] ?? 0);

$nama =
    trim($_POST['nama'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$kelas =
    strtoupper(
        trim($_POST['kelas'] ?? '')
    );

$status =
    trim($_POST['status'] ?? '');


/* ========================================
   VALIDASI
======================================== */

if ($id <= 0) {

    header(
        'Location: ../admin/siswa.php?error=' .
        urlencode('Data siswa tidak valid.')
    );

    exit;
}


if (
    $nama === '' ||
    $email === '' ||
    $kelas === ''
) {

    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&edit=1&error=' .
        urlencode(
            'Nama, email, dan kelas wajib diisi.'
        )
    );

    exit;
}


if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&edit=1&error=' .
        urlencode(
            'Format email tidak valid.'
        )
    );

    exit;
}


$kelasValid = [
    'X',
    'XI',
    'XII'
];


if (
    !in_array(
        $kelas,
        $kelasValid,
        true
    )
) {

    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&edit=1&error=' .
        urlencode(
            'Kelas tidak valid.'
        )
    );

    exit;
}


$statusValid = [
    'aktif',
    'nonaktif'
];


if (
    !in_array(
        $status,
        $statusValid,
        true
    )
) {

    $status = 'aktif';

}


try {

    $pdo->beginTransaction();


    /* ========================================
       AMBIL SISWA
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama,
            email,
            kelas,
            foto,
            status
        FROM users
        WHERE id = ?
          AND role = 'siswa'
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $siswa =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$siswa) {

        throw new Exception(
            'Data siswa tidak ditemukan.'
        );

    }


    /* ========================================
       FOTO LAMA
    ======================================== */

    $fotoLama =
        trim(
            $siswa['foto'] ?? ''
        );

    $fotoBaru =
        $fotoLama;


    $folderFoto =
        __DIR__ .
        '/../uploads/profile/';


    if (!is_dir($folderFoto)) {

        mkdir(
            $folderFoto,
            0755,
            true
        );

    }


    /* ========================================
       HAPUS FOTO
    ======================================== */

    $hapusFoto =
        isset($_POST['hapus_foto']) &&
        $_POST['hapus_foto'] === '1';


    if ($hapusFoto) {

        if ($fotoLama !== '') {

            $fileLama =
                $folderFoto .
                basename($fotoLama);

            if (is_file($fileLama)) {

                @unlink($fileLama);

            }

        }

        $fotoBaru = null;

    }


    /* ========================================
       UPLOAD FOTO BARU
    ======================================== */

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error']
        !== UPLOAD_ERR_NO_FILE
    ) {


        if (
            $_FILES['foto']['error']
            !== UPLOAD_ERR_OK
        ) {

            throw new Exception(
                'Foto gagal diupload.'
            );

        }


        /* UKURAN */

        if (
            $_FILES['foto']['size']
            > 2 * 1024 * 1024
        ) {

            throw new Exception(
                'Ukuran foto maksimal 2 MB.'
            );

        }


        $tmpName =
            $_FILES['foto']['tmp_name'];


        /* MIME */

        $mime =
            mime_content_type(
                $tmpName
            );


        $allowed = [

            'image/jpeg' => 'jpg',

            'image/png' => 'png',

            'image/webp' => 'webp'

        ];


        if (
            !isset(
                $allowed[$mime]
            )
        ) {

            throw new Exception(
                'Foto harus JPG, PNG, atau WEBP.'
            );

        }


        /* ====================================
           NAMA FILE UNIK
        ==================================== */

        $namaFile =
            'siswa_' .
            $id .
            '_' .
            bin2hex(
                random_bytes(10)
            ) .
            '.' .
            $allowed[$mime];


        $fileBaru =
            $folderFoto .
            $namaFile;


        if (
            !move_uploaded_file(
                $tmpName,
                $fileBaru
            )
        ) {

            throw new Exception(
                'Foto gagal disimpan.'
            );

        }


        /* ====================================
           HAPUS FOTO LAMA
        ==================================== */

        if ($fotoLama !== '') {

            $fileLama =
                $folderFoto .
                basename($fotoLama);


            if (
                is_file($fileLama) &&
                realpath($fileLama)
                !== realpath($fileBaru)
            ) {

                @unlink($fileLama);

            }

        }


        $fotoBaru =
            $namaFile;

    }


    /* ========================================
       UPDATE USERS
    ======================================== */

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            nama = ?,
            email = ?,
            kelas = ?,
            foto = ?,
            status = ?,
            profil_lengkap = 1
        WHERE id = ?
          AND role = 'siswa'
    ");


    $stmt->execute([

        $nama,

        $email,

        $kelas,

        $fotoBaru,

        $status,

        $id

    ]);


    /* ========================================
       AKTIVITAS
    ======================================== */

    $aktivitas =
        'Administrator memperbarui profil siswa '
        . $nama
        . '.';


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


    /* ========================================
       COMMIT
    ======================================== */

    $pdo->commit();


    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&success=' .
        urlencode(
            'Profil siswa berhasil diperbarui.'
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
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&edit=1&error=' .
        urlencode(
            $e->getMessage()
        )
    );

    exit;

}