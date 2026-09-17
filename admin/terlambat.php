<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('admin');

$page_title = 'Peminjaman Terlambat';
$base = '../';

date_default_timezone_set('Asia/Jakarta');
$sekarang = time();

$stmt = $pdo->query("
    SELECT p.*, u.foto, u.email
    FROM peminjaman p
    LEFT JOIN users u ON u.id = p.user_id
    WHERE p.status IN ('disetujui', 'dipinjam')
    ORDER BY p.tanggal_rencana_kembali ASC
");
$semua = $stmt->fetchAll(PDO::FETCH_ASSOC);

$terlambat = [];
foreach ($semua as $r) {
    $waktuKembali = !empty($r['waktu_rencana_kembali']) ? $r['waktu_rencana_kembali'] : '23:59:59';
    $batas = strtotime($r['tanggal_rencana_kembali'] . ' ' . $waktuKembali);
    if ($batas < $sekarang) {
        $r['batas_timestamp'] = $batas;
        $r['selisih_hari'] = ceil(($sekarang - $batas) / 86400);
        $terlambat[] = $r;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <div class="card" style="border-left: 4px solid var(--rose-600);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
                <span class="badge badge-terlambat" style="margin-bottom: 6px;">
                    ⚠️ Perhatian Khusus
                </span>
                <h2 style="font-size: 20px; font-weight: 800; color: var(--slate-900);">
                    Monitoring Peminjaman Terlambat
                </h2>
                <p style="font-size: 13px; color: var(--slate-600); margin-top: 4px;">
                    Daftar peminjaman yang telah melampaui batas waktu pengembalian buku yang disepakati.
                </p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Total Terlambat</span>
                <div style="font-size: 32px; font-weight: 800; color: var(--rose-600); line-height: 1;">
                    <?= count($terlambat) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Daftar Siswa dengan Status Terlambat</h3>
                <p class="card-subtitle">Hubungi siswa atau tandai buku sebagai sudah dikembalikan setelah diterima.</p>
            </div>
            <a href="peminjaman.php" class="btn btn-secondary btn-sm">
                &larr; Kembali ke Semua Peminjaman
            </a>
        </div>

        <?php if (!empty($terlambat)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Buku Dipinjam</th>
                            <th>Batas Kembali</th>
                            <th>Keterlambatan</th>
                            <th>Tindakan Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terlambat as $index => $row): ?>
                            <tr class="baris-terlambat">
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="user-cell">
                                        <img src="<?= !empty($row['foto']) ? '../uploads/profile/' . e($row['foto']) : '../assets/images/avatar.svg' ?>" class="user-cell-avatar" alt="">
                                        <div class="user-cell-meta">
                                            <strong><?= e($row['nama_siswa']) ?></strong>
                                            <small><?= e($row['email'] ?: 'Tanpa email') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: var(--slate-100); color: var(--slate-800);">
                                        <?= e($row['kelas']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= e($row['nama_buku']) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-500);"><?= (int)$row['jumlah'] ?> eksemplar</small>
                                </td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($row['tanggal_rencana_kembali'])) ?></strong>
                                    <br>
                                    <small style="color: var(--slate-400);"><?= e(substr($row['waktu_rencana_kembali'] ?? '15:00', 0, 5)) ?> WIB</small>
                                </td>
                                <td>
                                    <span class="badge badge-terlambat">
                                        Lewat <?= $row['selisih_hari'] ?> Hari
                                    </span>
                                </td>
                                <td>
                                    <form method="post" action="../proses/status_peminjaman.php" style="display: inline-block;">
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <input type="hidden" name="status" value="dikembalikan">
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Tandai buku ini sudah diterima kembali dari <?= e($row['nama_siswa']) ?>?')" title="Buku Telah Dikembalikan">
                                            📥 Tandai Dikembalikan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 48px 20px; color: var(--slate-500);">
                <div style="font-size: 36px; margin-bottom: 8px;">🎉</div>
                <strong style="display: block; font-size: 16px; color: var(--slate-800); margin-bottom: 4px;">Tidak Ada Peminjaman Terlambat</strong>
                <p style="font-size: 13px;">Semua siswa mengembalikan buku tepat waktu!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>