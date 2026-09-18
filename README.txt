Perpustakaan SMK

Aplikasi Perpustakaan SMK adalah aplikasi perpustakaan berbasis web menggunakan PHP dan MySQL untuk mengelola siswa, buku, peminjaman, profil pengguna, dan aktivitas perpustakaan.

Fitur

Login dan registrasi siswa

Hak akses Admin dan Siswa

Dashboard Admin

Dashboard Siswa

Data siswa

Profil siswa

Edit nama, email, dan kelas

Upload, ganti, dan hapus foto profil

Katalog buku

Pemilihan buku untuk peminjaman

Pengajuan peminjaman buku

Status peminjaman: menunggu, disetujui, dipinjam, dikembalikan

Pengelolaan stok buku

Catatan aktivitas

Pencarian data siswa

Teknologi

PHP

MySQL

PDO

HTML5

CSS3

JavaScript

Laragon

Visual Studio Code

Struktur Project

perpustakaan_smk/
├── admin/
│   ├── dashboard.php
│   ├── siswa.php
│   └── profil_siswa.php
├── siswa/
│   ├── dashboard.php
│   └── profil.php
├── proses/
│   ├── profil.php
│   └── hapus_siswa.php
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── header.php
│   ├── sidebar.php
│   ├── topbar.php
│   └── footer.php
├── assets/
│   └── images/
│       └── avatar.svg
├── uploads/
│   └── profile/
├── login.php
├── register.php
└── README.md

Database

Nama database yang digunakan:

perpustakaan_smk

Tabel utama:

users

Menyimpan data admin dan siswa.

id
nama
username
email
password
kelas
foto
role
status
profil_lengkap
created_at

Role:

admin
siswa

buku

Menyimpan data buku, kelas, stok total, dan stok tersedia.

peminjaman

Menyimpan data peminjaman siswa dan status peminjaman.

Status:

menunggu
disetujui
dipinjam
dikembalikan

aktivitas

Menyimpan riwayat aktivitas pengguna.

Cara Instalasi

1. Install Laragon

Jalankan Apache dan MySQL di Laragon.

2. Masukkan Project

Letakkan folder project di:

C:\laragon\www\perpustakaan_smk

3. Buat Database

Buka phpMyAdmin melalui Laragon, lalu buat database:

perpustakaan_smk

Import file .sql database project ke database tersebut.

4. Atur Koneksi

Buka:

config/database.php

Pastikan konfigurasi sesuai MySQL lokal, misalnya:

$host = 'localhost';
$dbname = 'perpustakaan_smk';
$username = 'root';
$password = '';

5. Jalankan

Buka:

http://localhost/perpustakaan_smk/

Alur Aplikasi

Siswa

Login
 ↓
Dashboard Siswa
 ↓
Katalog Buku
 ↓
Pilih Buku
 ↓
Ajukan Peminjaman
 ↓
Menunggu
 ↓
Disetujui
 ↓
Dipinjam
 ↓
Dikembalikan

Admin

Login
 ↓
Dashboard Admin
 ↓
Data Siswa
 ↓
Profil Siswa
 ↓
Data Buku
 ↓
Data Peminjaman
 ↓
Pengelolaan Perpustakaan

Profil Siswa

Foto profil disimpan di:

uploads/profile/

Siswa dapat:

Mengubah nama

Mengubah email

Mengubah kelas

Mengunggah foto

Mengganti foto

Menghapus foto

Format foto yang didukung:

JPG
PNG
WEBP

Ukuran maksimal foto:

2 MB

Keamanan Dasar

Aplikasi menggunakan:

PDO dan prepared statement

Session login

Pemeriksaan role

Validasi input

Validasi email

Validasi upload foto

Pembatasan ukuran file

Pemeriksaan MIME type

Nama file foto unik

Hak Akses

Fitur

Admin

Siswa

Login

✓

✓

Registrasi

-

✓

Dashboard

✓

✓

Data Siswa

✓

-

Profil Siswa

✓

✓

Hapus Siswa

✓

-

Katalog Buku

✓

✓

Peminjaman

✓

✓

Kelola Peminjaman

✓

-

Edit Profil

-

✓

Foto Profil

-

✓

Ubah Password

✓

✓

Tujuan Project

Project ini dibuat sebagai aplikasi pembelajaran sekaligus project Praktik Kerja Lapangan (PKL) untuk menerapkan pemrograman PHP, database MySQL, CRUD, autentikasi, session, upload file, dan pengelolaan sistem perpustakaan.

Author

Ridho Kurniawan

Project: Perpustakaan SMK

Jangan mengunggah data pribadi siswa, password database, atau kredensial rahasia ke repository GitHub publik.
