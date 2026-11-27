
<?php
// =============================================
// TARGET PENJUALAN - OWNER CLAIRMONT DESSERT
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($koneksi)) {
    require_once __DIR__ . '/../../koneksi2.php';
}

mysqli_set_charset($koneksi, 'utf8mb4');

// Validasi role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    exit('Akses ditolak.');
}

$pesan = '';
$jenisPesan = 'success';

// Periode yang ditampilkan
$bulanFilter = isset($_GET['bulan']) ? (int) $_GET['bulan'] : (int) date('m');
$tahunFilter = isset($_GET['tahun']) ? (int) $_GET['tahun'] : (int) date('Y');

if ($bulanFilter < 1 || $bulanFilter > 12) {
    $bulanFilter = (int) date('m');
}

if ($tahunFilter < 2000 || $tahunFilter > 2100) {
    $tahunFilter = (int) date('Y');
}

$namaBulan = [
    1 => 'Januari', 2 => 'Februari',
    3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober',
    11 => 'November', 12 => 'Desember'
];

// =============================================
// HAPUS TARGET
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['hapus_target'])) {

    $idHapus = (int) $_POST['id_target'];

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM tb_target_sales WHERE id_target = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $idHapus);

    if (mysqli_stmt_execute($stmt)) {
        $pesan = 'Target penjualan berhasil dihapus.';
    } else {
        $pesan = 'Gagal menghapus target penjualan.';
        $jenisPesan = 'danger';
    }

    mysqli_stmt_close($stmt);
}

// =============================================
// SIMPAN / UPDATE TARGET
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['simpan_target'])) {

    $idSales = (int) ($_POST['id_sales'] ?? 0);
    $targetOmzet = trim($_POST['target_omzet'] ?? '');
    $reward = trim($_POST['reward'] ?? '');
    $periode = $_POST['periode'] ?? '';
    $editId = (int) ($_POST['id_target'] ?? 0);

    $tanggal = DateTime::createFromFormat('!Y-m', $periode);

    if (!$tanggal || $tanggal->format('Y-m') !== $periode) {
        $pesan = 'Periode target tidak valid.';
        $jenisPesan = 'danger';
    } elseif ($idSales <= 0 || !ctype_digit($targetOmzet)
        || (float) $targetOmzet <= 0) {

        $pesan = 'Pilih Sales dan masukkan target omzet yang valid.';
        $jenisPesan = 'danger';

    } else {

        $bulan = (int) $tanggal->format('m');
        $tahun = (int) $tanggal->format('Y');

        // Pastikan akun yang dipilih benar-benar Sales aktif
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_user FROM tb_users
             WHERE id_user = ?
             AND role = 'sales'
             AND status = 'aktif'"
        );

        mysqli_stmt_bind_param($stmt, "i", $idSales);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $salesValid = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if (!$salesValid) {

            $pesan = 'Akun Sales tidak ditemukan atau tidak aktif.';
            $jenisPesan = 'danger';

        } else {

            // Jika sedang mengedit
            if ($editId > 0) {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE tb_target_sales
                     SET id_sales = ?,
                         bulan = ?,
                         tahun = ?,
                         target_omzet = ?,
                         reward = ?
                     WHERE id_target = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiiisi",
                    $idSales,
                    $bulan,
                    $tahun,
                    $targetOmzet,
                    $reward,
                    $editId
                );

                $berhasil = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

            } else {

                // Cek apakah target bulan tersebut sudah tersedia
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT id_target
                     FROM tb_target_sales
                     WHERE id_sales = ?
                     AND bulan = ?
                     AND tahun = ?
                     LIMIT 1"
                );

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

                if ($sudahAda) {

                    // Perbarui target yang sudah ada
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "UPDATE tb_target_sales
                         SET target_omzet = ?, reward = ?
                         WHERE id_target = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssi",
                        $targetOmzet,
                        $reward,
                        $idTargetLama
                    );

                } else {

                    // Buat target baru
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO tb_target_sales
                         (id_sales, bulan, tahun, target_omzet, reward)
                         VALUES (?, ?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iiiss",
                        $idSales,
                        $bulan,
                        $tahun,
                        $targetOmzet,
                        $reward
                    );
                }

                $berhasil = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            if ($berhasil) {
                $pesan = 'Target penjualan berhasil disimpan.';
            } else {
                $pesan = 'Gagal menyimpan target: ' . mysqli_error($koneksi);
                $jenisPesan = 'danger';
            }
        }
    }
}

// =============================================
// AMBIL DATA SALES AKTIF
// =============================================

$dataSales = mysqli_query(
    $koneksi,
    "SELECT id_user, nama
     FROM tb_users
     WHERE role = 'sales'
     AND status = 'aktif'
     ORDER BY nama ASC"
);

// =============================================
// DATA EDIT TARGET
// =============================================

$dataEdit = null;

if (isset($_GET['edit'])) {

    $idEdit = (int) $_GET['edit'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM tb_target_sales
         WHERE id_target = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $idEdit);
    mysqli_stmt_execute($stmt);

    $resultEdit = mysqli_stmt_get_result($stmt);
    $dataEdit = mysqli_fetch_assoc($resultEdit);

    mysqli_stmt_close($stmt);
}

// =============================================
// AMBIL TARGET DAN OMZET AKTUAL SALES
// =============================================

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

// =============================================
// STATISTIK
// =============================================

$totalTarget = 0;
$totalOmzet = 0;
$totalTercapai = 0;

$rows = [];

while ($row = mysqli_fetch_assoc($dataTarget)) {

    $target = (float) $row['target_omzet'];
    $omzet = (float) $row['omzet'];

    $persen = $target > 0
        ? ($omzet / $target) * 100
        : 0;

    $row['persen'] = $persen;

    $rows[] = $row;

    $totalTarget += $target;
    $totalOmzet += $omzet;

    if ($omzet >= $target) {
        $totalTercapai++;
    }
}

mysqli_stmt_close($stmt);

$persenKeseluruhan = $totalTarget > 0
    ? min(100, ($totalOmzet / $totalTarget) * 100)
    : 0;

?>

<style>
.target-sales-page {
    padding: 20px;
}

.target-sales-page .card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,.04);
}

.target-sales-page .card-header {
    background: #fff;
    font-weight: 700;
}

.target-sales-page .stat-box {
    padding: 20px;
    border-radius: 12px;
    background: white;
    border: 1px solid #e5e7eb;
    height: 100%;
}

.target-sales-page .stat-box h4 {
    font-weight: 700;
    margin-top: 8px;
}

.target-sales-page .progress {
    height: 10px;
    border-radius: 20px;
}

.target-sales-page .form-control,
.target-sales-page .form-select {
    border-radius: 8px;
    min-height: 42px;
}

.target-sales-page .btn {
    border-radius: 8px;
}

.target-sales-page table th {
    white-space: nowrap;
    background: #f8fafc;
}

.target-sales-page table td {
    vertical-align: middle;
}

.target-sales-page .table-responsive {
    overflow-x: auto;
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
                Kelola target omzet dan reward untuk setiap Sales.
            </p>
        </div>

        <span class="badge bg-primary p-3">
            <?= $namaBulan[$bulanFilter] . ' ' . $tahunFilter; ?>
        </span>

    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?= $jenisPesan; ?> alert-dismissible fade show">
            <?= htmlspecialchars($pesan); ?>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="stat-box">
                <span class="text-secondary">Total Target Omzet</span>
                <h4 class="text-primary">
                    Rp <?= number_format($totalTarget, 0, ',', '.'); ?>
                </h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-box">
                <span class="text-secondary">Total Penjualan</span>
                <h4 class="text-success">
                    Rp <?= number_format($totalOmzet, 0, ',', '.'); ?>
                </h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-box">
                <span class="text-secondary">Target Tercapai</span>
                <h4 class="text-success">
                    <?= $totalTercapai; ?> Sales
                </h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-box">
                <span class="text-secondary">Pencapaian Keseluruhan</span>
                <h4><?= number_format($persenKeseluruhan, 1); ?>%</h4>
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

            <form method="POST"
                  action="dashboard_owner.php?menu=target_sales">

                <?php if ($dataEdit): ?>
                    <input type="hidden" name="id_target"
                           value="<?= (int) $dataEdit['id_target']; ?>">
                <?php endif; ?>

                <div class="row g-3">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Pilih Sales
                        </label>

                        <select name="id_sales" class="form-select" required>

                            <option value="">-- Pilih Sales --</option>

                            <?php
                            if ($dataSales) {
                                mysqli_data_seek($dataSales, 0);

                                while ($sales = mysqli_fetch_assoc($dataSales)):
                            ?>

                                <option
                                    value="<?= (int) $sales['id_user']; ?>"
                                    <?= ($dataEdit && $dataEdit['id_sales'] == $sales['id_user'])
                                        ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars($sales['nama']); ?>

                                </option>

                            <?php
                                endwhile;
                            }
                            ?>

                        </select>

                    </div>

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
                                ? sprintf('%04d-%02d', $dataEdit['tahun'], $dataEdit['bulan'])
                                : sprintf('%04d-%02d', $tahunFilter, $bulanFilter); ?>">

                    </div>

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
                            placeholder="Contoh: 10000000"
                            required
                            value="<?= $dataEdit
                                ? htmlspecialchars($dataEdit['target_omzet'])
                                : ''; ?>">

                    </div>

                    <div class="col-md-8">

                        <label class="form-label fw-semibold">
                            Reward Jika Target Tercapai
                        </label>

                        <input
                            type="text"
                            name="reward"
                            class="form-control"
                            maxlength="255"
                            placeholder="Contoh: Bonus Rp 500.000 atau Insentif 5%"
                            value="<?= $dataEdit
                                ? htmlspecialchars($dataEdit['reward'] ?? '')
                                : ''; ?>">

                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">

                        <button type="submit"
                                name="simpan_target"
                                class="btn btn-primary flex-fill">

                            <i class="bi bi-save"></i>
                            <?= $dataEdit ? 'Update Target' : 'Simpan Target'; ?>

                        </button>

                        <?php if ($dataEdit): ?>
                            <a href="dashboard_owner.php?menu=target_sales"
                               class="btn btn-secondary">
                                Batal
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

            </form>

        </div>
    </div>

    <!-- FILTER PERIODE -->
    <div class="card mb-4">

        <div class="card-body">

            <form method="GET"
                  action="dashboard_owner.php"
                  class="row g-3 align-items-end">

                <input type="hidden" name="menu" value="target_sales">

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Bulan
                    </label>

                    <select name="bulan" class="form-select">

                        <?php foreach ($namaBulan as $nomor => $nama): ?>

                            <option value="<?= $nomor; ?>"
                                <?= $bulanFilter == $nomor ? 'selected' : ''; ?>>

                                <?= $nama; ?>

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
                        for ($tahun = date('Y') - 2;
                             $tahun <= date('Y') + 2;
                             $tahun++):
                        ?>

                            <option value="<?= $tahun; ?>"
                                <?= $tahunFilter == $tahun ? 'selected' : ''; ?>>

                                <?= $tahun; ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                </div>

                <div class="col-md-4">

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                        Tampilkan Data
                    </button>

                </div>

            </form>

        </div>
    </div>

    <!-- TABEL TARGET -->
    <div class="card">

        <div class="card-header py-3">
            <i class="bi bi-bar-chart-line text-primary"></i>
            Pencapaian Target Sales
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Sales</th>
                            <th>Target Omzet</th>
                            <th>Penjualan Aktual</th>
                            <th>Pencapaian</th>
                            <th>Reward</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($rows) > 0): ?>

                        <?php foreach ($rows as $index => $row): ?>

                            <?php
                            $tercapai = $row['omzet'] >= $row['target_omzet'];
                            $persen = $row['persen'];
                            $lebar = min(100, $persen);
                            ?>

                            <tr>

                                <td><?= $index + 1; ?></td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($row['nama']); ?>
                                </td>

                                <td>
                                    Rp <?= number_format($row['target_omzet'], 0, ',', '.'); ?>
                                </td>

                                <td>
                                    Rp <?= number_format($row['omzet'], 0, ',', '.'); ?>
                                </td>

                                <td style="min-width:160px;">

                                    <div class="fw-semibold mb-1">
                                        <?= number_format($persen, 1); ?>%
                                    </div>

                                    <div class="progress">

                                        <div
                                            class="progress-bar <?= $tercapai
                                                ? 'bg-success'
                                                : 'bg-primary'; ?>"
                                            style="width: <?= $lebar; ?>%">
                                        </div>

                                    </div>

                                </td>

                                <td>
    <?php
    $reward = $row['reward'] ?? '';

    if ($reward !== '' && is_numeric($reward)) {
        echo 'Rp ' . number_format((float)$reward, 0, ',', '.');
    } else {
        echo htmlspecialchars($reward ?: '-');
    }
    ?>
</td>

                                <td>

                                    <?php if ($tercapai): ?>

                                        <span class="badge bg-success">
                                            Tercapai
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Belum Tercapai
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td style="min-width:120px;">

                                    <a
                                        href="dashboard_owner.php?menu=target_sales&edit=<?= $row['id_target']; ?>"
                                        class="btn btn-sm btn-warning">

                                        <i class="bi bi-pencil"></i>

                                    </a>

                                    <form method="POST"
                                          action="dashboard_owner.php?menu=target_sales&bulan=<?= $bulanFilter; ?>&tahun=<?= $tahunFilter; ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus target ini?')">

                                        <input type="hidden"
                                               name="id_target"
                                               value="<?= $row['id_target']; ?>">

                                        <button type="submit"
                                                name="hapus_target"
                                                class="btn btn-sm btn-danger">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">

                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                Belum ada target penjualan untuk periode ini.

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>
</div>
</div>
