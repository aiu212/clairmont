
<?php
// =====================================
// IDENTITAS SALES
// =====================================
$id_sales = (int)($_SESSION['id_user'] ?? 0);

if ($id_sales <= 0) {
    echo "<div class='alert alert-danger'>Identitas Sales tidak ditemukan.</div>";
    return;
}

$bulanSekarang = (int)date('n');
$tahunSekarang = (int)date('Y');

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
    4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// =====================================
// AMBIL TARGET SALES
// =====================================
$stmt = mysqli_prepare($koneksi, "
    SELECT target_omzet, reward
    FROM tb_target_sales
    WHERE id_sales = ?
      AND bulan = ?
      AND tahun = ?
    LIMIT 1
");

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $id_sales,
    $bulanSekarang,
    $tahunSekarang
);

mysqli_stmt_execute($stmt);
$resultTarget = mysqli_stmt_get_result($stmt);
$dataTarget = mysqli_fetch_assoc($resultTarget);
mysqli_stmt_close($stmt);

// =====================================
// HITUNG PENJUALAN AKTUAL
// =====================================
$stmt = mysqli_prepare($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total_penjualan
    FROM tb_transaksi
    WHERE id_sales = ?
      AND status_transaksi = 'selesai'
      AND MONTH(tanggal_transaksi) = ?
      AND YEAR(tanggal_transaksi) = ?
");

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $id_sales,
    $bulanSekarang,
    $tahunSekarang
);

mysqli_stmt_execute($stmt);
$resultPenjualan = mysqli_stmt_get_result($stmt);
$dataPenjualan = mysqli_fetch_assoc($resultPenjualan);
mysqli_stmt_close($stmt);

$totalPenjualan = (int)$dataPenjualan['total_penjualan'];

// =====================================
// HITUNG PENCAPAIAN
// =====================================
$targetOmzet = (int)($dataTarget['target_omzet'] ?? 0);
$reward = $dataTarget['reward'] ?? '';

$persentase = $targetOmzet > 0
    ? ($totalPenjualan / $targetOmzet) * 100
    : 0;

$persentaseTampil = min(100, $persentase);

$sisaTarget = max(0, $targetOmzet - $totalPenjualan);
$targetTercapai = $targetOmzet > 0 && $totalPenjualan >= $targetOmzet;

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold">Target Penjualan</h3>
            <p class="text-muted mb-0">
                Pantau pencapaian target penjualan bulanan Anda.
            </p>
        </div>
        <span class="badge bg-primary p-2">
            <?= $namaBulan[$bulanSekarang] . ' ' . $tahunSekarang ?>
        </span>
    </div>

    <?php if (!$dataTarget): ?>

        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Target penjualan bulan ini belum ditentukan oleh Owner.
        </div>

    <?php else: ?>

        <!-- INFORMASI TARGET -->
        <div class="row">

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Target Penjualan</p>
                        <h4 class="fw-bold text-primary">
                            Rp <?= number_format($targetOmzet, 0, ',', '.') ?>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Penjualan</p>
                        <h4 class="fw-bold text-success">
                            Rp <?= number_format($totalPenjualan, 0, ',', '.') ?>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted mb-1">Sisa Target</p>
                        <h4 class="fw-bold text-danger">
                            Rp <?= number_format($sisaTarget, 0, ',', '.') ?>
                        </h4>
                    </div>
                </div>
            </div>

        </div>

        <!-- PROGRESS TARGET -->
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-graph-up-arrow"></i>
                    Progress Pencapaian Target
                </h5>
            </div>

            <div class="card-body">

                <div class="d-flex justify-content-between mb-2">
                    <strong>Pencapaian Penjualan</strong>
                    <strong>
                        <?= number_format($persentase, 1) ?>%
                    </strong>
                </div>

                <div class="progress" style="height: 22px;">
                    <div class="progress-bar
                        <?= $targetTercapai ? 'bg-success' : 'bg-primary' ?>"
                        role="progressbar"
                        style="width: <?= $persentaseTampil ?>%">
                    </div>
                </div>

                <div class="mt-3">

                    <?php if ($targetTercapai): ?>

                        <div class="alert alert-success mb-0">
                            <i class="bi bi-trophy-fill"></i>
                            <strong>Selamat!</strong>
                            Target penjualan bulan ini berhasil dicapai.
                        </div>

                    <?php else: ?>

                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-bullseye"></i>
                            Tetap semangat! Anda masih perlu mencapai
                            Rp <?= number_format($sisaTarget, 0, ',', '.') ?>
                            untuk memenuhi target.
                        </div>

                    <?php endif; ?>

                </div>

            </div>
        </div>

        <!-- REWARD -->
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-gift"></i>
                    Reward Penjualan
                </h5>
            </div>

            <div class="card-body">

                <?php if ($targetTercapai): ?>

                    <h5 class="text-success fw-bold">
                        <i class="bi bi-check-circle-fill"></i>
                        Reward Berhasil Dicapai!
                    </h5>

                    <p class="mb-0">
                        <?= htmlspecialchars($reward ?: 'Target berhasil dicapai.') ?>
                    </p>

                <?php else: ?>

                    <h5 class="text-muted fw-bold">
                        Reward Belum Diperoleh
                    </h5>

                    <p class="mb-0">
                        <?= htmlspecialchars($reward ?: 'Belum ada reward yang ditentukan.') ?>
                    </p>

                    <small class="text-muted">
                        Reward akan diperoleh setelah target penjualan tercapai.
                    </small>

                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

</div>
