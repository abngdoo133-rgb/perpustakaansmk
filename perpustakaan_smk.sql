-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2026 at 09:06 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan_smk`
--

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas`
--

CREATE TABLE `aktivitas` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `aktivitas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aktivitas`
--

INSERT INTO `aktivitas` (`id`, `user_id`, `aktivitas`, `created_at`) VALUES
(1, NULL, 'Mengajukan peminjaman: Agama', '2026-09-04 03:41:27'),
(2, 5, 'Mengajukan peminjaman: Agama', '2026-09-04 04:54:46'),
(3, 5, 'Mengajukan peminjaman buku: mtk sebanyak 40 buku', '2026-09-07 03:52:28'),
(4, 5, 'Status peminjaman mtk diubah menjadi disetujui', '2026-09-07 04:06:51'),
(5, 5, 'Mengajukan peminjaman buku: mtk sebanyak 5 buku', '2026-09-07 05:12:19'),
(6, 5, 'Status peminjaman mtk milik YUSUF diubah menjadi dikembalikan', '2026-09-07 05:12:54'),
(7, 5, 'Status peminjaman mtk milik Albu diubah menjadi dikembalikan', '2026-09-07 05:13:01'),
(8, 5, 'Status peminjaman Agama milik Ridho Kurniawan diubah menjadi disetujui', '2026-09-07 05:13:08'),
(9, NULL, 'Status peminjaman Agama milik ridho diubah menjadi ditolak', '2026-09-07 05:13:19'),
(10, 5, 'Status peminjaman mtk milik YUSUF diubah menjadi dikembalikan', '2026-09-07 06:16:33'),
(11, 5, 'Mengajukan peminjaman buku PJOK kelas XI PPLG sebanyak 19 buku', '2026-09-07 06:43:06'),
(12, 5, 'Buku mtk kelas XI milik YUSUF telah dikembalikan sebanyak 5 buku', '2026-09-07 06:57:45'),
(13, 5, 'Buku PJOK kelas XI PPLG milik YUSUF telah dikembalikan sebanyak 19 buku', '2026-09-07 06:57:49'),
(14, 5, 'Buku mtk kelas XI PPLG milik Albu telah dikembalikan sebanyak 40 buku', '2026-09-07 06:58:03'),
(15, 5, 'Status peminjaman Agama kelas x rpl milik Ridho Kurniawan diubah menjadi disetujui', '2026-09-07 06:58:07'),
(16, 5, 'Buku Agama kelas x rpl milik Ridho Kurniawan telah dikembalikan sebanyak 20 buku', '2026-09-07 06:58:14'),
(17, NULL, 'Status peminjaman Agama kelas x rpl milik ridho diubah menjadi ditolak', '2026-09-07 06:58:20'),
(18, NULL, 'Status peminjaman Agama kelas x rpl milik ridho diubah menjadi dipinjam', '2026-09-07 07:36:03'),
(19, NULL, 'Buku Agama kelas x rpl milik ridho telah dikembalikan sebanyak 20 buku', '2026-09-07 07:36:13'),
(20, NULL, 'Data siswa saya kelas swwsw telah dihapus oleh administrator.', '2026-09-07 07:36:42'),
(21, NULL, 'Data siswa ridho kurniawan kelas XII RPL telah dihapus oleh administrator.', '2026-09-07 08:07:59'),
(22, 5, 'Mengajukan peminjaman buku Bahasa Indonesia kelas XII PPLG sebanyak 30 buku', '2026-09-07 08:09:57'),
(23, 5, 'Status peminjaman Bahasa Indonesia kelas XII PPLG milik Ridho Kurniawan diubah menjadi disetujui', '2026-09-07 08:20:51'),
(24, 5, 'Peminjaman buku Agama kelas XI PPLG sebanyak 12 buku langsung disetujui', '2026-09-07 08:28:35'),
(25, 5, 'Menghapus peminjaman buku \"Agama\" sebanyak 12 buku.', '2026-09-07 08:50:29'),
(26, 5, 'Menghapus peminjaman buku \"Bahasa Indonesia\" sebanyak 30 buku.', '2026-09-07 08:50:34'),
(27, 5, 'Meminjam buku \"SENI RUPA SMK/MK KLS.10/KM\" kelas X sebanyak 10 buku. Waktu mulai 09-09-2026 14:00 WIB, batas kembali 09-09-2026 15:00 WIB.', '2026-09-09 06:40:47'),
(28, 5, 'Siswa melengkapi profil. Kelas: X PPLG.', '2026-09-09 07:09:20'),
(29, 5, 'Meminjam buku \"PEND. JASMANI ORKES SMK/MAK KLS. 10/KM\" kelas X PPLG sebanyak 15 buku. Waktu mulai 09-09-2026 14:00 WIB, batas kembali 09-09-2026 15:00 WIB.', '2026-09-09 07:29:12'),
(30, 5, 'Buku PEND. JASMANI ORKES SMK/MAK KLS. 10/KM kelas X PPLG milik Ridho Kurniawan telah dikembalikan sebanyak 15 buku', '2026-09-09 07:39:34'),
(31, NULL, 'Siswa melengkapi profil. Kelas: X PPLG.', '2026-09-09 07:52:46'),
(32, NULL, 'Meminjam buku \"DSR2 AGRITEKNOLOGI PENGOLAHAN HASIL PERTANIAN KLS. 10 VOL1\" kelas X PPLG sebanyak 1 buku. Waktu mulai 09-09-2026 15:00 WIB, batas kembali 09-10-2026 16:00 WIB.', '2026-09-09 07:56:43'),
(33, NULL, 'Data siswa  kelas - telah dihapus oleh administrator.', '2026-09-09 07:57:59'),
(34, NULL, 'Siswa memperbarui profil. Kelas: X PPLG.', '2026-09-09 08:05:06'),
(35, 5, 'Siswa memperbarui profil.', '2026-09-10 02:44:45'),
(36, 5, 'Siswa memperbarui profil.', '2026-09-10 02:45:03'),
(37, NULL, 'Siswa memperbarui profil.', '2026-09-10 02:45:41'),
(38, NULL, 'Siswa memperbarui profil.', '2026-09-10 02:46:02'),
(39, 5, 'Meminjam buku \"DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI\" kelas X PPLG sebanyak 20 buku. Waktu mulai 10-09-2026 10:00 WIB, batas kembali 10-09-2026 14:00 WIB.', '2026-09-10 03:20:03'),
(40, 5, 'Meminjam buku \"DASAR2 KULINER PROG KEAHLIAN KULINER SMK/MAK KLS.10 VOL.1/KM\" kelas X PPLG sebanyak 1 buku. Waktu mulai 10-09-2026 10:00 WIB, batas kembali 10-09-2026 14:00 WIB.', '2026-09-10 03:28:49'),
(41, NULL, 'Buku DSR2 AGRITEKNOLOGI PENGOLAHAN HASIL PERTANIAN KLS. 10 VOL1 kelas X PPLG milik marcelino telah dikembalikan sebanyak 1 buku', '2026-09-10 03:29:21'),
(42, 5, 'Buku DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan telah dikembalikan sebanyak 20 buku', '2026-09-10 03:29:35'),
(43, 5, 'Buku DASAR2 KULINER PROG KEAHLIAN KULINER SMK/MAK KLS.10 VOL.1/KM kelas X PPLG milik Ridho Kurniawan telah dikembalikan sebanyak 1 buku', '2026-09-10 03:29:42'),
(44, 5, 'Meminjam buku \"DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI\" kelas X PPLG sebanyak 1 buku. Waktu mulai 10-09-2026 10:02 WIB, batas kembali 10-09-2026 10:10 WIB.', '2026-09-10 03:43:19'),
(45, 5, 'Meminjam buku \"DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI\" kelas X PPLG sebanyak 1 buku. Waktu mulai 10-09-2026 20:00 WIB, batas kembali 10-09-2026 20:05 WIB.', '2026-09-10 03:44:54'),
(46, 5, 'Buku DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan telah dikembalikan sebanyak 1 buku', '2026-09-10 04:01:28'),
(47, 5, 'Meminjam buku \"DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI\" kelas X PPLG sebanyak 2 buku. Waktu mulai 10-09-2026 23:27 WIB, batas kembali 10-09-2026 23:30 WIB.', '2026-09-10 04:26:18'),
(48, 5, 'Buku SENI RUPA SMK/MK KLS.10/KM kelas X milik Ridho Kurniawan telah dikembalikan sebanyak 10 buku', '2026-09-10 04:47:42'),
(49, 5, 'Buku DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan telah dikembalikan sebanyak 2 buku', '2026-09-10 06:27:08'),
(50, 5, 'Status peminjaman DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan diubah menjadi disetujui', '2026-09-10 06:27:23'),
(51, 5, 'Status peminjaman DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan diubah menjadi ditolak', '2026-09-10 06:27:35'),
(52, 5, 'Status peminjaman DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan diubah menjadi disetujui', '2026-09-10 06:36:02'),
(53, 5, 'Buku DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI kelas X PPLG milik Ridho Kurniawan telah dikembalikan sebanyak 1 buku', '2026-09-10 06:36:08'),
(54, NULL, 'Siswa memperbarui profil.', '2026-09-10 08:32:11'),
(55, NULL, 'Siswa memperbarui profil.', '2026-09-10 08:32:33'),
(56, NULL, 'Siswa memperbarui profil.', '2026-09-10 08:33:01'),
(57, NULL, 'Siswa memperbarui profil.', '2026-09-10 08:48:04'),
(58, 5, 'Siswa memperbarui profil.', '2026-09-14 15:16:47'),
(59, 5, 'Siswa memperbarui profil.', '2026-09-15 04:07:06'),
(60, 5, 'Meminjam buku \"AKUN. LMBG/INSTANSI PEMERINTAH-KK AKUNTANSI SMK KLS.12/KM\" kelas XII sebanyak 10 buku. Waktu mulai 15-09-2026 23:20 WIB, batas kembali 15-09-2026 23:30 WIB.', '2026-09-15 04:08:05'),
(61, 5, 'Meminjam buku \"BUPENA MATEMATIKA SMK/MK KLS.12/XII\" kelas XII sebanyak 1 buku. Waktu mulai 15-09-2026 23:20 WIB, batas kembali 15-09-2026 23:30 WIB.', '2026-09-15 04:08:46'),
(62, 5, 'Meminjam buku \"MATEMATIKA RUMP. BISNIS & MANAJEMEN SMK/MK KLS.12/XII\" kelas XII sebanyak 1 buku. Waktu mulai 15-09-2026 23:20 WIB, batas kembali 15-09-2026 23:30 WIB.', '2026-09-15 04:09:56'),
(63, 5, 'Meminjam buku \"MATEMATIKA RUMP. TEKNOLOGI SMK/MK KLS.12/XII\" kelas XII sebanyak 1 buku. Waktu mulai 15-09-2026 12:30 WIB, batas kembali 15-09-2026 12:34 WIB.', '2026-09-15 05:19:39'),
(64, 5, 'Buku \"MATEMATIKA RUMP. TEKNOLOGI SMK/MK KLS.12/XII\" milik Ridho Kurniawan telah dikembalikan sebanyak 1 buku. Stok telah ditambahkan kembali.', '2026-09-16 03:31:25'),
(65, 5, 'Buku \"AKUN. LMBG/INSTANSI PEMERINTAH-KK AKUNTANSI SMK KLS.12/KM\" milik Ridho Kurniawan telah dikembalikan sebanyak 10 buku. Stok telah ditambahkan kembali.', '2026-09-16 03:32:06'),
(66, 5, 'Buku \"MATEMATIKA RUMP. BISNIS & MANAJEMEN SMK/MK KLS.12/XII\" milik Ridho Kurniawan telah dikembalikan sebanyak 1 buku. Stok telah ditambahkan kembali.', '2026-09-16 03:32:41'),
(67, 5, 'Status peminjaman \"BUPENA MATEMATIKA SMK/MK KLS.12/XII\" milik Ridho Kurniawan diubah menjadi disetujui', '2026-09-16 03:33:29'),
(68, 5, 'Buku \"BUPENA MATEMATIKA SMK/MK KLS.12/XII\" milik Ridho Kurniawan telah dikembalikan sebanyak 1 buku. Stok telah ditambahkan kembali.', '2026-09-16 03:33:46'),
(69, 5, 'Meminjam buku \"BUPENA MERDEKA, B.INDONESIA SMK/MK KLS.12/XII\" kelas XII sebanyak 5 buku. Waktu mulai 17-09-2026 10:15 WIB, batas kembali 17-09-2026 10:30 WIB.', '2026-09-17 03:09:33'),
(70, 5, 'Buku \"BUPENA MERDEKA, B.INDONESIA SMK/MK KLS.12/XII\" milik Ridho Kurniawan telah dikembalikan sebanyak 5 buku. Stok telah ditambahkan kembali.', '2026-09-17 04:23:20');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int UNSIGNED NOT NULL,
  `nama_buku` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Umum',
  `kelas` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'X',
  `mata_pelajaran` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_buku` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rak` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_rak` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penerbit` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pengarang` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_perolehan` year DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stok_total` int UNSIGNED NOT NULL DEFAULT '50',
  `stok_tersedia` int UNSIGNED NOT NULL DEFAULT '50',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `nama_buku`, `kategori`, `kelas`, `mata_pelajaran`, `jenis_buku`, `rak`, `nomor_rak`, `penerbit`, `pengarang`, `tahun_perolehan`, `foto`, `stok_total`, `stok_tersedia`, `created_at`, `updated_at`) VALUES
(11, 'BUPENA MERDEKA, B.INDONESIA SMK/MK KLS.12/XII', 'Umum', 'XII', 'Bahasa Indonesia', NULL, NULL, NULL, 'ERLANGGA', 'Pipit Dwi Komariah', '2024', 'buku_1789020752_4fc66c7dee.webp', 25, 25, '2026-09-09 06:08:16', '2026-09-17 04:23:20'),
(12, 'DSR2 AGRITEKNOLOGI PENGOLAHAN HASIL PERTANIAN KLS. 10 VOL1', 'Produktif', 'X', 'APHP', NULL, NULL, NULL, 'ERLANGGA', 'Retno U.K. I Fitri N. I Fitria R.E. I Ari W.P.', '2023', 'buku_1789025456_144c536ecb.png', 20, 19, '2026-09-09 06:08:16', '2026-09-10 07:30:56'),
(13, 'PENYIMPANAN BIBIT ATP SMK/MK FASE F KLS.12/KM', 'Produktif', 'XII', 'ATP', NULL, NULL, NULL, 'ANDI', 'HIERONYMUS BUDI SANTOSO', '2025', 'buku_1789027873_74a7c1e20c.jpg', 10, 10, '2026-09-09 06:08:16', '2026-09-10 08:11:13'),
(14, 'SENI RUPA SMK/MK KLS.10/KM', 'Umum', 'X', 'SBK', NULL, NULL, NULL, 'ERLANGGA', 'SUGIYANTO I M. HASAM BASORI', '2024', 'buku_1789027182_66e75adfa8.jpg', 15, 15, '2026-09-09 06:08:16', '2026-09-10 07:59:42'),
(15, 'SENI MUSIK SMK/MK KLS.10/KM', 'Umum', 'X', 'SBK', NULL, NULL, NULL, 'ERLANGGA', 'T. AGUSTIEN P.R.', '2024', 'buku_1789027238_897af7ff64.jpg', 16, 16, '2026-09-09 06:08:16', '2026-09-10 08:00:38'),
(16, 'B.Indonesia KLS XII', 'Umum', 'XII', 'Bahasa Indonesia', NULL, NULL, NULL, 'ERLANGGA', 'Pipit Dwi Komariah', '2024', 'buku_1789020618_b780baa2da.png', 30, 30, '2026-09-09 06:08:16', '2026-09-10 06:10:18'),
(17, 'DSR2 AGRITEKNOLOGI PENGOLAHAN HASIL PERTANIAN KLS. 10 VOL3', 'Produktif', 'X', 'APHP', NULL, NULL, NULL, 'ERLANGGA', 'Retno U.K. I Fitri N. I Fitria R.E.', '2023', 'buku_1789025569_23d3acd070.jpg', 20, 20, '2026-09-09 06:08:16', '2026-09-10 07:32:49'),
(18, 'PENYIMPANAN LAHAN ATP SMK/MK FASE F KLS.12/KM', 'Produktif', 'XII', 'ATP', NULL, NULL, NULL, 'ANDI', 'HIERONYMUS BUDI SANTOSO', '2025', 'buku_1789027905_ddf47d77af.jpg', 10, 10, '2026-09-09 06:08:16', '2026-09-10 08:11:45'),
(19, 'KEAMANAN PANGAN APHP SMK/MK FASE F', 'Produktif', 'Umum', 'APHP', NULL, NULL, NULL, 'ANDI', 'ELY RAHAYU', '2025', 'buku_1789025932_bfbfe98a7a.jpg', 10, 10, '2026-09-09 06:08:16', '2026-09-10 07:38:52'),
(20, 'PEND. JASMANI ORKES SMK/MAK KLS. 10/KM', 'Umum', 'X', 'PJOK', NULL, NULL, NULL, 'ERLANGGA', 'M. AZHAR MUSTABSHIRIN', '2024', 'buku_1789026747_b750b7c898.webp', 15, 0, '2026-09-09 06:08:16', '2026-09-10 07:52:27'),
(21, 'BUPENA MATEMATIKA SMK/MK KLS.12/XII', 'Umum', 'XII', 'MATEMATIKA', NULL, NULL, NULL, 'ERLANGGA', 'Kasmina l Toali', '2024', 'buku_1789020683_3753ec0ced.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-16 03:33:46'),
(22, 'DASAR2 KULINER PROG KEAHLIAN KULINER SMK/MAK KLS.10 VOL.1/KM', 'Produktif', 'X', 'APHP', NULL, NULL, NULL, 'ERLANGGA', 'Uut Susiyanti l Wahyu Suprihartini', '2023', 'buku_1789025165_0a92376e27.jpg', 25, 24, '2026-09-09 06:08:16', '2026-09-10 07:26:05'),
(23, 'PROD. OLAHAN HASIL HEWANI APHP SMK/MK FASE F', 'Produktif', 'Umum', 'APHP', NULL, NULL, NULL, 'ANDI', 'NUERKHASANAH', '2025', 'buku_1789028225_0cf54ce0f9.jpg', 10, 10, '2026-09-09 06:08:16', '2026-09-10 08:17:05'),
(24, 'PEND. JASMANI ORKES SMK/MAK KLS. 11/KM', 'Umum', 'XI', 'PJOK', NULL, NULL, NULL, 'ERLANGGA', 'M. AZHAR MUSTABSHIRIN', '2024', 'buku_1789027296_5ef3663ef5.jpg', 15, 15, '2026-09-09 06:08:16', '2026-09-10 08:01:36'),
(26, 'MATEMATIKA RUMP. TEKNOLOGI SMK/MK KLS.12/XII', 'Umum', 'XII', 'MATEMATIKA', NULL, NULL, NULL, 'ERLANGGA', 'Arif Ediyanto l Maya Harsasi', '2024', 'buku_1789026649_ae5e544d8f.webp', 30, 30, '2026-09-09 06:08:16', '2026-09-16 03:31:25'),
(27, 'PKK BIDANG KEAHLIAN BISNIS MANJ. SMK FASE F KLS 11/KM', 'Umum', 'XI', 'PKK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti I JOKO WIDODO', '2024', 'buku_1789028074_87b2a4d253.jpg', 14, 14, '2026-09-09 06:08:16', '2026-09-10 08:14:34'),
(28, 'PRODUKSI OLAHAN HASIL NABATI SMK/MAK KLS.11/KM REVISI', 'Produktif', 'XI', 'APHP', NULL, NULL, NULL, 'ERLANGGA', 'Fitri N. I Retno U.K. I Fitria R.E. I Ari W.P.', '2025', 'buku_1789028165_2e5ff96e87.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 08:16:05'),
(29, 'MATEMATIKA RUMP. BISNIS & MANAJEMEN SMK/MK KLS.12/XII', 'Umum', 'XII', 'MATEMATIKA', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti l Kusmuriyanto', '2024', 'buku_1789026623_d5460b822e.png', 18, 18, '2026-09-09 06:08:16', '2026-09-16 03:32:41'),
(30, 'PEMROGRAMAN BERBASIS TEKS, GRAFIS DAN MULTIMEDIA SMK/MK FASE F', 'Produktif', 'Umum', 'PPLG', NULL, NULL, NULL, 'ANDI', 'PATWIYANTO I ZAENAL ARIFIN', '2025', 'buku_1789026718_5ee33d5c26.jpg', 10, 10, '2026-09-09 06:08:16', '2026-09-10 07:51:58'),
(31, 'PRODUKSI OLAHAN HASIL NABATI SMK/MAK KLS. 12/KM REVISI', 'Produktif', 'XII', 'APHP', NULL, NULL, NULL, 'ERLANGGA', 'Fitri N. I Retno U.K. I Fitria R.E. I Ari W.P.', '2025', 'buku_1789028191_b370e7b7d8.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 08:16:31'),
(32, 'PKK BIDANG KEAHLIAN TEKNOLOGI INFORMASI SMK FASE F KLS 12/KM', 'Umum', 'XII', 'PKK', NULL, NULL, NULL, 'ERLANGGA', 'ANDI NOVIANTO', '2024', 'buku_1789028116_b7d7c45cf6.jpg', 15, 15, '2026-09-09 06:08:16', '2026-09-10 08:15:16'),
(33, 'SPLASH RUMP. TEKNOLOGI SMK/MK KLS.12/XII', 'Umum', 'XII', NULL, NULL, NULL, NULL, 'ERLANGGA', 'Anik M.indriastuti', '2024', 'buku_1789026999_3fc4444a50.png', 30, 30, '2026-09-09 06:08:16', '2026-09-10 07:56:39'),
(34, 'SPLASH RUMP. BISNIS & MANAJEMEN SMK/MK KLS.12/XII', 'Umum', 'XII', NULL, NULL, NULL, NULL, 'ERLANGGA', 'Anik M.indriastuti', '2024', 'buku_1789027030_f059eafed0.png', 18, 18, '2026-09-09 06:08:16', '2026-09-10 07:57:10'),
(35, 'PEMOGRAMAN WEB KK REKAYASA PERANGKAT LUNAK SMK KLS 11/KM', 'Produktif', 'XI', 'PPLG', NULL, NULL, NULL, 'ERLANGGA', 'OKTA P.', '2024', 'buku_1789026683_7997edc7f1.jpg', 16, 16, '2026-09-09 06:08:16', '2026-09-10 07:51:23'),
(36, 'AKUNTANSI PERUSAHAAN JASA-KK AKUNTANSI SMK/MAK KLS.11/KM', 'Produktif', 'XI', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti l Aris Budianto l Esti Wibawani', '2024', 'buku_1789020395_9bf057614e.webp', 25, 25, '2026-09-09 06:08:16', '2026-09-10 06:06:35'),
(37, 'AKUNTANSI PERUSAHAAN DAGANG-KK AKUNTANSI SMK/MAK KLS. 11', 'Produktif', 'XI', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti l Aris Budianto l Esti Wibawani', '2024', 'buku_1789020264_75f91558a7.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 06:04:24'),
(38, 'AKUNTANSI PERUSAHAAN MANUFAKTUR-KK AKUNTANSI SMK KLS.12/', 'Produktif', 'XII', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti', '2024', 'buku_1789020543_a283e5536d.jpg', 18, 18, '2026-09-09 06:08:16', '2026-09-10 06:09:03'),
(39, 'PROSEDUR PENGGUNAAN KENDARAAN RINGAN SMK/MAK KLS.12/KM', 'Produktif', 'XII', 'TKR', NULL, NULL, NULL, 'ERLANGGA', 'Kasus Buwono l Berti Sagendra', '2024', 'buku_1789027812_817c493a2f.jpg', 30, 30, '2026-09-09 06:08:16', '2026-09-10 08:10:12'),
(40, 'AKUNTANSI PERUSAHAAN MANUFAKTUR-KK AKUNTANSI SMK KLS. 12', 'Produktif', 'XII', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti', '2024', 'buku_1789020457_72451f4ec1.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 06:07:37'),
(41, 'AKUN. LMBG/INSTANSI PEMERINTAH-KK AKUNTANSI SMK KLS.11/KM', 'Produktif', 'XI', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti l Kusmayadi l Aris Budianto', '2024', 'buku_1789020004_694d62568c.webp', 25, 25, '2026-09-09 06:08:16', '2026-09-10 06:01:32'),
(42, 'SISTEM PEMINDAH TENAGA KENDARAAN RINGAN SMK/MAK KLS. 12', 'Produktif', 'XII', 'TKR', NULL, NULL, NULL, 'ERLANGGA', 'Muh Rusyad N.', '2024', 'buku_1789027144_142b77793f.jpg', 30, 30, '2026-09-09 06:08:16', '2026-09-10 07:59:04'),
(43, 'KOMPAK PRSH DAGANG ACCURATE (KK AKUNTANSI) SMK/MAK FASE F', 'Produktif', 'Umum', 'AKL', NULL, NULL, NULL, 'ERLANGGA', 'Bimo Suciono l Yudhi Rahmanto', '2024', 'buku_1789026217_f8bb2b9879.webp', 25, 25, '2026-09-09 06:08:16', '2026-09-10 07:43:37'),
(44, 'PROJEK KREATIF DAN KEWIRAUSAHAAN-BK TMR SMK/MAK KLS. 12', 'Umum', 'XII', NULL, NULL, NULL, NULL, 'ERLANGGA', 'Andi Novianto', '2024', 'buku_1789027961_6a4fea614c.jpg', 22, 22, '2026-09-09 06:08:16', '2026-09-10 08:12:41'),
(45, 'UP5 (UNJUK P5) KEBEKERJAAN SMK/MAK KLS.12/KM', 'Umum', 'XII', NULL, NULL, NULL, NULL, 'ERLANGGA', 'Arif Ediyanto l Nining Mariyaningsih', '2024', 'buku_1789026979_c8d82b61e4.jpg', 30, 30, '2026-09-09 06:08:16', '2026-09-10 07:56:19'),
(46, 'KOMPAK PRSH JASA ACCURATE (KK AKUNTANSI) SMK/MAK FASE F/KM', 'Produktif', 'Umum', 'AKL', NULL, NULL, NULL, 'ERLANGGA', 'Bimo Suciono l Yudhi Rahmanto', '2024', 'buku_1789026314_1395c40e93.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 07:45:14'),
(47, 'KOMPAK PRSH JASA MYOB (KK AKUNTANSI) SMK/MAK FASE F/KM', 'Produktif', 'Umum', 'AKL', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti l Aris Budianto l Esti Wibawani', '2024', 'buku_1789026401_e71b2f8b67.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 07:46:41'),
(48, 'SISTEM SASIS KENDARAAN RINGAN. SMK/MK KLS. 12', 'Produktif', 'XII', 'TKR', NULL, NULL, NULL, 'ERLANGGA', 'Muh Rusyad N.', '2024', 'buku_1789027075_37e2fae45a.jpg', 30, 30, '2026-09-09 06:08:16', '2026-09-10 07:57:55'),
(49, 'PKK BIDANG KEAHLIAN TEKNOLOGI INFORMASI . SMK/MK KLS 12', 'Umum', 'XII', 'PKK', NULL, NULL, NULL, 'ERLANGGA', 'Berti Sagendra', '2024', 'buku_1789028091_5285169608.jpg', 30, 30, '2026-09-09 06:08:16', '2026-09-10 08:14:51'),
(50, 'PRAKTIK KERJA LAPANGAN BERBASIS INDUSTRI SMK/MAK KLS.12/KM', 'Produktif', 'XII', NULL, NULL, NULL, NULL, 'ERLANGGA', 'Arif Ediyanto l Sri Wahyudi l Kamariyanto', '2025', 'buku_1789028135_9c82715259.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 08:15:35'),
(51, 'PERPAJAKAN (KK AKUNTANSI) SMK/MAK KLS.11 FASE F/KM', 'Produktif', 'XI', 'AKL', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti', '2024', 'buku_1789028050_9fa05bcfed.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-10 08:14:10'),
(52, 'AKUNTANSI KEUANGAN (KK AKUNTANSI) SMK/MAK KLS.12 FASE F/KM', 'Produktif', 'XII', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti', '2024', 'buku_1789020188_b575c41fd2.jpg', 18, 18, '2026-09-09 06:08:16', '2026-09-10 06:03:08'),
(53, 'PERPAJAKAAN (KK AKUNTANSI) SMK/MAK KLS.12 FASE F/KM', 'Produktif', 'XII', 'AKL', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti', '2024', 'buku_1789027934_3b91bc558d.jpg', 18, 18, '2026-09-09 06:08:16', '2026-09-10 08:12:14'),
(54, 'AKUN. LMBG/INSTANSI PEMERINTAH-KK AKUNTANSI SMK KLS.12/KM', 'Produktif', 'XII', 'KK', NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti l Kusmayadi l Aris Budianto', '2025', 'buku_1789020070_906baf259b.jpg', 25, 25, '2026-09-09 06:08:16', '2026-09-16 03:32:06'),
(55, 'PEND.AGAMA ISLAM & BUDI PEKERTI SMK KLS.12/KM', 'Umum', 'XII', 'AGAMA', NULL, NULL, NULL, 'ERLANGGA', 'H.A. SHOLEH DIMYATHI I MUNAWIR AM', '2024', 'buku_1789027843_b0d81a04b9.jpg', 24, 24, '2026-09-09 06:08:16', '2026-09-10 08:10:43'),
(56, 'PROYEK KREATIF DAN KEWIRAUSAHAAN-BK BISNIS MANAJ SMK KLS. 12', 'Umum', 'XII', NULL, NULL, NULL, NULL, 'ERLANGGA', 'Dwi Harti I JOKO WIDODO', '2024', 'buku_1789027355_533686adbc.jpg', 18, 18, '2026-09-09 06:08:16', '2026-09-10 08:02:35'),
(57, 'DASAR2 ANIMASI SMK/MAK KLS. 10 VOL.1/KM REVISI', 'Umum', 'X', 'SBK', NULL, NULL, NULL, NULL, NULL, NULL, 'buku_1789022480_d87c8fbc27.jpg', 50, 50, '2026-09-10 06:36:02', '2026-09-10 07:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `buku_id` int UNSIGNED DEFAULT NULL,
  `nama_siswa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_buku` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int UNSIGNED NOT NULL DEFAULT '1',
  `tanggal_pinjam` date NOT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `tanggal_rencana_kembali` date DEFAULT NULL,
  `waktu_rencana_kembali` time DEFAULT NULL,
  `tanggal_waktu_kembali` datetime DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('menunggu','disetujui','dipinjam','dikembalikan','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profil_lengkap` tinyint(1) NOT NULL DEFAULT '0',
  `role` enum('admin','siswa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `email`, `password`, `kelas`, `foto`, `profil_lengkap`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator Perpustakaan', 'admin', 'admin@perpus.local', 'admin123', NULL, 'user_1_1788708436.jpg', 0, 'admin', 'aktif', '2026-09-04 03:29:45', '2026-09-06 15:27:16'),
(5, 'Ridho Kurniawan', 'albu', 'ridho@gmail.com', '111111', 'XII', 'siswa_5_c1872ce445680d50cf4f.jpg', 1, 'siswa', 'aktif', '2026-09-04 04:21:33', '2026-09-15 04:07:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aktivitas_user` (`user_id`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_buku_kelas` (`nama_buku`,`kelas`),
  ADD UNIQUE KEY `unique_buku_kelas` (`nama_buku`,`kelas`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_peminjaman_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aktivitas`
--
ALTER TABLE `aktivitas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD CONSTRAINT `fk_aktivitas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
