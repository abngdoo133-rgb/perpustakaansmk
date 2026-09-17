<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

header('Content-Type: application/json; charset=utf-8');

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nama_siswa,
            kelas,
            nama_buku,
            jumlah,
            tanggal_pinjam,
            status,
            created_at
        FROM peminjaman
        WHERE status = 'disetujui'
        ORDER BY id DESC
        LIMIT 10
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $latestId = 0;

    if (!empty($data)) {
        $latestId = (int) $data[0]['id'];
    }

    echo json_encode([
        'success'   => true,
        'count'     => count($data),
        'latest_id' => $latestId,
        'items'     => $data
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success'   => false,
        'count'     => 0,
        'latest_id' => 0,
        'items'     => []
    ]);

}