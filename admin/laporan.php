<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Laporan Perpustakaan';
$base = '../';

// Data statistik peminjaman
$stats = $pdo->query("
    SELECT
        status,
        COUNT(*) AS total,
        SUM(jumlah) AS jumlah
    FROM peminjaman
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Top 10 Buku Terpopuler
$popular = $pdo->query("
    SELECT
        nama_buku,
        COUNT(*) AS pengajuan,
        SUM(jumlah) AS total_buku
    FROM peminjaman
    GROUP BY nama_buku
    ORDER BY pengajuan DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$totalPengajuan = 0;
$totalBuku = 0;
$dikembalikanCount = 0;

foreach ($stats as $r) {
    $totalPengajuan += (int)$r['total'];
    $totalBuku += (int)$r['jumlah'];
    if ($r['status'] === 'dikembalikan') {
        $dikembalikanCount += (int)$r['total'];
    }
}

$completionRate = $totalPengajuan > 0 ? round(($dikembalikanCount / $totalPengajuan) * 100, 1) : 100;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
                <span class="badge" style="background: var(--primary-50); color: var(--primary-700); margin-bottom: 6px;">
                    📊 Laporan Eksekutif
                </span>
                <h2 style="font-size: 20px; font-weight: 800; color: var(--slate-900);">
                    Laporan Statistik Sirkulasi Perpustakaan
                </h2>
                <p style="font-size: 13px; color: var(--slate-500); margin-top: 2px;">
                    Analisis data peminjaman, buku terfavorit, dan tingkat penyelesaian peminjaman siswa.
                </p>
            </div>
            <button type="button" class="btn btn-secondary" onclick="window.print()">
                🖨️ Cetak Laporan
            </button>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="stat-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" x2="16" y1="2" y2="6"/>
                    <line x1="8" x2="8" y1="2" y2="6"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Transaksi</span>
                <strong class="stat-value"><?= number_format($totalPengajuan) ?></strong>
                <span class="stat-desc">Seluruh pengajuan pinjam</span>
            </div>
        </div>

        <div class="stat-card emerald">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Eksemplar</span>
                <strong class="stat-value"><?= number_format($totalBuku) ?></strong>
                <span class="stat-desc">Buku keluar masuk</span>
            </div>
        </div>

        <div class="stat-card indigo">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Tingkat Pengembalian</span>
                <strong class="stat-value"><?= $completionRate ?>%</strong>
                <span class="stat-desc"><?= $dikembalikanCount ?> transaksi tuntas</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <!-- BREAKDOWN STATUS -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Distribusi Status Peminjaman</h3>
                    <p class="card-subtitle">Perincian status sirkulasi saat ini.</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ($stats as $s): ?>
                    <?php
                    $pct = $totalPengajuan > 0 ? round(((int)$s['total'] / $totalPengajuan) * 100, 1) : 0;
                    $color = match($s['status']) {
                        'dikembalikan' => 'var(--emerald-500)',
                        'dipinjam', 'disetujui' => 'var(--primary-600)',
                        'menunggu' => 'var(--amber-500)',
                        default => 'var(--slate-400)'
                    };
                    ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 4px;">
                            <span style="text-transform: capitalize;"><?= e($s['status']) ?></span>
                            <span style="color: var(--slate-600);"><?= (int)$s['total'] ?> transaksi (<?= $pct ?>%)</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: var(--slate-100); border-radius: 4px; overflow: hidden;">
                            <div style="width: <?= $pct ?>%; height: 100%; background: <?= $color ?>; border-radius: 4px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TOP 10 BUKU FAVORIT -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Top 10 Buku Paling Sering Dipinjam</h3>
                    <p class="card-subtitle">Koleksi terpopuler berdasarkan frekuensi peminjaman.</p>
                </div>
            </div>

            <?php if (!empty($popular)): ?>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($popular as $index => $pop): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 12px; background: var(--slate-50); border-radius: var(--radius-md);">
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                <span style="width: 24px; height: 24px; border-radius: 50%; background: <?= $index < 3 ? 'var(--primary-600)' : 'var(--slate-200)' ?>; color: <?= $index < 3 ? 'white' : 'var(--slate-700)' ?>; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">
                                    <?= $index + 1 ?>
                                </span>
                                <strong style="font-size: 13px; color: var(--slate-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($pop['nama_buku']) ?>
                                </strong>
                            </div>
                            <span class="badge" style="background: white; border: 1px solid var(--slate-200); color: var(--primary-700); flex-shrink: 0;">
                                <?= (int)$pop['pengajuan'] ?>x Pinjam
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 24px; color: var(--slate-400);">
                    Belum ada data aktivitas buku.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>