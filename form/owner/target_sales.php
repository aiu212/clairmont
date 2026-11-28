<?php
// =====================================================
// TARGET PENJUALAN - OWNER CLAIRMONT DESSERT
// File: form/owner/target_sales.php
// PHP 7.3
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($koneksi)) {
    require_once __DIR__ . '/../../koneksi2.php';
}

mysqli_set_charset($koneksi, 'utf8mb4');

// Validasi akses Owner
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    exit('Akses ditolak.');
}

$pesan = '';
$jenisPesan = 'success';

$namaBulan = array(
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
    4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
);

$bulanFilter = isset($_GET['bulan']) ? (int) $_GET['bulan'] : (int) date('m');
$tahunFilter = isset($_GET['tahun']) ? (int) $_GET['tahun'] : (int) date('Y');

if ($bulanFilter < 1 || $bulanFilter > 12) {
    $bulanFilter = (int) date('m');
}

if ($tahunFilter < 2000 || $tahunFilter > 2100) {
    $tahunFilter = (int) date('Y');
}

function target_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function target_rupiah($value)
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

// =====================================================
// HAPUS TARGET
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_target'])) {

    $idHapus = isset($_POST['id_target']) ? (int) $_POST['id_target'] : 0;

    if ($idHapus > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM tb_target_sales WHERE id_target = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $idHapus);

            if (mysqli_stmt_execute($stmt)) {
                $pesan = 'Target penjualan berhasil dihapus.';
            } else {
                $pesan = 'Gagal menghapus target penjualan: ' . mysqli_error($koneksi);
                $jenisPesan = 'danger';
            }

            mysqli_stmt_close($stmt);

        } else {
            $pesan = 'Query hapus target tidak dapat diproses.';
            $jenisPesan = 'danger';
        }

    } else {
        $pesan = 'Data target yang akan dihapus tidak valid.';
        $jenisPesan = 'danger';
    }
}

// =====================================================
// SIMPAN / UPDATE TARGET
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_target'])) {

    $idSales = isset($_POST['id_sales']) ? (int) $_POST['id_sales'] : 0;

    $targetOmzet = trim(
        isset($_POST['target_omzet']) ? $_POST['target_omzet'] : ''
    );

    $rewardTipe = isset($_POST['reward_tipe'])
        ? $_POST['reward_tipe'] : 'nominal';

    $rewardNilaiInput = trim(
        isset($_POST['reward_nilai']) ? $_POST['reward_nilai'] : '0'
    );

    $periode = isset($_POST['periode']) ? $_POST['periode'] : '';

    $editId = isset($_POST['id_target']) ? (int) $_POST['id_target'] : 0;

    $tanggal = DateTime::createFromFormat('!Y-m', $periode);

    if (!$tanggal || $tanggal->format('Y-m') !== $periode) {

        $pesan = 'Periode target tidak valid.';
        $jenisPesan = 'danger';

    } elseif (
        $idSales <= 0 ||
        !ctype_digit($targetOmzet) ||
        (float) $targetOmzet <= 0
    ) {

        $pesan = 'Pilih Sales dan masukkan target omzet berupa angka lebih dari 0.';
        $jenisPesan = 'danger';

    } elseif (!in_array($rewardTipe, array('nominal', 'persen'), true)) {

        $pesan = 'Jenis reward tidak valid.';
        $jenisPesan = 'danger';

    } elseif (!is_numeric($rewardNilaiInput) || (float) $rewardNilaiInput < 0) {

        $pesan = 'Nilai reward harus berupa angka nol atau lebih.';
        $jenisPesan = 'danger';

    } elseif ($rewardTipe === 'persen' && (float) $rewardNilaiInput > 100) {

        $pesan = 'Insentif persentase tidak boleh lebih dari 100%.';
        $jenisPesan = 'danger';

    } else {

        $bulan = (int) $tanggal->format('m');
        $tahun = (int) $tanggal->format('Y');

        $rewardNilai = (float) $rewardNilaiInput;

        // Pastikan akun yang dipilih merupakan Sales aktif.
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_user FROM tb_users
             WHERE id_user = ?
             AND role = 'sales'
             AND status = 'aktif'"
        );

        $salesValid = false;

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $idSales);

            mysqli_stmt_execute($stmt);

            mysqli_stmt_store_result($stmt);

            $salesValid = mysqli_stmt_num_rows($stmt) > 0;

            mysqli_stmt_close($stmt);
        }

        if (!$salesValid) {

            $pesan = 'Akun Sales tidak ditemukan atau tidak aktif.';
            $jenisPesan = 'danger';

        } else {

            // Kompatibilitas kolom reward lama.
            if ($rewardTipe === 'persen') {

                $rewardLegacy = 'Insentif ' .
                    rtrim(
                        rtrim(number_format($rewardNilai, 2, '.', ''), '0'),
                        '.'
                    ) . '%';

            } else {

                $rewardLegacy = (string) round($rewardNilai);
            }

            $berhasil = false;

            if ($editId > 0) {

                // UPDATE TARGET
                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE tb_target_sales
                     SET id_sales = ?,
                         bulan = ?,
                         tahun = ?,
                         target_omzet = ?,
                         reward = ?,
                         reward_tipe = ?,
                         reward_nilai = ?
                     WHERE id_target = ?"
                );

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iiiissdi",
                        $idSales,
                        $bulan,
                        $tahun,
                        $targetOmzet,
                        $rewardLegacy,
                        $rewardTipe,
                        $rewardNilai,
                        $editId
                    );

                    $berhasil = mysqli_stmt_execute($stmt);

                    if (!$berhasil) {
                        $pesan = 'Gagal memperbarui target: ' . mysqli_error($koneksi);
                        $jenisPesan = 'danger';
                    }

                    mysqli_stmt_close($stmt);
                }

            } else {

                // CEK TARGET SALES PADA PERIODE YANG SAMA
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id_target
                     FROM tb_target_sales
                     WHERE id_sales = ?
                     AND bulan = ?
                     AND tahun = ?
                     LIMIT 1"
                );

                $idTargetLama = 0;

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iii",
                        $idSales,
                        $bulan,
                        $tahun
                    );

                    mysqli_stmt_execute($stmt);

                    mysqli_stmt_bind_result($stmt, $idTargetLama);

                    $sudahAda = mysqli_stmt_fetch($stmt);

                    mysqli_stmt_close($stmt);

                } else {
                    $sudahAda = false;
                }

                if ($sudahAda) {

                    // UPDATE JIKA TARGET SUDAH ADA
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "UPDATE tb_target_sales
                         SET target_omzet = ?,
                             reward = ?,
                             reward_tipe = ?,
                             reward_nilai = ?
                         WHERE id_target = ?"
                    );

                    if ($stmt) {

                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssdi",
                            $targetOmzet,
                            $rewardLegacy,
                            $rewardTipe,
                            $rewardNilai,
                            $idTargetLama
                        );

                        $berhasil = mysqli_stmt_execute($stmt);

                        if (!$berhasil) {
                            $pesan = 'Gagal memperbarui target: ' . mysqli_error($koneksi);
                            $jenisPesan = 'danger';
                        }

                        mysqli_stmt_close($stmt);
                    }

                } else {

                    // INSERT TARGET BARU
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO tb_target_sales
                         (
                            id_sales,
                            bulan,
                            tahun,
                            target_omzet,
                            reward,
                            reward_tipe,
                            reward_nilai
                         )
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    if ($stmt) {

                        mysqli_stmt_bind_param(
                            $stmt,
                            "iiisssd",
                            $idSales,
                            $bulan,
                            $tahun,
                            $targetOmzet,
                            $rewardLegacy,
                            $rewardTipe,
                            $rewardNilai
                        );

                        $berhasil = mysqli_stmt_execute($stmt);

                        if (!$berhasil) {
                            $pesan = 'Gagal menyimpan target: ' . mysqli_error($koneksi);
                            $jenisPesan = 'danger';
                        }

                        mysqli_stmt_close($stmt);
                    }
                }
            }

            if ($berhasil) {

                $urlKembali = 'dashboard_owner.php?menu=target_sales&bulan=' .
                    $bulan . '&tahun=' . $tahun;

                if (!headers_sent()) {
                    header('Location: ' . $urlKembali);
                    exit;
                }

                $pesan = 'Target penjualan berhasil disimpan.';
                $jenisPesan = 'success';

            } elseif ($pesan === '') {

                $pesan = 'Target gagal disimpan. Periksa struktur tabel dan koneksi database.';
                $jenisPesan = 'danger';
            }
        }
    }
}

// =====================================================
// DATA SALES AKTIF
// =====================================================

$dataSales = mysqli_query(
    $koneksi,
    "SELECT id_user, nama
     FROM tb_users
     WHERE role = 'sales'
     AND status = 'aktif'
     ORDER BY nama ASC"
);

// =====================================================
// DATA EDIT TARGET
// =====================================================

$dataEdit = null;

if (isset($_GET['edit'])) {

    $idEdit = (int) $_GET['edit'];

    if ($idEdit > 0) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM tb_target_sales WHERE id_target = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $idEdit);

            mysqli_stmt_execute($stmt);

            $resultEdit = mysqli_stmt_get_result($stmt);

            if ($resultEdit) {
                $dataEdit = mysqli_fetch_assoc($resultEdit);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// =====================================================
// TARGET DAN PENJUALAN AKTUAL
// =====================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        t.id_target,
        t.id_sales,
        u.nama,
        t.bulan,
        t.tahun,
        t.target_omzet,
        t.reward,
        t.reward_tipe,
        t.reward_nilai,
        COALESCE(p.omzet, 0) AS omzet

     FROM tb_target_sales t

     INNER JOIN tb_users u
        ON t.id_sales = u.id_user

     LEFT JOIN (
        SELECT
            id_sales,
            SUM(total_harga) AS omzet

        FROM tb_transaksi

        WHERE status_transaksi = 'selesai'
          AND MONTH(tanggal_transaksi) = ?
          AND YEAR(tanggal_transaksi) = ?

        GROUP BY id_sales

     ) p ON t.id_sales = p.id_sales

     WHERE t.bulan = ?
       AND t.tahun = ?

     ORDER BY u.nama ASC"
);

$rows = array();

$totalTarget = 0;
$totalOmzet = 0;
$totalTercapai = 0;
$totalInsentif = 0;

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "iiii",
        $bulanFilter,
        $tahunFilter,
        $bulanFilter,
        $tahunFilter
    );

    mysqli_stmt_execute($stmt);

    $dataTarget = mysqli_stmt_get_result($stmt);

    if ($dataTarget) {

        while ($row = mysqli_fetch_assoc($dataTarget)) {

            $target = (float) $row['target_omzet'];
            $omzet = (float) $row['omzet'];

            $tercapai = ($target > 0 && $omzet >= $target);

            $persen = $target > 0
                ? ($omzet / $target) * 100
                : 0;

            $rewardTipe = isset($row['reward_tipe'])
                ? $row['reward_tipe']
                : 'nominal';

            $rewardNilai = isset($row['reward_nilai'])
                ? (float) $row['reward_nilai']
                : 0;

            // Kompatibilitas data lama.
            if (
                $rewardNilai <= 0 &&
                $rewardTipe === 'nominal' &&
                isset($row['reward']) &&
                is_numeric($row['reward'])
            ) {
                $rewardNilai = (float) $row['reward'];
            }

            $insentif = 0;

            if ($tercapai) {

                if ($rewardTipe === 'persen') {

                    $insentif = $omzet * ($rewardNilai / 100);

                } else {

                    $insentif = $rewardNilai;
                }
            }

            $row['persen'] = $persen;
            $row['tercapai'] = $tercapai;
            $row['reward_nilai_hitung'] = $rewardNilai;
            $row['insentif'] = $insentif;

            $rows[] = $row;

            $totalTarget += $target;
            $totalOmzet += $omzet;
            $totalInsentif += $insentif;

            if ($tercapai) {
                $totalTercapai++;
            }
        }
    }

    mysqli_stmt_close($stmt);
}

$persenKeseluruhan = $totalTarget > 0
    ? ($totalOmzet / $totalTarget) * 100
    : 0;

?>

<style>
.target-sales-page {
    padding: 20px;
}

.target-sales-page .card,
.target-sales-page .stat-box {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(0,0,0,.04);
}

.target-sales-page .card-header {
    background: #fff;
    font-weight: 700;
}

.target-sales-page .stat-box {
    padding: 20px;
    background: #fff;
    height: 100%;
}

.target-sales-page .stat-box .stat-label {
    color: #64748b;
    font-size: 14px;
}

.target-sales-page .stat-box h4 {
    font-weight: 700;
    margin: 9px 0 0;
}

.target-sales-page .form-control,
.target-sales-page .form-select {
    min-height: 43px;
    border-radius: 9px;
}

.target-sales-page .btn {
    border-radius: 9px;
}

.target-sales-page table th {
    white-space: nowrap;
    background: #f8fafc;
}

.target-sales-page table td {
    vertical-align: middle;
}

.target-sales-page .progress {
    height: 9px;
    border-radius: 20px;
    background: #e9ecef;
}

.target-sales-page .table-responsive {
    overflow-x: auto;
}

.target-sales-page .help-text {
    font-size: 12px;
    color: #64748b;
    margin-top: 5px;
}
</style>

<div class="app-main">
<div class="app-content">
<div class="container-fluid target-sales-page">

    <!-- JUDUL -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-bullseye text-primary"></i>
                Target Penjualan
            </h2>

            <p class="text-secondary mb-0">
                Kelola target omzet, pantau pencapaian, dan hitung insentif Sales.
            </p>
        </div>

        <span class="badge bg-primary p-3">
            <?= target_h($namaBulan[$bulanFilter] . ' ' . $tahunFilter); ?>
        </span>

    </div>

    <!-- PESAN -->
    <?php if ($pesan !== ''): ?>

        <div class="alert alert-<?= target_h($jenisPesan); ?> alert-dismissible fade show">

            <?= target_h($pesan); ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>

    <!-- RINGKASAN -->
    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="stat-box">

                <div class="stat-label">
                    Total Target Omzet
                </div>

                <h4 class="text-primary">
                    <?= target_rupiah($totalTarget); ?>
                </h4>

            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-box">

                <div class="stat-label">
                    Total Penjualan Aktual
                </div>

                <h4 class="text-success">
                    <?= target_rupiah($totalOmzet); ?>
                </h4>

            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-box">

                <div class="stat-label">
                    Sales Mencapai Target
                </div>

                <h4 class="text-success">
                    <?= (int) $totalTercapai; ?> Sales
                </h4>

            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-box">

                <div class="stat-label">
                    Total Insentif yang Diperoleh
                </div>

                <h4 class="text-dark">
                    <?= target_rupiah($totalInsentif); ?>
                </h4>

            </div>
        </div>

    </div>

    <!-- FORM TARGET -->
    <div class="card mb-4">

        <div class="card-header py-3">

            <i class="bi bi-plus-circle text-primary"></i>

            <?= $dataEdit ? 'Edit Target Penjualan' : 'Buat Target Penjualan'; ?>

        </div>

        <div class="card-body">

            <?php if (!$dataSales || mysqli_num_rows($dataSales) === 0): ?>

                <div class="alert alert-warning mb-0">

                    Belum ada akun Sales aktif.
                    Tambahkan atau aktifkan akun dengan role Sales terlebih dahulu.

                </div>

            <?php else: ?>

                <form method="POST"
                      action="dashboard_owner.php?menu=target_sales">

                    <?php if ($dataEdit): ?>

                        <input type="hidden"
                               name="id_target"
                               value="<?= (int) $dataEdit['id_target']; ?>">

                    <?php endif; ?>

                    <div class="row g-3">

                        <!-- SALES -->
                        <div class="col-md-4">

                            <label class="form-label fw-semibold">
                                Pilih Sales
                            </label>

                            <select name="id_sales"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Pilih Sales --
                                </option>

                                <?php

                                mysqli_data_seek($dataSales, 0);

                                while ($sales = mysqli_fetch_assoc($dataSales)):

                                ?>

                                    <option
                                        value="<?= (int) $sales['id_user']; ?>"
                                        <?= (
                                            $dataEdit &&
                                            (int) $dataEdit['id_sales'] === (int) $sales['id_user']
                                        ) ? 'selected' : ''; ?>
                                    >

                                        <?= target_h($sales['nama']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <!-- PERIODE -->
                        <div class="col-md-4">

                            <label class="form-label fw-semibold">
                                Periode Target
                            </label>

                            <input
                                type="month"
                                name="periode"
                                class="form-control"
                                required
                                value="<?= $dataEdit
                                    ? target_h(sprintf('%04d-%02d', $dataEdit['tahun'], $dataEdit['bulan']))
                                    : target_h(sprintf('%04d-%02d', $tahunFilter, $bulanFilter)); ?>"
                            >

                        </div>

                        <!-- TARGET OMZET -->
                        <div class="col-md-4">

                            <label class="form-label fw-semibold">
                                Target Omzet (Rp)
                            </label>

                            <input
                                type="number"
                                name="target_omzet"
                                class="form-control"
                                min="1"
                                step="1"
                                required
                                placeholder="Contoh: 10000000"
                                value="<?= $dataEdit ? target_h($dataEdit['target_omzet']) : ''; ?>"
                            >

                        </div>

                        <!-- JENIS REWARD -->
                        <div class="col-md-4">

                            <label class="form-label fw-semibold">
                                Jenis Reward
                            </label>

                            <?php

                            $jenisRewardEdit = $dataEdit &&
                                isset($dataEdit['reward_tipe'])
                                ? $dataEdit['reward_tipe']
                                : 'nominal';

                            ?>

                            <select
                                name="reward_tipe"
                                id="reward_tipe"
                                class="form-select"
                                required
                            >

                                <option
                                    value="nominal"
                                    <?= $jenisRewardEdit === 'nominal' ? 'selected' : ''; ?>
                                >
                                    Bonus Nominal (Rp)
                                </option>

                                <option
                                    value="persen"
                                    <?= $jenisRewardEdit === 'persen' ? 'selected' : ''; ?>
                                >
                                    Insentif Persentase (%)
                                </option>

                            </select>

                        </div>

                        <!-- NILAI REWARD -->
                        <div class="col-md-4">

                            <label
                                class="form-label fw-semibold"
                                id="label_reward_nilai"
                            >
                                Nilai Bonus (Rp)
                            </label>

                            <?php

                            $nilaiRewardEdit = '';

                            if ($dataEdit) {

                                if (
                                    isset($dataEdit['reward_nilai']) &&
                                    (float) $dataEdit['reward_nilai'] > 0
                                ) {

                                    $nilaiRewardEdit = $dataEdit['reward_nilai'];

                                } elseif (
                                    isset($dataEdit['reward']) &&
                                    is_numeric($dataEdit['reward'])
                                ) {

                                    $nilaiRewardEdit = $dataEdit['reward'];
                                }
                            }

                            ?>

                            <input
                                type="number"
                                name="reward_nilai"
                                id="reward_nilai"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="Contoh: 500000"
                                value="<?= target_h($nilaiRewardEdit); ?>"
                            >

                            <div
                                class="help-text"
                                id="help_reward_nilai"
                            >
                                Bonus dibayarkan jika Sales mencapai target.
                            </div>

                        </div>

                        <!-- TOMBOL -->
                        <div class="col-md-4 d-flex align-items-end gap-2">

                            <button
                                type="submit"
                                name="simpan_target"
                                class="btn btn-primary flex-fill"
                            >

                                <i class="bi bi-save"></i>

                                <?= $dataEdit ? 'Update Target' : 'Simpan Target'; ?>

                            </button>

                            <?php if ($dataEdit): ?>

                                <a
                                    href="dashboard_owner.php?menu=target_sales"
                                    class="btn btn-secondary"
                                >
                                    Batal
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </form>

            <?php endif; ?>

        </div>

    </div>

    <!-- FILTER PERIODE -->
    <div class="card mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="dashboard_owner.php"
                class="row g-3 align-items-end"
            >

                <input
                    type="hidden"
                    name="menu"
                    value="target_sales"
                >

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Bulan
                    </label>

                    <select name="bulan" class="form-select">

                        <?php foreach ($namaBulan as $nomor => $nama): ?>

                            <option
                                value="<?= (int) $nomor; ?>"
                                <?= $bulanFilter === $nomor ? 'selected' : ''; ?>
                            >

                                <?= target_h($nama); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Tahun
                    </label>

                    <select name="tahun" class="form-select">

                        <?php

                        for (
                            $tahun = (int) date('Y') - 2;
                            $tahun <= (int) date('Y') + 2;
                            $tahun++
                        ):

                        ?>

                            <option
                                value="<?= $tahun; ?>"
                                <?= $tahunFilter === $tahun ? 'selected' : ''; ?>
                            >

                                <?= $tahun; ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                </div>

                <div class="col-md-4">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >

                        <i class="bi bi-search"></i>
                        Tampilkan Data

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- TABEL PENCAPAIAN -->
    <div class="card">

        <div class="card-header py-3">

            <i class="bi bi-bar-chart-line text-primary"></i>

            Pencapaian Target Sales

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Nama Sales</th>
                            <th>Target Omzet</th>
                            <th>Penjualan Aktual</th>
                            <th>Pencapaian</th>
                            <th>Reward</th>
                            <th>Insentif Diperoleh</th>
                            <th>Status</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (count($rows) > 0): ?>

                        <?php foreach ($rows as $index => $row): ?>

                            <?php

                            $lebar = min(100, (float) $row['persen']);

                            $rewardTipeRow = isset($row['reward_tipe'])
                                ? $row['reward_tipe']
                                : 'nominal';

                            ?>

                            <tr>

                                <td>
                                    <?= $index + 1; ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= target_h($row['nama']); ?>
                                </td>

                                <td>
                                    <?= target_rupiah($row['target_omzet']); ?>
                                </td>

                                <td>
                                    <?= target_rupiah($row['omzet']); ?>
                                </td>

                                <!-- PENCAPAIAN -->
                                <td style="min-width:155px;">

                                    <div class="fw-semibold mb-1">

                                        <?= number_format(
                                            $row['persen'],
                                            1,
                                            ',',
                                            '.'
                                        ); ?>%

                                    </div>

                                    <div class="progress">

                                        <div
                                            class="progress-bar <?= $row['tercapai'] ? 'bg-success' : 'bg-primary'; ?>"
                                            role="progressbar"
                                            style="width: <?= $lebar; ?>%;"
                                            aria-valuenow="<?= $lebar; ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        >
                                        </div>

                                    </div>

                                </td>

                                <!-- REWARD -->
                                <td>

                                    <?php if ($rewardTipeRow === 'persen'): ?>

                                        <?= number_format(
                                            $row['reward_nilai_hitung'],
                                            2,
                                            ',',
                                            '.'
                                        ); ?>% dari omzet

                                    <?php else: ?>

                                        <?= target_rupiah(
                                            $row['reward_nilai_hitung']
                                        ); ?>

                                    <?php endif; ?>

                                </td>

                                <!-- INSENTIF -->
                                <td class="fw-semibold">

                                    <?php if ($row['tercapai']): ?>

                                        <span class="text-success">

                                            <?= target_rupiah($row['insentif']); ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-secondary">
                                            Belum diperoleh
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- STATUS -->
                                <td>

                                    <?php if ($row['tercapai']): ?>

                                        <span class="badge bg-success">
                                            Tercapai
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Belum Tercapai
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- AKSI -->
                                <td style="min-width:105px;">

                                    <a
                                        href="dashboard_owner.php?menu=target_sales&edit=<?= (int) $row['id_target']; ?>&bulan=<?= $bulanFilter; ?>&tahun=<?= $tahunFilter; ?>"
                                        class="btn btn-sm btn-warning"
                                        title="Edit target"
                                    >

                                        <i class="bi bi-pencil"></i>

                                    </a>

                                    <form
                                        method="POST"
                                        action="dashboard_owner.php?menu=target_sales&bulan=<?= $bulanFilter; ?>&tahun=<?= $tahunFilter; ?>"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus target penjualan ini?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_target"
                                            value="<?= (int) $row['id_target']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="hapus_target"
                                            class="btn btn-sm btn-danger"
                                            title="Hapus target"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="9" class="text-center py-4 text-secondary">

                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                Belum ada target penjualan untuk periode

                                <?= target_h(
                                    $namaBulan[$bulanFilter] . ' ' . $tahunFilter
                                ); ?>.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                    <?php if (count($rows) > 0): ?>

                        <tfoot>

                            <tr class="fw-bold">

                                <td colspan="2">
                                    TOTAL
                                </td>

                                <td>
                                    <?= target_rupiah($totalTarget); ?>
                                </td>

                                <td>
                                    <?= target_rupiah($totalOmzet); ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        $persenKeseluruhan,
                                        1,
                                        ',',
                                        '.'
                                    ); ?>%
                                </td>

                                <td>-</td>

                                <td class="text-success">
                                    <?= target_rupiah($totalInsentif); ?>
                                </td>

                                <td colspan="2">

                                    <?= (int) $totalTercapai; ?>
                                    Sales tercapai

                                </td>

                            </tr>

                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

            <div class="small text-secondary mt-2">

                Catatan: omzet dihitung dari transaksi berstatus
                <strong>selesai</strong> pada bulan dan tahun yang dipilih.
                Insentif baru dihitung setelah Sales mencapai target.

            </div>

        </div>

    </div>

</div>
</div>
</div>

<script>

(function () {

    var tipe = document.getElementById('reward_tipe');

    var label = document.getElementById('label_reward_nilai');

    var input = document.getElementById('reward_nilai');

    var bantuan = document.getElementById('help_reward_nilai');

    if (!tipe || !label || !input || !bantuan) {
        return;
    }

    function sesuaikanReward() {

        if (tipe.value === 'persen') {

            label.textContent = 'Persentase Insentif (%)';

            input.placeholder = 'Contoh: 5';

            input.max = '100';

            input.step = '0.01';

            bantuan.textContent =
                'Insentif dihitung dari omzet aktual jika Sales mencapai target.';

        } else {

            label.textContent = 'Nilai Bonus (Rp)';

            input.placeholder = 'Contoh: 500000';

            input.removeAttribute('max');

            input.step = '1';

            bantuan.textContent =
                'Bonus nominal dibayarkan jika Sales mencapai target.';
        }
    }

    tipe.addEventListener('change', sesuaikanReward);

    sesuaikanReward();

})();

</script>