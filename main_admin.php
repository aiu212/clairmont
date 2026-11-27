
<?php
include "koneksi2.php";

/* =====================================================
   FUNGSI BANTU
===================================================== */

function dashboardEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dashboardQuery($koneksi, $sql) {
    $result = mysqli_query($koneksi, $sql);

    if (!$result) {
        error_log("Dashboard Query Error: " . mysqli_error($koneksi));
        return false;
    }

    return $result;
}

function dashboardNumber($value) {
    return number_format((float)$value, 0, ',', '.');
}

/* =====================================================
   1. STATISTIK UTAMA
===================================================== */

// Total customer
$qCustomer = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_customer
");

$customer = $qCustomer ? mysqli_fetch_assoc($qCustomer) : ['total' => 0];

// Total produk aktif
$qProduk = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_produk
    WHERE status = 'aktif'
");

$barang = $qProduk ? mysqli_fetch_assoc($qProduk) : ['total' => 0];

// Total seluruh transaksi (semua status)
$qTransaksi = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_transaksi
");

$transaksi = $qTransaksi ? mysqli_fetch_assoc($qTransaksi) : ['total' => 0];

// Total pendapatan hanya transaksi selesai
$qPendapatan = dashboardQuery($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total
    FROM tb_transaksi
    WHERE status_transaksi = 'selesai'
");

$pendapatan = $qPendapatan ? mysqli_fetch_assoc($qPendapatan) : ['total' => 0];

// Total transaksi selesai
$qSelesai = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_transaksi
    WHERE status_transaksi = 'selesai'
");

$transaksiSelesai = $qSelesai ? mysqli_fetch_assoc($qSelesai) : ['total' => 0];

// Total transaksi pending
$qPending = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_transaksi
    WHERE status_transaksi = 'pending'
");

$transaksiPending = $qPending ? mysqli_fetch_assoc($qPending) : ['total' => 0];

// Total transaksi batal
$qBatal = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_transaksi
    WHERE status_transaksi = 'batal'
");

$transaksiBatal = $qBatal ? mysqli_fetch_assoc($qBatal) : ['total' => 0];


/* =====================================================
   2. DATA STOK PRODUK
===================================================== */

$qTotalStok = dashboardQuery($koneksi, "
    SELECT COALESCE(SUM(stok), 0) AS total
    FROM tb_produk
    WHERE status = 'aktif'
");

$totalStok = $qTotalStok
    ? mysqli_fetch_assoc($qTotalStok)['total']
    : 0;

$qMenipis = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_produk
    WHERE status = 'aktif'
    AND stok BETWEEN 1 AND 5
");

$stokMenipis = $qMenipis
    ? mysqli_fetch_assoc($qMenipis)['total']
    : 0;

$qHabis = dashboardQuery($koneksi, "
    SELECT COUNT(*) AS total
    FROM tb_produk
    WHERE status = 'aktif'
    AND stok = 0
");

$stokHabis = $qHabis
    ? mysqli_fetch_assoc($qHabis)['total']
    : 0;


/* =====================================================
   3. TOTAL PRODUK TERJUAL
   HANYA TRANSAKSI SELESAI
===================================================== */

$qQty = dashboardQuery($koneksi, "
    SELECT COALESCE(SUM(d.jumlah), 0) AS total
    FROM tb_detail_transaksi d
    JOIN tb_transaksi t
        ON d.id_transaksi = t.id_transaksi
    WHERE t.status_transaksi = 'selesai'
");

$qty = $qQty
    ? mysqli_fetch_assoc($qQty)
    : ['total' => 0];


/* =====================================================
   4. LIMA PRODUK PALING LAKU
===================================================== */

$topBarang = dashboardQuery($koneksi, "
    SELECT
        p.id_produk,
        p.nama_produk,
        SUM(d.jumlah) AS total_terjual
    FROM tb_detail_transaksi d
    JOIN tb_produk p
        ON d.id_produk = p.id_produk
    JOIN tb_transaksi t
        ON d.id_transaksi = t.id_transaksi
    WHERE t.status_transaksi = 'selesai'
    GROUP BY p.id_produk, p.nama_produk
    ORDER BY total_terjual DESC
    LIMIT 5
");


/* =====================================================
   5. DAFTAR SEMUA PRODUK
===================================================== */

$barangSemua = dashboardQuery($koneksi, "
    SELECT
        id_produk,
        nama_produk,
        harga,
        stok,
        kode_produk,
        gambar
    FROM tb_produk
    WHERE status = 'aktif'
    ORDER BY nama_produk ASC
");


/* =====================================================
   6. TRANSAKSI TERBARU
===================================================== */

$qlatest = dashboardQuery($koneksi, "
    SELECT
        t.id_transaksi,
        t.kode_transaksi,
        t.tanggal_transaksi,
        t.total_harga,
        t.metode_pembayaran,
        t.status_transaksi,
        GROUP_CONCAT(
            DISTINCT p.nama_produk
            ORDER BY p.nama_produk
            SEPARATOR ', '
        ) AS produk
    FROM tb_transaksi t
    LEFT JOIN tb_detail_transaksi d
        ON t.id_transaksi = d.id_transaksi
    LEFT JOIN tb_produk p
        ON d.id_produk = p.id_produk
    GROUP BY
        t.id_transaksi,
        t.kode_transaksi,
        t.tanggal_transaksi,
        t.total_harga,
        t.metode_pembayaran,
        t.status_transaksi
    ORDER BY t.tanggal_transaksi DESC
    LIMIT 5
");


/* =====================================================
   7. DAFTAR CUSTOMER
===================================================== */

$customers = dashboardQuery($koneksi, "
    SELECT
        id_customer,
        nama_customer,
        alamat,
        no_hp,
        created_at
    FROM tb_customer
    ORDER BY created_at DESC
");


/* =====================================================
   8. DATA GRAFIK PENJUALAN 12 BULAN TERAKHIR
===================================================== */

$chartLabels = [];
$chartValues = [];
$chartData = [];

$qGrafik = dashboardQuery($koneksi, "
    SELECT
        DATE_FORMAT(tanggal_transaksi, '%Y-%m') AS bulan,
        SUM(total_harga) AS total
    FROM tb_transaksi
    WHERE status_transaksi = 'selesai'
    AND tanggal_transaksi >= DATE_FORMAT(
        DATE_SUB(CURDATE(), INTERVAL 11 MONTH),
        '%Y-%m-01'
    )
    AND tanggal_transaksi < DATE_FORMAT(
        DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
        '%Y-%m-01'
    )
    GROUP BY DATE_FORMAT(tanggal_transaksi, '%Y-%m')
    ORDER BY bulan ASC
");

if ($qGrafik) {
    while ($g = mysqli_fetch_assoc($qGrafik)) {
        $chartData[$g['bulan']] = (float)$g['total'];
    }
}

for ($i = 11; $i >= 0; $i--) {

    $bulan = date(
        'Y-m',
        strtotime("-$i months", strtotime(date('Y-m-01')))
    );

    $chartLabels[] = date(
        'M Y',
        strtotime($bulan . '-01')
    );

    $chartValues[] = isset($chartData[$bulan])
        ? $chartData[$bulan]
        : 0;
}


/* =====================================================
   9. FORMAT RINGKASAN
===================================================== */

$totalCustomer = (int)$customer['total'];
$totalProduk = (int)$barang['total'];
$totalTransaksi = (int)$transaksi['total'];
$totalPendapatan = (float)$pendapatan['total'];

?>

<!-- =====================================================
     DASHBOARD CONTENT
===================================================== -->

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Clairmont Dessert</h3>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <a href="#">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Clairmont
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>


<div class="app-content">
<div class="container-fluid">


<!-- =====================================================
     STATISTIK UTAMA
===================================================== -->

<div class="row">

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
                <i class="bi bi-people-fill"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Customer</span>
                <span class="info-box-number">
                    <?= dashboardNumber($totalCustomer); ?>
                </span>
            </div>
        </div>
    </div>


    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm">
                <i class="bi bi-box-seam"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Produk</span>
                <span class="info-box-number">
                    <?= dashboardNumber($totalProduk); ?>
                </span>
            </div>
        </div>
    </div>


    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-warning shadow-sm">
                <i class="bi bi-receipt"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Transaksi</span>
                <span class="info-box-number">
                    <?= dashboardNumber($totalTransaksi); ?>
                </span>
            </div>
        </div>
    </div>


    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-danger shadow-sm">
                <i class="bi bi-cash-stack"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Pendapatan</span>
                <span class="info-box-number">
                    Rp <?= dashboardNumber($totalPendapatan); ?>
                </span>
            </div>
        </div>
    </div>

</div>


<!-- =====================================================
     GRAFIK PENJUALAN DAN STATUS STOK
===================================================== -->

<div class="row">
<div class="col-md-12">

<div class="card mb-4">

    <div class="card-header">
        <h5 class="card-title">GRAFIK PENJUALAN</h5>

        <div class="card-tools">
            <button type="button"
                    class="btn btn-tool"
                    data-lte-toggle="card-collapse">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>


    <div class="card-body">

        <div class="row">

            <div class="col-md-8">

                <p class="text-center">
                    <strong>
                        Penjualan 12 Bulan Terakhir
                    </strong>
                </p>

                <div id="sales-chart" style="min-height:300px;"></div>

            </div>


            <div class="col-md-4">

                <p class="text-center">
                    <strong>Status Stok Produk</strong>
                </p>


                <div class="progress-group">
                    Stok Tersedia

                    <span class="float-end">
                        <b><?= dashboardNumber($totalStok); ?></b>
                    </span>

                    <div class="progress progress-sm">
                        <div class="progress-bar text-bg-success"
                             style="width:100%">
                        </div>
                    </div>
                </div>


                <div class="progress-group">
                    Produk Stok Menipis

                    <span class="float-end">
                        <b><?= dashboardNumber($stokMenipis); ?></b>
                    </span>

                    <div class="progress progress-sm">
                        <div class="progress-bar text-bg-warning"
                             style="width:100%">
                        </div>
                    </div>
                </div>


                <div class="progress-group">
                    Produk Habis

                    <span class="float-end">
                        <b><?= dashboardNumber($stokHabis); ?></b>
                    </span>

                    <div class="progress progress-sm">
                        <div class="progress-bar text-bg-danger"
                             style="width:100%">
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>


    <!-- RINGKASAN -->
    <div class="card-footer">

        <div class="row">

            <div class="col-md-3 col-6">
                <div class="text-center border-end">
                    <span class="text-success">
                        <i class="bi bi-people-fill"></i>
                    </span>

                    <h5 class="fw-bold mb-0">
                        <?= dashboardNumber($totalCustomer); ?>
                    </h5>

                    <span class="text-uppercase">
                        TOTAL CUSTOMER
                    </span>
                </div>
            </div>


            <div class="col-md-3 col-6">
                <div class="text-center border-end">
                    <span class="text-info">
                        <i class="bi bi-box-seam"></i>
                    </span>

                    <h5 class="fw-bold mb-0">
                        <?= dashboardNumber($totalProduk); ?>
                    </h5>

                    <span class="text-uppercase">
                        TOTAL PRODUK
                    </span>
                </div>
            </div>


            <div class="col-md-3 col-6">
                <div class="text-center border-end">
                    <span class="text-success">
                        <i class="bi bi-receipt"></i>
                    </span>

                    <h5 class="fw-bold mb-0">
                        <?= dashboardNumber($totalTransaksi); ?>
                    </h5>

                    <span class="text-uppercase">
                        TOTAL TRANSAKSI
                    </span>
                </div>
            </div>


            <div class="col-md-3 col-6">
                <div class="text-center">
                    <span class="text-danger">
                        <i class="bi bi-cash-stack"></i>
                    </span>

                    <h5 class="fw-bold mb-0">
                        Rp <?= dashboardNumber($totalPendapatan); ?>
                    </h5>

                    <span class="text-uppercase">
                        TOTAL PENDAPATAN
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>
</div>
</div>


<!-- =====================================================
     STATUS TRANSAKSI
===================================================== -->

<div class="row">

    <div class="col-md-4 col-6">
        <div class="info-box mb-3 text-bg-success">
            <span class="info-box-icon">
                <i class="bi bi-check-circle"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Transaksi Selesai</span>
                <span class="info-box-number">
                    <?= dashboardNumber($transaksiSelesai['total']); ?>
                </span>
            </div>
        </div>
    </div>


    <div class="col-md-4 col-6">
        <div class="info-box mb-3 text-bg-warning">
            <span class="info-box-icon">
                <i class="bi bi-clock-history"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Transaksi Pending</span>
                <span class="info-box-number">
                    <?= dashboardNumber($transaksiPending['total']); ?>
                </span>
            </div>
        </div>
    </div>


    <div class="col-md-4 col-6">
        <div class="info-box mb-3 text-bg-danger">
            <span class="info-box-icon">
                <i class="bi bi-x-circle"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Transaksi Batal</span>
                <span class="info-box-number">
                    <?= dashboardNumber($transaksiBatal['total']); ?>
                </span>
            </div>
        </div>
    </div>

</div>


<!-- =====================================================
     DAFTAR CUSTOMER
===================================================== -->

<div class="card mb-4">

    <div class="card-header">
        <h3 class="card-title">Daftar Customer</h3>

        <div class="card-tools">
            <span class="badge text-bg-info">
                <?= $customers ? mysqli_num_rows($customers) : 0; ?>
                Customer
            </span>

            <button type="button"
                    class="btn btn-tool"
                    data-lte-toggle="card-collapse">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>


    <div class="card-body">
        <div class="row text-center"
             style="max-height:400px;overflow-y:auto;">

            <?php if ($customers && mysqli_num_rows($customers) > 0) { ?>

                <?php while ($c = mysqli_fetch_assoc($customers)) { ?>

                    <div class="col-6 col-md-3 p-2">

                        <div class="border rounded p-3 h-100">

                            <i class="bi bi-person-circle fs-1 text-primary"></i>

                            <div class="fw-bold mt-2 text-truncate">
                                <?= dashboardEscape($c['nama_customer']); ?>
                            </div>

                            <div class="text-muted small">
                                ID: <?= (int)$c['id_customer']; ?>
                            </div>

                            <div class="text-muted small text-truncate">
                                <?= dashboardEscape($c['no_hp'] ?: '-'); ?>
                            </div>

                            <div class="text-muted small text-truncate">
                                <?= dashboardEscape($c['alamat'] ?: '-'); ?>
                            </div>

                            <div class="text-secondary small">
                                <?php
                                echo !empty($c['created_at'])
                                    ? date('d M Y', strtotime($c['created_at']))
                                    : '-';
                                ?>
                            </div>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="col-12 text-center text-muted py-4">
                    Belum ada data customer.
                </div>

            <?php } ?>

        </div>
    </div>

</div>


<!-- =====================================================
     TRANSAKSI TERBARU
===================================================== -->

<div class="card mb-4">

    <div class="card-header">
        <h3 class="card-title">Transaksi Terbaru</h3>

        <div class="card-tools">
            <button type="button"
                    class="btn btn-tool"
                    data-lte-toggle="card-collapse">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-striped mb-0">

                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>


                <tbody>

                <?php if ($qlatest && mysqli_num_rows($qlatest) > 0) { ?>

                    <?php $no = 1; ?>

                    <?php while ($l = mysqli_fetch_assoc($qlatest)) { ?>

                        <?php
                        $status = strtolower($l['status_transaksi']);

                        $badgeClass = 'bg-secondary';

                        if ($status == 'selesai') {
                            $badgeClass = 'bg-success';
                        } elseif ($status == 'pending') {
                            $badgeClass = 'bg-warning text-dark';
                        } elseif ($status == 'proses') {
                            $badgeClass = 'bg-info text-dark';
                        } elseif ($status == 'batal') {
                            $badgeClass = 'bg-danger';
                        }
                        ?>

                        <tr>

                            <td class="text-center">
                                <?= $no++; ?>
                            </td>

                            <td>
                                <?= dashboardEscape($l['kode_transaksi']); ?>
                            </td>

                            <td>
                                <?= dashboardEscape(
                                    date('d-m-Y H:i', strtotime($l['tanggal_transaksi']))
                                ); ?>
                            </td>

                            <td>
                                <?= dashboardEscape($l['produk'] ?: '-'); ?>
                            </td>

                            <td>
                                <?= dashboardEscape($l['metode_pembayaran']); ?>
                            </td>

                            <td class="text-end">
                                Rp <?= dashboardNumber($l['total_harga']); ?>
                            </td>

                            <td class="text-center">
                                <span class="badge <?= $badgeClass; ?>">
                                    <?= dashboardEscape(ucfirst($status)); ?>
                                </span>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            Belum ada transaksi.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>


    <div class="card-footer clearfix">

        <a href="dashboard_admin.php?menu=transaksi"
           class="btn btn-sm btn-primary float-end">
            Lihat Semua Transaksi
        </a>

    </div>

</div>


<!-- =====================================================
     PRODUK TERLARIS DAN TOTAL PRODUK TERJUAL
===================================================== -->

<div class="row">

    <div class="col-md-4">

        <div class="info-box mb-3 text-bg-warning">
            <span class="info-box-icon">
                <i class="bi bi-box-seam"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Produk</span>
                <span class="info-box-number">
                    <?= dashboardNumber($totalProduk); ?>
                </span>
            </div>
        </div>


        <div class="info-box mb-3 text-bg-success">
            <span class="info-box-icon">
                <i class="bi bi-people-fill"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Customer</span>
                <span class="info-box-number">
                    <?= dashboardNumber($totalCustomer); ?>
                </span>
            </div>
        </div>


        <div class="info-box mb-3 text-bg-danger">
            <span class="info-box-icon">
                <i class="bi bi-receipt"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Transaksi</span>
                <span class="info-box-number">
                    <?= dashboardNumber($totalTransaksi); ?>
                </span>
            </div>
        </div>


        <div class="info-box mb-3 text-bg-info">
            <span class="info-box-icon">
                <i class="bi bi-cart-check-fill"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total Produk Terjual</span>
                <span class="info-box-number">
                    <?= dashboardNumber($qty['total']); ?>
                </span>
            </div>
        </div>

    </div>


    <div class="col-md-8">

        <div class="card mb-4">

            <div class="card-header">
                <h3 class="card-title">5 Produk Paling Laku</h3>

                <div class="card-tools">
                    <button type="button"
                            class="btn btn-tool"
                            data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>


            <div class="card-body p-0">

                <table class="table table-striped mb-0">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th class="text-end">Total Terjual</th>
                        </tr>
                    </thead>


                    <tbody>

                    <?php if ($topBarang && mysqli_num_rows($topBarang) > 0) { ?>

                        <?php $no = 1; ?>

                        <?php while ($b = mysqli_fetch_assoc($topBarang)) { ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td>
                                    <?= dashboardEscape($b['nama_produk']); ?>
                                </td>

                                <td class="text-end">
                                    <span class="badge bg-success">
                                        <?= dashboardNumber($b['total_terjual']); ?>
                                        unit
                                    </span>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="3"
                                class="text-center text-muted py-3">
                                Belum ada penjualan selesai.
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>


            <div class="card-footer text-muted">
                Hanya menghitung produk dari transaksi selesai.
            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     DAFTAR SEMUA PRODUK
===================================================== -->

<div class="card mb-4">

    <div class="card-header">
        <h3 class="card-title">Daftar Semua Produk</h3>

        <div class="card-tools">
            <button type="button"
                    class="btn btn-tool"
                    data-lte-toggle="card-collapse">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>


    <div class="card-body p-0">

        <div class="px-2"
             style="max-height:400px;overflow-y:auto;">

            <?php if ($barangSemua && mysqli_num_rows($barangSemua) > 0) { ?>

                <?php while ($b = mysqli_fetch_assoc($barangSemua)) { ?>

                    <?php
                    $namaGambar = basename((string)$b['gambar']);

                    $imgPath = __DIR__ . '/uploads/' . $namaGambar;

                    $img = (
                        !empty($namaGambar) &&
                        is_file($imgPath)
                    )
                        ? 'uploads/' . rawurlencode($namaGambar)
                        : 'uploads/default.png';
                    ?>

                    <div class="d-flex border-top py-2 px-1 align-items-center">

                        <div class="col-2 text-center">

                            <img src="<?= dashboardEscape($img); ?>"
                                 class="img-size-50 rounded"
                                 alt="<?= dashboardEscape($b['nama_produk']); ?>"
                                 style="width:55px;height:55px;object-fit:cover;">

                        </div>


                        <div class="col-10">

                            <div class="fw-bold">
                                <?= dashboardEscape($b['nama_produk']); ?>

                                <span class="badge text-bg-primary float-end">
                                    Rp <?= dashboardNumber($b['harga']); ?>
                                </span>
                            </div>

                            <div class="text-muted small">
                                Stok: <?= dashboardNumber($b['stok']); ?>
                                |
                                Kode: <?= dashboardEscape($b['kode_produk']); ?>
                            </div>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="text-center py-4 text-muted">
                    Data produk belum tersedia.
                </div>

            <?php } ?>

        </div>

    </div>


    <div class="card-footer text-center">

        <a href="dashboard_admin.php?menu=produk"
           class="uppercase">
            Lihat Semua Produk
        </a>

    </div>

</div>


</div>
</div>


<!-- =====================================================
     GRAFIK PENJUALAN DINAMIS
===================================================== -->

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {

    const chartElement = document.querySelector('#sales-chart');

    if (!chartElement) {
        return;
    }

    const labels = <?= json_encode(
        $chartLabels,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ); ?>;

    const values = <?= json_encode(
        $chartValues,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ); ?>;

    if (typeof ApexCharts === 'undefined') {
        chartElement.innerHTML =
            '<div class="text-center text-muted py-5">' +
            'Grafik belum dapat ditampilkan. Periksa pemuatan ApexCharts.' +
            '</div>';
        return;
    }

    if (chartElement.dataset.rendered === '1') {
        return;
    }

    chartElement.dataset.rendered = '1';

    const options = {

        series: [{
            name: 'Total Penjualan',
            data: values
        }],

        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            },
            fontFamily: 'Segoe UI, Arial, sans-serif'
        },

        colors: ['#0d6efd'],

        dataLabels: {
            enabled: false
        },

        stroke: {
            curve: 'smooth',
            width: 4
        },

        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },

        xaxis: {
            categories: labels
        },

        yaxis: {
            labels: {
                formatter: function (value) {
                    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
                }
            }
        },

        tooltip: {
            y: {
                formatter: function (value) {
                    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
                }
            }
        },

        noData: {
            text: 'Belum ada data penjualan.'
        }
    };

    const chart = new ApexCharts(chartElement, options);

    chart.render();

})();
</script>