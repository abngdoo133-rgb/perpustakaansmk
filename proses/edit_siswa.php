<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');


/* ==============================
   ID SISWA
============================== */

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {

    header(
        'Location: ../admin/siswa.php?error=' .
        urlencode('Data siswa tidak valid.')
    );

    exit;
}


/* ==============================
   DATA FORM
============================== */

$nama =
    trim($_POST['nama'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$kelas =
    strtoupper(
        trim($_POST['kelas'] ?? '')
    );

$status =
    trim($_POST['status'] ?? 'aktif');


/* ==============================
   VALIDASI
============================== */

if (
    $nama === '' ||
    $email === '' ||
    $kelas === ''
) {

    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&error=' .
        urlencode(
            'Nama, email, dan kelas wajib diisi.'
        )
    );

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&error=' .
        urlencode(
            'Format email tidak valid.'
        )
    );

    exit;
}


if (
    !in_array(
        $kelas,
        ['X', 'XI', 'XII'],
        true
    )
) {

    header(
        'Location: ../admin/profil_siswa.php?id=' .
        $id .
        '&error=' .
        urlencode(
            'Kelas tidak valid.'
        )
    );

    exit;
}


if (
    !in_array(
        $status,
        ['aktif', 'nonaktif'],
        true
    )
) {

    $status = 'aktif';

}


/* ==============================
   AMBIL SISWA
============================== */

$stmt = $pdo->prepare("
    SELECT
        id,
        nama,
        foto
    FROM users
    WHERE id = ?
      AND role = 'siswa'
    LIMIT 1
");

$stmt->execute([$id]);

$siswa =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$siswa) {

    header(
        'Location: ../admin/siswa.php?error=' .
        urlencode(
            'Data siswa tidak ditemukan.'
        )
    );

    exit;
}


$fotoLama =
    trim($siswa['foto'] ?? '');

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


try {

    $pdo->beginTransaction();


    /* ==============================
       HAPUS FOTO
    ============================== */

    if (
        isset($_POST['hapus_foto']) &&
        $_POST['hapus_foto'] === '1'
    ) {

        if ($fotoLama !== '') {

            $fotoPath =
                $folderFoto .
                basename($fotoLama);

            if (is_file($fotoPath)) {

                @unlink($fotoPath);

            }

        }

        $fotoBaru = null;

    }


    /* ==============================
       UPLOAD FOTO BARU
    ============================== */

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !==
        UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES['foto']['error'] !==
            UPLOAD_ERR_OK
        ) {

            throw new Exception(
                'Foto gagal diupload.'
            );

        }


        if (
            $_FILES['foto']['size'] >
            2 * 1024 * 1024
        ) {

            throw new Exception(
                'Ukuran foto maksimal 2 MB.'
            );

        }


        $tmpName =
            $_FILES['foto']['tmp_name'];


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


        /* ==============================
           NAMA FILE UNIK
        ============================== */

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


        /* ==============================
           HAPUS FOTO LAMA
        ============================== */

        if (
            $fotoLama !== ''
        ) {

            $fileLama =
                $folderFoto .
                basename($fotoLama);


            if (
                is_file($fileLama) &&
                realpath($fileLama) !==
                realpath($fileBaru)
            ) {

                @unlink(
                    $fileLama
                );

            }

        }


        $fotoBaru =
            $namaFile;

    }


    /* ==============================
       UPDATE USERS
    ============================== */

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            nama = ?,
            email = ?,
            kelas = ?,
            status = ?,
            foto = ?
        WHERE id = ?
          AND role = 'siswa'
    ");


    $stmt->execute([

        $nama,

        $email,

        $kelas,

        $status,

        $fotoBaru,

        $id

    ]);


    /* ==============================
       AKTIVITAS
    ============================== */

    $aktivitas =
        'Administrator mengubah profil siswa ' .
        $nama .
        ' kelas ' .
        $kelas .
        '.';


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


    $pdo->commit();


    /* ==============================
       BERHASIL
    ============================== */

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
        '&error=' .
        urlencode(
            $e->getMessage()
        )
    );

    exit;

}