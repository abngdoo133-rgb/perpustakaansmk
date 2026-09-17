<?php

require '../config/database.php';
require '../includes/auth.php';

require_role('siswa');

$page_title = 'Edit Peminjaman';
$base = '../';

$id = (int)($_GET['id'] ?? 0);
$user_id = (int)($_SESSION['user']['id'] ?? 0);

if ($id <= 0 || $user_id <= 0) {
    header('Location: riwayat.php?error=Data peminjaman tidak ditemukan.');
    exit;
}


/* =========================
   AMBIL DATA PEMINJAMAN
========================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM peminjaman
    WHERE id = ?
    AND user_id = ?
    AND status = 'disetujui'
    LIMIT 1
");

$stmt->execute([
    $id,
    $user_id
]);

$peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================
   DATA TIDAK DITEMUKAN
========================= */

if (!$peminjaman) {

    header(
        'Location: riwayat.php?error=' .
        urlencode(
            'Peminjaman tidak dapat diedit.'
        )
    );

    exit;
}


include '../includes/header.php';
include '../includes/sidebar.php';

?>

<main>

    <?php include '../includes/topbar.php'; ?>


    <div class="edit-container">

        <div class="edit-card">


            <!-- =========================
                 HEADER
            ========================== -->

            <div class="edit-header">

                <div>

                    <span class="edit-label">
                        PEMINJAMAN
                    </span>

                    <h2>
                        Edit Peminjaman
                    </h2>

                    <p>
                        Ubah jumlah buku atau tanggal pengembalian.
                    </p>

                </div>


                <a
                    href="riwayat.php"
                    class="btn-kembali"
                >
                    Kembali
                </a>

            </div>


            <!-- =========================
                 FORM
            ========================== -->

            <form
                action="../proses/edit_peminjaman.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$peminjaman['id'] ?>"
                >


                <!-- NAMA -->

                <div class="form-group">

                    <label>
                        Nama Siswa
                    </label>

                    <input
                        type="text"
                        value="<?= e($peminjaman['nama_siswa']) ?>"
                        readonly
                    >

                </div>


                <!-- KELAS -->

                <div class="form-group">

                    <label>
                        Kelas
                    </label>

                    <input
                        type="text"
                        value="<?= e($peminjaman['kelas']) ?>"
                        readonly
                    >

                </div>


                <!-- BUKU -->

                <div class="form-group">

                    <label>
                        Nama Buku
                    </label>

                    <input
                        type="text"
                        value="<?= e($peminjaman['nama_buku']) ?>"
                        readonly
                    >

                </div>


                <!-- JUMLAH -->

                <div class="form-group">

                    <label for="jumlah">
                        Jumlah Buku
                    </label>

                    <input
                        type="number"
                        id="jumlah"
                        name="jumlah"
                        min="1"
                        max="50"
                        value="<?= (int)$peminjaman['jumlah'] ?>"
                        required
                    >

                    <small>
                        Maksimal 50 buku.
                        Sistem akan menyesuaikan stok secara otomatis.
                    </small>

                </div>


                <!-- TANGGAL -->

                <div class="form-group">

                    <label for="tanggal_rencana_kembali">
                        Tanggal Pengembalian
                    </label>

                    <input
                        type="date"
                        id="tanggal_rencana_kembali"
                        name="tanggal_rencana_kembali"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                        value="<?= e($peminjaman['tanggal_rencana_kembali']) ?>"
                        required
                    >

                </div>


                <!-- TOMBOL -->

                <div class="form-actions">

                    <a
                        href="riwayat.php"
                        class="btn-batal"
                    >
                        Batal
                    </a>


                    <button
                        type="submit"
                        class="btn-simpan"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</main>


<style>

/* =========================
   CONTAINER
========================= */

.edit-container {

    max-width: 850px;

    margin: 25px auto;

    padding-bottom: 40px;

}


/* =========================
   CARD
========================= */

.edit-card {

    background: white;

    border: 1px solid #e2e8f0;

    border-radius: 20px;

    padding: 28px;

    box-shadow:
        0 10px 30px rgba(15, 23, 42, 0.06);

}


/* =========================
   HEADER
========================= */

.edit-header {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 25px;

}


.edit-label {

    color: #1976d2;

    font-size: 10px;

    font-weight: 800;

    letter-spacing: 1.3px;

}


.edit-header h2 {

    margin: 6px 0 5px;

    color: #172033;

    font-size: 24px;

}


.edit-header p {

    margin: 0;

    color: #64748b;

    font-size: 13px;

}


/* =========================
   KEMBALI
========================= */

.btn-kembali {

    padding: 9px 14px;

    border-radius: 9px;

    background: #f8fafc;

    color: #475569;

    border: 1px solid #e2e8f0;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

}


.btn-kembali:hover {

    background: #f1f5f9;

}


/* =========================
   FORM
========================= */

.form-group {

    display: flex;

    flex-direction: column;

    margin-bottom: 18px;

}


.form-group label {

    margin-bottom: 8px;

    color: #172033;

    font-size: 13px;

    font-weight: 700;

}


.form-group input {

    width: 100%;

    box-sizing: border-box;

    padding: 13px 14px;

    border: 1px solid #cbd5e1;

    border-radius: 11px;

    outline: none;

    font-size: 14px;

}


.form-group input:focus {

    border-color: #0f766e;

    box-shadow:
        0 0 0 3px rgba(15, 118, 110, 0.10);

}


.form-group input[readonly] {

    background: #f8fafc;

    color: #64748b;

    cursor: not-allowed;

}


.form-group small {

    margin-top: 6px;

    color: #94a3b8;

    font-size: 11px;

}


/* =========================
   ACTION
========================= */

.form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 10px;

    margin-top: 25px;

}


/* =========================
   BATAL
========================= */

.btn-batal {

    padding: 11px 18px;

    border-radius: 10px;

    background: #f8fafc;

    border: 1px solid #cbd5e1;

    color: #475569;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}


/* =========================
   SIMPAN
========================= */

.btn-simpan {

    padding: 11px 20px;

    border: none;

    border-radius: 10px;

    background: #0f766e;

    color: white;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

}


.btn-simpan:hover {

    background: #115e59;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 600px) {

    .edit-container {

        margin: 15px auto;

        padding: 0 15px 30px;

    }


    .edit-card {

        padding: 20px;

    }


    .edit-header {

        flex-direction: column;

    }


    .form-actions {

        flex-direction: column-reverse;

        align-items: stretch;

    }


    .btn-batal,
    .btn-simpan {

        text-align: center;

    }

}

</style>


<?php include '../includes/footer.php'; ?>