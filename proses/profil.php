<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('siswa');

$userId = (int)($_SESSION['user']['id'] ?? 0);

if ($userId <= 0) {
    header('Location: ../login.php');
    exit;
}

$nama  = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$kelas = strtoupper(trim($_POST['kelas'] ?? ''));

if ($nama === '' || $email === '' || $kelas === '') {
    header(
        'Location: ../siswa/profil.php?error=' .
        urlencode('Nama, email, dan kelas wajib diisi.')
    );
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header(
        'Location: ../siswa/profil.php?error=' .
        urlencode('Format email tidak valid.')
    );
    exit;
}

try {

    $pdo->beginTransaction();

    // Ambil data siswa berdasarkan ID yang sedang login
    $stmt = $pdo->prepare("
        SELECT id, foto
        FROM users
        WHERE id = ?
          AND role = 'siswa'
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Data siswa tidak ditemukan.');
    }

    $fotoLama = trim($user['foto'] ?? '');
    $fotoBaru = $fotoLama;

    $folderFoto = __DIR__ . '/../uploads/profile/';

    if (!is_dir($folderFoto)) {
        mkdir($folderFoto, 0755, true);
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS FOTO
    |--------------------------------------------------------------------------
    */

    if (
        isset($_POST['hapus_foto']) &&
        $_POST['hapus_foto'] === '1'
    ) {

        if ($fotoLama !== '') {

            $fileLama = $folderFoto . basename($fotoLama);

            if (is_file($fileLama)) {
                @unlink($fileLama);
            }
        }

        $fotoBaru = null;
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD FOTO BARU
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Foto gagal diupload.');
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            throw new Exception('Ukuran foto maksimal 2 MB.');
        }

        $tmpName = $_FILES['foto']['tmp_name'];

        $mime = mime_content_type($tmpName);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowed[$mime])) {
            throw new Exception(
                'Foto harus JPG, PNG, atau WEBP.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE BENAR-BENAR UNIK
        |--------------------------------------------------------------------------
        */

        $namaFile =
            'siswa_' .
            $userId .
            '_' .
            bin2hex(random_bytes(10)) .
            '.' .
            $allowed[$mime];

        $fileBaru = $folderFoto . $namaFile;

        if (!move_uploaded_file($tmpName, $fileBaru)) {
            throw new Exception('Foto gagal disimpan.');
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS FOTO LAMA SETELAH FOTO BARU BERHASIL
        |--------------------------------------------------------------------------
        */

        if ($fotoLama !== '') {

            $fileLama = $folderFoto . basename($fotoLama);

            if (
                is_file($fileLama) &&
                realpath($fileLama) !== realpath($fileBaru)
            ) {
                @unlink($fileLama);
            }
        }

        $fotoBaru = $namaFile;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            nama = ?,
            email = ?,
            kelas = ?,
            foto = ?,
            profil_lengkap = 1
        WHERE id = ?
          AND role = 'siswa'
    ");

    $stmt->execute([
        $nama,
        $email,
        $kelas,
        $fotoBaru,
        $userId
    ]);

    /*
    |--------------------------------------------------------------------------
    | AKTIVITAS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO aktivitas
        (user_id, aktivitas)
        VALUES (?, ?)
    ");

    $stmt->execute([
        $userId,
        'Siswa memperbarui profil.'
    ]);

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | UPDATE SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION['user']['nama'] = $nama;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['kelas'] = $kelas;
    $_SESSION['user']['foto'] = $fotoBaru;
    $_SESSION['user']['profil_lengkap'] = 1;

    header(
        'Location: ../siswa/profil.php?success=' .
        urlencode('Profil berhasil disimpan.')
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: ../siswa/profil.php?error=' .
        urlencode($e->getMessage())
    );

    exit;
}