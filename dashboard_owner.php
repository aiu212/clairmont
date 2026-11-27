
<?php
session_start();
require_once __DIR__ . '/koneksi2.php';

// =====================================
// VALIDASI LOGIN OWNER
// =====================================
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'owner'
) {
    header('Location: login.php');
    exit;
}

mysqli_set_charset($koneksi, 'utf8mb4');

$menu = $_GET['menu'] ?? 'home';

$emailOwner = $_SESSION['email'] ?? 'Owner';
$namaOwner = ucwords(str_replace('@', ' ', explode('@', $emailOwner)[0]));

// =====================================
// DATA GRAFIK PENJUALAN
// =====================================
$qGrafik = mysqli_query($koneksi, "
    SELECT
        DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') AS bulan,
        SUM(d.jumlah * d.harga) AS total
    FROM tb_transaksi t
    JOIN tb_detail_transaksi d
        ON t.id_transaksi = d.id_transaksi
    WHERE t.status_transaksi = 'selesai'
    GROUP BY DATE_FORMAT(t.tanggal_transaksi, '%Y-%m')
    ORDER BY bulan ASC
");

$bulan = [];
$total = [];

if ($qGrafik) {
    while ($row = mysqli_fetch_assoc($qGrafik)) {
        $bulan[] = $row['bulan'];
        $total[] = (int) $row['total'];
    }
}

// =====================================
// NONAKTIFKAN CACHE
// =====================================
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Clairmont - Dashboard Owner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="color-scheme" content="light">

    <!-- FONT -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- OVERLAY SCROLLBARS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">

    <!-- ADMINLTE -->
    <link rel="stylesheet" href="dist/css/adminlte.css">

    <!-- APEXCHARTS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css">

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

    <!-- ================================= -->
    <!-- HEADER -->
    <!-- ================================= -->

    <nav class="app-header navbar navbar-expand bg-primary-subtle">

        <div class="container-fluid">

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link"
                       data-lte-toggle="sidebar"
                       href="#">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">

                <!-- USER OWNER -->
                <li class="nav-item dropdown user-menu">

                    <a href="#"
                       class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       data-bs-toggle="dropdown">

                        <div class="rounded-circle d-flex justify-content-center align-items-center shadow"
                             style="width:36px;height:36px;background:#7c3aed;color:white;">

                            <i class="bi bi-person-fill fs-5"></i>

                        </div>

                        <span class="d-none d-md-inline fw-semibold text-dark">
                            <?= htmlspecialchars($namaOwner); ?>
                        </span>

                    </a>

                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">

                        <li class="user-header text-center"
                            style="background:linear-gradient(135deg,#7c3aed,#a78bfa);color:white;">

                            <div class="mx-auto mb-2 rounded-circle d-flex justify-content-center align-items-center"
                                 style="width:64px;height:64px;background:white;color:#7c3aed;">

                                <i class="bi bi-person-fill fs-1"></i>

                            </div>

                            <p class="mb-0 fw-semibold">
                                <?= htmlspecialchars($namaOwner); ?>
                            </p>

                            <small>
                                <?= htmlspecialchars($emailOwner); ?>
                            </small>

                        </li>

                        <li class="user-footer">

                            <a href="dashboard_owner.php?menu=profil"
                               class="btn btn-default btn-flat">
                                Profil
                            </a>

                            <a href="logout.php"
                               class="btn btn-default btn-flat float-end">
                                Logout
                            </a>

                        </li>

                    </ul>

                </li>

            </ul>

        </div>

    </nav>


    <!-- ================================= -->
    <!-- SIDEBAR -->
    <!-- ================================= -->

    <aside class="app-sidebar bg-danger-subtle">

        <div class="sidebar-brand">

            <a href="dashboard_owner.php" class="brand-link">

                <img
                    src="dist/assets/img/logo.jpeg"
                    alt="Clairmont Logo"
                    class="brand-image opacity-75 shadow"
                >

                <span class="brand-text fw-light">
                    Clairmont Dessert
                </span>

            </a>

        </div>

        <div class="sidebar-wrapper">

            <nav class="mt-2">

                <?php
                // MENU OWNER
                include __DIR__ . '/owner/menu_owner.php';
                ?>

            </nav>

        </div>

    </aside>


    <!-- ================================= -->
    <!-- KONTEN HALAMAN -->
    <!-- ================================= -->

    <?php

    switch ($menu) {

        // HOME
        case 'home':
            include __DIR__ . '/main_owner.php';
            break;


        // PRODUK
        case 'produk':
            include __DIR__ . '/form/barang/view.php';
            break;

        case 'barang_form':
            include __DIR__ . '/form/barang/form_barang.php';
            break;

        case 'load_produk':
            include __DIR__ . '/form/barang/load_produk.php';
            break;

        case 'cart_add':
            include __DIR__ . '/form/barang/cart_add.php';
            break;

        case 'cart':
            include __DIR__ . '/form/barang/cart.php';
            break;

        case 'cart_update':
            include __DIR__ . '/form/barang/cart_update.php';
            break;

        case 'checkout':
            include __DIR__ . '/form/barang/checkout.php';
            break;

        case 'checkout_proses':
            include __DIR__ . '/form/barang/checkout_proses.php';
            break;

        case 'checkout_sukses':
            include __DIR__ . '/form/barang/checkout_sukses.php';
            break;

        case 'checkout_info':
            include __DIR__ . '/form/barang/checkout_info.php';
            break;

        case 'pesanan':
            include __DIR__ . '/form/barang/pesanan.php';
            break;

        case 'pesanan_detail':
            include __DIR__ . '/form/barang/pesanan_detail.php';
            break;

        case 'invoice_pdf':
            include __DIR__ . '/form/barang/invoice_pdf.php';
            break;


        // CUSTOMER
        case 'customer':
            include __DIR__ . '/form/customer/view.php';
            break;

        case 'customer_form':
            include __DIR__ . '/form/customer/form_customer.php';
            break;


        // TRANSAKSI
        case 'transaksi':
            include __DIR__ . '/form/transaksi/view.php';
            break;

        case 'transaksi_form':
            include __DIR__ . '/form/transaksi/form_transaksi.php';
            break;

        case 'load_transaksi':
            include __DIR__ . '/form/transaksi/load_transaksi.php';
            break;


        // TARGET PENJUALAN
        case 'target_sales':
            include __DIR__ . '/form/owner/target_sales.php';
            break;


        // LAPORAN
        case 'view_barang':
            include __DIR__ . '/laporan/view_barang.php';
            break;

        case 'view_customer':
            include __DIR__ . '/laporan/view_customer.php';
            break;

        case 'lap_transaksi':
            include __DIR__ . '/laporan/lap_transaksi.php';
            break;


        // EDIT
        case 'edit_barang':
            include __DIR__ . '/form/barang/edit.php';
            break;

        case 'edit_customer':
            include __DIR__ . '/form/customer/edit.php';
            break;

        case 'edit_transaksi':
            include __DIR__ . '/form/transaksi/edit.php';
            break;


        // HAPUS
        case 'hapus_barang':
            include __DIR__ . '/form/barang/hapus.php';
            break;

        case 'hapus_customer':
            include __DIR__ . '/form/customer/hapus.php';
            break;

        case 'hapus_transaksi':
            include __DIR__ . '/form/transaksi/edit.php';
            break;


        // STOK
        case 'tambah_stok':
            include __DIR__ . '/form/stok_barang/form_stok.php';
            break;


        // PROFIL
        case 'profil':
            include __DIR__ . '/form/barang/profil.php';
            break;


        // LOGOUT
        case 'logout':
            header('Location: logout.php');
            exit;
            break;


        // HALAMAN TIDAK DIKENAL
        default:
            include __DIR__ . '/main_owner.php';
            break;

    }

    ?>


    <!-- ================================= -->
    <!-- FOOTER -->
    <!-- ================================= -->

    <footer class="app-footer bg-primary-subtle">

        <strong>
            Copyright &copy; 2025-2026
            <a href="dashboard_owner.php"
               class="text-decoration-none">
                Clairmont Dessert
            </a>.
        </strong>

        All rights reserved.

    </footer>

</div>


<!-- ================================= -->
<!-- JAVASCRIPT -->
<!-- ================================= -->

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

<script src="dist/js/adminlte.js"></script>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>


<script>

// =====================================
// GRAFIK PENJUALAN HOME
// =====================================

document.addEventListener('DOMContentLoaded', function () {

    const chartElement = document.querySelector('#sales-chart');

    // Grafik hanya dibuat jika elemen tersedia
    if (chartElement && typeof ApexCharts !== 'undefined') {

        const salesChartOptions = {

            series: [{
                name: 'Total Penjualan',
                data: <?= json_encode($total ?: [0]); ?>
            }],

            chart: {
                height: 180,
                type: 'area',
                toolbar: {
                    show: false
                }
            },

            dataLabels: {
                enabled: false
            },

            stroke: {
                curve: 'smooth'
            },

            colors: ['#0d6efd'],

            xaxis: {
                type: 'datetime',
                categories: <?= json_encode($bulan ?: [date('Y-m-01')]); ?>
            },

            tooltip: {
                x: {
                    format: 'MMMM yyyy'
                },

                y: {
                    formatter: function (val) {
                        return 'Rp ' + Number(val).toLocaleString('id-ID');
                    }
                }
            }

        };

        const salesChart = new ApexCharts(
            chartElement,
            salesChartOptions
        );

        salesChart.render();

    }

});


// =====================================
// OVERLAY SCROLLBARS
// =====================================

document.addEventListener('DOMContentLoaded', function () {

    const sidebarWrapper = document.querySelector('.sidebar-wrapper');

    if (
        sidebarWrapper &&
        window.OverlayScrollbarsGlobal &&
        window.OverlayScrollbarsGlobal.OverlayScrollbars &&
        window.innerWidth > 992
    ) {

        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: 'os-theme-light',
                autoHide: 'leave',
                clickScroll: true
            }
        });

    }

});

</script>

</body>
</html>
