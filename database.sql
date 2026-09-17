CREATE DATABASE IF NOT EXISTS perpustakaan_smk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan_smk;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) DEFAULT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    kelas VARCHAR(50) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    role ENUM('admin','siswa') NOT NULL DEFAULT 'siswa',
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE peminjaman (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    nama_siswa VARCHAR(100) NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    nama_buku VARCHAR(200) NOT NULL,
    jumlah INT UNSIGNED NOT NULL DEFAULT 1,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE DEFAULT NULL,
    status ENUM('menunggu','disetujui','dipinjam','dikembalikan','ditolak') NOT NULL DEFAULT 'menunggu',
    catatan VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_peminjaman_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE aktivitas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aktivitas_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Password demo: password
INSERT INTO users (nama, username, email, password, kelas, role)
VALUES ('Administrator Perpustakaan', 'admin', 'admin@perpus.local',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC8y2y2vV6jQwQ4Q3Q3W',
        NULL, 'admin');
