<?php
require 'config/database.php';

echo "=== TABEL: peminjaman ===\n";
$res = $pdo->query("DESCRIBE peminjaman");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Default'] . "\n";
}

echo "\n=== TABEL: buku ===\n";
$res = $pdo->query("DESCRIBE buku");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Default'] . "\n";
}

echo "\n=== TABEL: users ===\n";
$res = $pdo->query("DESCRIBE users");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Default'] . "\n";
}

echo "\n=== TABEL: aktivitas ===\n";
try {
    $res = $pdo->query("DESCRIBE aktivitas");
    foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . " | " . $col['Default'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

