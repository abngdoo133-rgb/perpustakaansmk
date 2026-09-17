<?php

require_once '../config/database.php';
require_once '../includes/auth.php';

require_role('siswa');

date_default_timezone_set('Asia/Jakarta');

$page_title = 'Pinjam Buku';
$base = '../';

$userId = (int)($_SESSION['user']['id'] ?? 0);

/* =========================================================
   1. DATA PROFIL SISWA
   ========================================================= */
$stmtProfil = $pdo->prepare("
    SELECT id, nama, username, email, kelas, foto, profil_lengkap
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmtProfil->execute([$userId]);
$profil = $stmtProfil->fetch(PDO::FETCH_ASSOC);

$isProfileComplete = (
    $profil &&
    (int)($profil['profil_lengkap'] ?? 0) === 1 &&
    trim($profil['nama'] ?? '') !== '' &&
    trim($profil['kelas'] ?? '') !== ''
);

$namaSiswa = trim($profil['nama'] ?? 'Siswa');
$kelasSiswa = strtoupper(trim($profil['kelas'] ?? ''));

/* =========================================================
   2. FUNGSI TINGKAT KELAS & KELAYAKAN
   ========================================================= */
function tingkatKelas($k) {
    $k = strtoupper(trim($k));
    if (str_starts_with($k, 'XII')) return 'XII';
    if (str_starts_with($k, 'XI')) return 'XI';
    if (str_starts_with($k, 'X')) return 'X';
    return '';
}

function bukuBolehDipinjam($kSiswa, $kBuku) {
    $kSiswa = strtoupper(trim($kSiswa));
    $kBuku = strtoupper(trim($kBuku));

    $tingkatSiswa = tingkatKelas($kSiswa);
    $tingkatBuku = tingkatKelas($kBuku);

    if ($tingkatSiswa === '' || $tingkatBuku === '' || $tingkatSiswa !== $tingkatBuku) {
        return false;
    }
    if ($kBuku === $tingkatBuku) return true;
    return $kBuku === $kSiswa;
}

/* =========================================================
   3. FUNGSI FOTO COVER BUKU
   ========================================================= */
function cariCoverBuku($foto) {
    if (empty($foto)) return '';
    $nama = basename(trim($foto));
    if ($nama === '') return '';

    $paths = [
        __DIR__ . '/../uploads/books/' . $nama => '../uploads/books/' . rawurlencode($nama),
        __DIR__ . '/../uploads/buku/' . $nama  => '../uploads/buku/' . rawurlencode($nama),
    ];

    foreach ($paths as $file => $url) {
        if (is_file($file)) {
            return $url . '?v=' . filemtime($file);
        }
    }
    return '';
}

/* =========================================================
   4. BUKU YANG DIPILIH
   ========================================================= */
$bukuId = (int)($_GET['buku_id'] ?? 0);
$bukuDipilih = null;
$errorBuku = '';

if ($bukuId > 0) {
    $stmtBuku = $pdo->prepare("SELECT * FROM buku WHERE id = ? LIMIT 1");
    $stmtBuku->execute([$bukuId]);
    $bukuDipilih = $stmtBuku->fetch(PDO::FETCH_ASSOC);

    if (!$bukuDipilih) {
        $errorBuku = 'Buku tidak ditemukan.';
    } elseif ((int)$bukuDipilih['stok_tersedia'] <= 0) {
        $errorBuku = 'Maaf, stok buku "' . $bukuDipilih['nama_buku'] . '" sedang habis.';
        $bukuDipilih = null;
    } elseif (!bukuBolehDipinjam($kelasSiswa, $bukuDipilih['kelas'])) {
        $errorBuku = 'Buku ini diperuntukkan untuk kelas ' . $bukuDipilih['kelas'] . ', sedangkan kelas Anda adalah ' . $kelasSiswa . '.';
        $bukuDipilih = null;
    }
}

/* =========================================================
   5. DAFTAR BUKU UNTUK PEMILIH INTERAKTIF
   ========================================================= */
$tingkatUser = tingkatKelas($kelasSiswa);
$stmtKatalog = $pdo->prepare("
    SELECT *
    FROM buku
    WHERE stok_tersedia > 0
      AND (
          kelas = ?
          OR kelas = ?
          OR kelas LIKE ?
      )
    ORDER BY nama_buku ASC
");
$stmtKatalog->execute([
    $tingkatUser,
    $kelasSiswa,
    $tingkatUser . ' %'
]);
$daftarBuku = $stmtKatalog->fetchAll(PDO::FETCH_ASSOC);

$tanggalHariIni = date('Y-m-d');
$jamSekarang = date('H:i');
$tanggalDefaultKembali = date('Y-m-d', strtotime('+7 days'));
$jamDefaultKembali = '15:00';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? $errorBuku;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <!-- ALERT PESAN -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <div>
                <strong>Peminjaman Berhasil!</strong>
                <p><?= e($success) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <span class="alert-icon">!</span>
            <div>
                <strong>Perhatian</strong>
                <p><?= e($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$isProfileComplete): ?>
        <!-- PERINGATAN PROFIL BELUM LENGKAP -->
        <div class="card" style="border-left: 4px solid var(--amber-500);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-bottom: 4px;">
                        ⚠️ Profil Siswa Belum Lengkap
                    </h3>
                    <p style="font-size: 13px; color: var(--slate-600);">
                        Harap lengkapi nama lengkap, kelas, dan email pada halaman profil sebelum melakukan peminjaman buku.
                    </p>
                </div>
                <a href="profil.php" class="btn btn-primary">
                    Lengkapi Profil Sekarang
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- LAYOUT UTAMA PEMINJAMAN -->
        <div class="loan-layout">

            <!-- KOLOM KIRI: FORM & SELEKSI BUKU -->
            <div class="loan-form-section">

                <!-- 1. BUKU TERPILIH ATAU PEMILIH BUKU -->
                <?php if ($bukuDipilih): ?>
                    <?php 
                    $coverUrl = cariCoverBuku($bukuDipilih['foto'] ?? '');
                    ?>
                    <div class="selected-book-spotlight">
                        <?php if ($coverUrl !== ''): ?>
                            <img src="<?= e($coverUrl) ?>" alt="<?= e($bukuDipilih['nama_buku']) ?>" class="spotlight-cover">
                        <?php else: ?>
                            <div class="book-card-fallback" style="width: 100px; height: 145px; padding: 12px 8px;">
                                <div class="fallback-title" style="font-size: 11px;"><?= e($bukuDipilih['nama_buku']) ?></div>
                                <div class="fallback-meta" style="font-size: 9px;">Perpus SMK</div>
                            </div>
                        <?php endif; ?>

                        <div class="spotlight-info">
                            <span class="book-class-tag match">
                                ✓ Sesuai Kelas <?= e($bukuDipilih['kelas']) ?>
                            </span>
                            <h3><?= e($bukuDipilih['nama_buku']) ?></h3>
                            <div class="spotlight-meta">
                                <div>
                                    <span>Pengarang</span>
                                    <strong><?= e($bukuDipilih['pengarang'] ?: '-') ?></strong>
                                </div>
                                <div>
                                    <span>Penerbit</span>
                                    <strong><?= e($bukuDipilih['penerbit'] ?: '-') ?></strong>
                                </div>
                                <div>
                                    <span>Lokasi Rak</span>
                                    <strong><?= e($bukuDipilih['rak'] ?: 'Rak Utama') ?> <?= e($bukuDipilih['nomor_rak'] ? '(' . $bukuDipilih['nomor_rak'] . ')' : '') ?></strong>
                                </div>
                                <div>
                                    <span>Stok Tersedia</span>
                                    <strong style="color: var(--emerald-600);"><?= (int)$bukuDipilih['stok_tersedia'] ?> buku</strong>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary btn-sm btn-change-book" onclick="openBookModal()">
                            Ganti Buku
                        </button>
                    </div>

                <?php else: ?>
                    <!-- KARTU AJAKAN PILIH BUKU -->
                    <div class="card" style="text-align: center; padding: 36px 24px; border: 2px dashed var(--primary-200); background: #f8faff;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--primary-100); color: var(--primary-600); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                                <path d="M12 9v6m-3-3h6"/>
                            </svg>
                        </div>
                        <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-bottom: 6px;">
                            Pilih Buku yang Ingin Kamu Pinjam
                        </h3>
                        <p style="font-size: 13.5px; color: var(--slate-500); max-width: 450px; margin: 0 auto 20px;">
                            Kamu bisa memilih buku yang sesuai dengan tingkat kelas <strong><?= e($kelasSiswa) ?></strong> langsung di sini atau menjelajah katalog lengkap.
                        </p>
                        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                            <button type="button" class="btn btn-primary btn-lg" onclick="openBookModal()">
                                🔍 Pilih Buku dari Daftar
                            </button>
                            <a href="buku.php" class="btn btn-secondary btn-lg">
                                Buka Katalog Lengkap
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($bukuDipilih): ?>
                <!-- 2. FORMULIR PEMINJAMAN -->
                <form action="../proses/peminjaman.php" method="POST" id="loanForm" class="card">
                    <input type="hidden" name="buku_id" id="formBukuId" value="<?= (int)$bukuDipilih['id'] ?>">
                    <input type="hidden" name="nama_buku" value="<?= e($bukuDipilih['nama_buku']) ?>">
                    <input type="hidden" name="kelas" value="<?= e($kelasSiswa) ?>">

                    <div class="card-header">
                        <div>
                            <h3 class="card-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                                    <line x1="16" x2="16" y1="2" y2="6"/>
                                    <line x1="8" x2="8" y1="2" y2="6"/>
                                </svg>
                                Atur Durasi & Waktu Pengembalian
                            </h3>
                            <p class="card-subtitle">Pilih durasi peminjaman cepat atau sesuaikan sendiri.</p>
                        </div>
                    </div>

                    <!-- PILIHAN DURASI CEPAT -->
                    <div class="form-group">
                        <label>Pilihan Durasi Peminjaman</label>
                        <div class="duration-chips">
                            <div class="duration-chip" onclick="selectDuration(3, this)">
                                <strong>3 Hari</strong>
                                <span>Tugas / PR</span>
                            </div>
                            <div class="duration-chip active" onclick="selectDuration(7, this)">
                                <strong>7 Hari</strong>
                                <span>Standar (1 Mgg)</span>
                            </div>
                            <div class="duration-chip" onclick="selectDuration(14, this)">
                                <strong>14 Hari</strong>
                                <span>2 Minggu</span>
                            </div>
                            <div class="duration-chip" onclick="selectCustomDuration(this)">
                                <strong>Kustom</strong>
                                <span>Atur Sendiri</span>
                            </div>
                        </div>
                    </div>

                    <!-- TANGGAL & WAKTU -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tglPinjam">Tanggal Mulai Pinjam</label>
                            <input type="date" class="form-control" id="tglPinjam" name="tanggal_pinjam" value="<?= $tanggalHariIni ?>" min="<?= $tanggalHariIni ?>" required onchange="onDateChange()">
                        </div>

                        <div class="form-group">
                            <label for="waktuMulai">Jam Mulai</label>
                            <input type="time" class="form-control" id="waktuMulai" name="waktu_mulai" value="<?= $jamSekarang ?>" required onchange="updateSummary()">
                        </div>

                        <div class="form-group">
                            <label for="tglKembali">Tanggal Batas Pengembalian</label>
                            <input type="date" class="form-control" id="tglKembali" name="tanggal_rencana_kembali" value="<?= $tanggalDefaultKembali ?>" min="<?= $tanggalHariIni ?>" required onchange="onCustomDateChange()">
                        </div>

                        <div class="form-group">
                            <label for="waktuKembali">Jam Batas Kembali</label>
                            <input type="time" class="form-control" id="waktuKembali" name="waktu_rencana_kembali" value="<?= $jamDefaultKembali ?>" required onchange="updateSummary()">
                        </div>
                    </div>

                    <!-- JUMLAH BUKU & CATATAN -->
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="jumlahBuku">Jumlah Buku Dipinjam</label>
                            <select class="form-control" id="jumlahBuku" name="jumlah" onchange="updateSummary()">
                                <option value="1" selected>1 Eksemplar</option>
                                <?php if ((int)$bukuDipilih['stok_tersedia'] >= 2): ?>
                                    <option value="2">2 Eksemplar (Maksimal)</option>
                                <?php endif; ?>
                            </select>
                            <small class="helper">Siswa diperbolehkan meminjam maksimal 2 buku per transaksi.</small>
                        </div>

                        <div class="form-group">
                            <label for="catatanPinjam">Keperluan / Catatan (Opsional)</label>
                            <input type="text" class="form-control" id="catatanPinjam" name="catatan" placeholder="Contoh: Tugas Bahasa Indonesia Bab 3" oninput="updateSummary()">
                        </div>
                    </div>
                </form>
                <?php endif; ?>

            </div>

            <!-- KOLOM KANAN: TIKET RINGKASAN PEMINJAMAN -->
            <?php if ($bukuDipilih): ?>
            <div class="loan-summary-sidebar">
                <div class="loan-ticket-card">
                    <div class="ticket-header">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">
                            Pratinjau Peminjaman
                        </span>
                        <h3>Tiket Digital Perpustakaan</h3>
                        <p>SMK Digital Library System</p>
                    </div>

                    <div class="ticket-body">
                        <div class="ticket-row">
                            <span>Peminjam</span>
                            <strong><?= e($namaSiswa) ?> (<?= e($kelasSiswa) ?>)</strong>
                        </div>

                        <div class="ticket-row">
                            <span>Buku Dipilih</span>
                            <strong id="sumJudulBuku"><?= e($bukuDipilih['nama_buku']) ?></strong>
                        </div>

                        <div class="ticket-row">
                            <span>Jumlah</span>
                            <strong id="sumJumlah">1 Eksemplar</strong>
                        </div>

                        <div class="ticket-row">
                            <span>Lokasi Rak</span>
                            <strong><?= e($bukuDipilih['rak'] ?: 'Rak Koleksi') ?></strong>
                        </div>

                        <div class="ticket-row">
                            <span>Mulai Pinjam</span>
                            <strong id="sumMulaiPinjam"><?= date('d/m/Y', strtotime($tanggalHariIni)) ?> (<?= $jamSekarang ?> WIB)</strong>
                        </div>

                        <!-- KOTAK BATAS WAKTU -->
                        <div class="ticket-deadline-box">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <div>
                                <strong id="sumBatasTgl"><?= date('d M Y', strtotime($tanggalDefaultKembali)) ?>, 15:00 WIB</strong>
                                <small id="sumCountdown">Batas durasi peminjaman: 7 Hari</small>
                            </div>
                        </div>

                        <!-- CHECKBOX PERSETUJUAN -->
                        <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 12px; color: var(--slate-600); cursor: pointer; margin-top: 4px;">
                            <input type="checkbox" id="agreeTerms" checked style="margin-top: 2px; width: 16px; height: 16px; accent-color: var(--primary-600);">
                            <span>Saya bersedia merawat buku ini dengan baik dan mengembalikannya sebelum batas waktu.</span>
                        </label>

                        <!-- TOMBOL SUBMIT -->
                        <button type="button" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;" onclick="submitLoanForm()">
                            ✓ Ajukan Peminjaman Sekarang
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <!-- MODAL PEMILIH BUKU INTERAKTIF -->
    <div class="modal-backdrop" id="bookModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <div>
                    <h3>Pilih Buku Koleksi</h3>
                    <p style="font-size: 12px; color: var(--slate-500);">Buku yang tersedia untuk kelas <strong><?= e($kelasSiswa) ?></strong></p>
                </div>
                <button type="button" class="modal-close" onclick="closeBookModal()">&times;</button>
            </div>

            <div class="modal-body">
                <!-- SEARCH DALAM MODAL -->
                <div class="search-box" style="margin-bottom: 16px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" class="form-control" id="modalSearchInput" placeholder="Cari judul buku, pengarang, penerbit..." oninput="filterBookList()">
                </div>

                <div id="modalBookList" style="display: flex; flex-direction: column; gap: 10px; max-height: 420px; overflow-y: auto; padding-right: 4px;">
                    <?php if (!empty($daftarBuku)): ?>
                        <?php foreach ($daftarBuku as $b): ?>
                            <?php 
                            $coverThumb = cariCoverBuku($b['foto'] ?? '');
                            ?>
                            <div class="card book-item-row" style="margin-bottom: 0; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 14px; transition: background 0.2s;" data-title="<?= strtolower(e($b['nama_buku'] . ' ' . $b['pengarang'] . ' ' . $b['penerbit'])) ?>">
                                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                    <?php if ($coverThumb !== ''): ?>
                                        <img src="<?= e($coverThumb) ?>" alt="" style="width: 38px; height: 52px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div style="width: 38px; height: 52px; border-radius: 4px; background: var(--primary-700); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; flex-shrink: 0; text-align: center; padding: 2px;">
                                            Buku
                                        </div>
                                    <?php endif; ?>
                                    <div style="min-width: 0;">
                                        <strong style="display: block; font-size: 13.5px; color: var(--slate-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= e($b['nama_buku']) ?>
                                        </strong>
                                        <small style="color: var(--slate-500); font-size: 11.5px;">
                                            <?= e($b['pengarang'] ?: 'Pengarang Umum') ?> &bull; Kelas <?= e($b['kelas']) ?> &bull; Stok: <span style="color: var(--emerald-600); font-weight: 700;"><?= (int)$b['stok_tersedia'] ?></span>
                                        </small>
                                    </div>
                                </div>
                                <a href="pinjam.php?buku_id=<?= (int)$b['id'] ?>" class="btn btn-primary btn-sm" style="flex-shrink: 0;">
                                    Pilih Buku Ini
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px; color: var(--slate-400);">
                            Belum ada buku yang tersedia untuk kelas Anda saat ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let activeDays = 7;

function openBookModal() {
    const modal = document.getElementById('bookModal');
    if (modal) modal.classList.add('show');
}

function closeBookModal() {
    const modal = document.getElementById('bookModal');
    if (modal) modal.classList.remove('show');
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('bookModal');
    if (e.target === modal) closeBookModal();
});

function filterBookList() {
    const q = document.getElementById('modalSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.book-item-row');
    rows.forEach(row => {
        const text = row.getAttribute('data-title') || '';
        row.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function selectDuration(days, el) {
    activeDays = days;
    document.querySelectorAll('.duration-chip').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');

    const tglPinjamVal = document.getElementById('tglPinjam').value;
    if (tglPinjamVal) {
        const d = new Date(tglPinjamVal);
        d.setDate(d.getDate() + days);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('tglKembali').value = `${yyyy}-${mm}-${dd}`;
    }
    updateSummary();
}

function selectCustomDuration(el) {
    activeDays = 'custom';
    document.querySelectorAll('.duration-chip').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
    updateSummary();
}

function onCustomDateChange() {
    document.querySelectorAll('.duration-chip').forEach(c => c.classList.remove('active'));
    const customChip = document.querySelectorAll('.duration-chip')[3];
    if (customChip) customChip.classList.add('active');
    updateSummary();
}

function onDateChange() {
    if (typeof activeDays === 'number') {
        selectDuration(activeDays, document.querySelectorAll('.duration-chip')[activeDays === 3 ? 0 : (activeDays === 7 ? 1 : 2)]);
    } else {
        updateSummary();
    }
}

function updateSummary() {
    const tglPinjam = document.getElementById('tglPinjam');
    const waktuMulai = document.getElementById('waktuMulai');
    const tglKembali = document.getElementById('tglKembali');
    const waktuKembali = document.getElementById('waktuKembali');
    const jml = document.getElementById('jumlahBuku');

    if (!tglPinjam || !tglKembali) return;

    if (tglKembali.value < tglPinjam.value) {
        tglKembali.value = tglPinjam.value;
    }

    const dPinjam = new Date(tglPinjam.value);
    const dKembali = new Date(tglKembali.value);

    // Format tanggal Indonesia
    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const formattedKembali = `${dKembali.getDate()} ${bulan[dKembali.getMonth()]} ${dKembali.getFullYear()}`;
    const formattedPinjam = `${String(dPinjam.getDate()).padStart(2,'0')}/${String(dPinjam.getMonth()+1).padStart(2,'0')}/${dPinjam.getFullYear()}`;

    const sumMulai = document.getElementById('sumMulaiPinjam');
    const sumBatas = document.getElementById('sumBatasTgl');
    const sumCountdown = document.getElementById('sumCountdown');
    const sumJml = document.getElementById('sumJumlah');

    if (sumMulai) sumMulai.textContent = `${formattedPinjam} (${waktuMulai.value} WIB)`;
    if (sumBatas) sumBatas.textContent = `${formattedKembali}, ${waktuKembali.value} WIB`;

    const diffDays = Math.round((dKembali - dPinjam) / (1000 * 60 * 60 * 24));
    if (sumCountdown) {
        if (diffDays === 0) {
            sumCountdown.textContent = 'Batas durasi peminjaman: Hari ini';
        } else {
            sumCountdown.textContent = `Batas durasi peminjaman: ${diffDays} Hari`;
        }
    }

    if (sumJml && jml) sumJml.textContent = `${jml.value} Eksemplar`;
}

function submitLoanForm() {
    const agree = document.getElementById('agreeTerms');
    if (agree && !agree.checked) {
        alert('Harap setujui pernyataan komitmen merawat buku sebelum meminjam.');
        agree.focus();
        return;
    }

    const form = document.getElementById('loanForm');
    if (form) form.submit();
}

document.addEventListener('DOMContentLoaded', updateSummary);
</script>

<?php include '../includes/footer.php'; ?>