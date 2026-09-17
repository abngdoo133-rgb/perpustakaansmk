<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

require_role('siswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../siswa/riwayat.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$userId = (int)($_SESSION['user']['id'] ?? 0);

if ($id <= 0 || $userId <= 0) {
    header('Location: ../siswa/riwayat.php?error=Data+peminjaman+tidak+valid.');
    exit;
}

try {
    $pdo->beginTransaction();

    // Ambil peminjaman milik siswa yang statusnya masih disetujui
    $stmt = $pdo->prepare("
        SELECT *
        FROM peminjaman
        WHERE id = ?
          AND user_id = ?
          AND status = 'disetujui'
        FOR UPDATE
    ");
    $stmt->execute([$id, $userId]);
    $peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$peminjaman) {
        throw new Exception('Peminjaman tidak ditemukan atau tidak dapat dihapus.');
    }

    $namaBuku = $peminjaman['nama_buku'];
    $kelas = $peminjaman['kelas'];
    $jumlah = (int)$peminjaman['jumlah'];

    // Kunci data buku
    $stmt = $pdo->prepare("
        SELECT *
        FROM buku
        WHERE nama_buku = ?
          AND kelas = ?
        FOR UPDATE
    ");
    $stmt->execute([$namaBuku, $kelas]);
    $buku = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$buku) {
        throw new Exception('Data stok buku tidak ditemukan.');
    }

    // Kembalikan stok buku
    $stokBaru = min(
        (int)$buku['stok_total'],
        (int)$buku['stok_tersedia'] + $jumlah
    );

    $stmt = $pdo->prepare("
        UPDATE buku
        SET stok_tersedia = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $stokBaru,
        $buku['id']
    ]);

    // Catat aktivitas
    $stmt = $pdo->prepare("
        INSERT INTO aktivitas (user_id, aktivitas)
        VALUES (?, ?)
    ");

    $stmt->execute([
        $userId,
        'Menghapus peminjaman buku "' . $namaBuku . '" sebanyak ' . $jumlah . ' buku.'
    ]);

    // Hapus data peminjaman
    $stmt = $pdo->prepare("
        DELETE FROM peminjaman
        WHERE id = ?
          AND user_id = ?
    ");

    $stmt->execute([
        $id,
        $userId
    ]);

    $pdo->commit();

    header('Location: ../siswa/riwayat.php?success=Peminjaman+berhasil+dihapus+dan+stok+buku+dikembalikan.');
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../siswa/riwayat.php?error=' . urlencode($e->getMessage()));
    exit;
}