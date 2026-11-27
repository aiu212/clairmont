
<?php
session_start();
require_once __DIR__ . '/koneksi2.php';

/* =========================
   VALIDASI LOGIN OWNER
========================= */
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    ($_SESSION['role'] ?? '') !== 'owner'
) {
    header('Location: login.php');
    exit;
}

mysqli_set_charset($koneksi, 'utf8mb4');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$menu = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['menu'] ?? 'home');

$emailOwner = $_SESSION['email'] ?? '-';
$namaOwner = $_SESSION['nama'] ?? ucwords(str_replace(
    ['.', '_', '-'],
    ' ',
    explode('@', $emailOwner)[0]
));

$projectBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($projectBase === '.') {
    $projectBase = '';
}

$dashboardUrl = $projectBase . '/dashboard_owner.php';
$logoutUrl = $projectBase . '/logout.php';

$laporanAktif = in_array(
    $menu,
    ['view_barang', 'view_customer', 'lap_transaksi'],
    true
);

/* =========================
   DATA GRAFIK PENJUALAN
========================= */
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
        $total[] = (int)$row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">

    <title>Clairmont Dessert | Dashboard Owner</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
          href="dist/css/adminlte.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css">

    <style>
        :root {
            --clairmont-pink: #f7d4dc;
            --clairmont-pink-strong: #efb2c2;
            --clairmont-lavender: #e6ddff;
            --clairmont-blue: #dceaff;
            --clairmont-ink: #493747;
            --clairmont-muted: #7c7180;
            --clairmont-bg: #fff8fb;
            --sidebar-width: 260px;
            --header-height: 64px;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Source Sans 3', sans-serif;
            color: var(--clairmont-ink);
            background: var(--clairmont-bg) !important;
            overflow-x: hidden;
        }

        .app-wrapper {
            min-height: 100vh;
            background: var(--clairmont-bg);
        }

        /* HEADER */
        .app-header {
            position: fixed !important;
            top: 0 !important;
            left: var(--sidebar-width) !important;
            right: 0 !important;
            width: auto !important;
            height: var(--header-height);
            min-height: var(--header-height);
            margin: 0 !important;
            padding: 0 20px;
            z-index: 1037;
            background: linear-gradient(
                110deg,
                #f7d4dc 0%,
                #f4e5f5 52%,
                #dceaff 100%
            ) !important;
            border-bottom: 1px solid rgba(130, 91, 119, .10);
            box-shadow: 0 2px 8px rgba(80, 50, 80, .04);
        }

        .app-header .nav-link {
            color: var(--clairmont-ink) !important;
        }

        .profile-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #7c3aed;
            color: white;
        }

        /* SIDEBAR */
        .app-sidebar {
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            width: var(--sidebar-width) !important;
            min-width: var(--sidebar-width) !important;
            max-width: var(--sidebar-width) !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 1040;
            background: #f8d9e0 !important;
            border-right: 1px solid rgba(130, 91, 119, .08);
            box-shadow: 2px 0 12px rgba(80, 50, 80, .04);
            transform: none !important;
        }

        .sidebar-brand {
            height: var(--header-height);
            min-height: var(--header-height);
            padding: 0 18px;
            background: #f8d9e0 !important;
            border-bottom: 1px solid rgba(130, 91, 119, .08);
        }

        .sidebar-brand .brand-link {
            display: flex;
            align-items: center;
            height: var(--header-height);
            padding: 0;
            color: var(--clairmont-ink) !important;
            text-decoration: none;
        }

        .sidebar-brand .brand-image {
            width: 40px;
            height: 40px;
            margin: 0 10px 0 0;
            object-fit: cover;
            border-radius: 10px;
            opacity: 1 !important;
        }

        .sidebar-brand .brand-text {
            font-size: 17px;
            font-weight: 700 !important;
            color: var(--clairmont-ink) !important;
        }

        .sidebar-wrapper {
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 8px 25px;
        }

        .sidebar-menu {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu .nav-link {
            display: flex;
            align-items: center;
            min-height: 45px;
            margin: 5px 4px;
            padding: 10px 13px;
            color: var(--clairmont-ink) !important;
            border-radius: 14px;
            font-weight: 600;
            transition: .2s ease;
        }

        .sidebar-menu .nav-link p {
            margin: 0;
            width: 100%;
        }

        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            color: #352536 !important;
            background: linear-gradient(135deg, #f3b6c2, #e6f0ff) !important;
            box-shadow: 0 6px 16px rgba(94, 64, 91, .12);
        }

        .sidebar-menu .nav-link:hover {
            transform: translateX(3px);
        }

        .sidebar-menu .nav-treeview {
            padding-left: 8px;
        }

        .sidebar-menu .nav-treeview .nav-link {
            min-height: 38px;
            font-size: 14px;
            background: rgba(255,255,255,.45);
        }

        .nav-arrow {
            float: right;
            transition: .2s;
        }

        .menu-open > .nav-link .nav-arrow {
            transform: rotate(90deg);
        }

        /* MAIN CONTENT */
        .app-main {
            display: block !important;
            min-width: 0;
            min-height: calc(100vh - var(--header-height));
            margin: 0 0 0 var(--sidebar-width) !important;
            padding: var(--header-height) 0 0 !important;
            background: var(--clairmont-bg) !important;
        }

        .app-content {
            min-height: calc(100vh - var(--header-height) - 60px);
            padding: 22px !important;
            background: transparent !important;
        }

        .app-content > .container-fluid {
            width: 100%;
            max-width: 100%;
            padding: 0 !important;
            margin: 0;
        }

        .app-main .card,
        .app-main .small-box,
        .app-main .info-box {
            border: 1px solid #f0dce8;
            border-radius: 16px;
            box-shadow: 0 5px 18px rgba(91, 62, 86, .06);
            overflow: hidden;
        }

        .app-main .card-header {
            background: #fff;
            border-bottom: 1px solid #f0dce8;
        }

        .app-main .table {
            --bs-table-border-color: #eadde6;
        }

        .app-main .form-control,
        .app-main .form-select {
            border-color: #ead9e5;
            border-radius: 10px;
        }

        .app-main .btn {
            border-radius: 10px;
        }

        /* PROFILE */
        .account-profile {
            max-width: 950px;
        }

        .account-profile .profile-banner {
            padding: 28px;
            border-radius: 18px;
            background: linear-gradient(120deg, #fce8f4, #e8e1ff);
            border: 1px solid #f0dce8;
        }

        .account-profile .profile-avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            color: #b66a91;
            font-size: 32px;
        }

        .account-profile .profile-row {
            padding: 17px 0;
            border-bottom: 1px solid #f0dce8;
        }

        .account-profile .profile-row:last-child {
            border-bottom: 0;
        }

        /* FOOTER */
        .app-footer {
            display: block;
            margin: 0 0 0 var(--sidebar-width) !important;
            padding: 16px 22px;
            color: var(--clairmont-ink);
            background: linear-gradient(
                110deg,
                #f7d4dc 0%,
                #f4e5f5 52%,
                #dceaff 100%
            ) !important;
            border-top: 1px solid rgba(130, 91, 119, .10);
        }

        .dropdown-menu {
            border: 1px solid #f0dce8;
            border-radius: 13px;
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%) !important;
                transition: transform .25s ease;
            }

            body.sidebar-open .app-sidebar {
                transform: translateX(0) !important;
            }

            .app-header {
                left: 0 !important;
            }

            .app-main,
            .app-footer {
                margin-left: 0 !important;
            }

            .app-content {
                padding: 15px !important;
            }

            body.sidebar-open::after {
                content: "";
                position: fixed;
                inset: 0;
                z-index: 1039;
                background: rgba(0,0,0,.25);
            }
        }

        @media (max-width: 575.98px) {
            .sidebar-brand .brand-text {
                font-size: 15px;
            }

            .app-header {
                padding: 0 10px;
            }

            .app-content {
                padding: 12px !important;
            }
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

<div class="app-wrapper">

    <!-- HEADER -->
    <nav class="app-header navbar navbar-expand">
        <div class="container-fluid">

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">

                    <a href="#"
                       class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       data-bs-toggle="dropdown">

                        <div class="profile-circle">
                            <i class="bi bi-person-fill fs-4"></i>
                        </div>

                        <span class="fw-semibold">
                            <?= htmlspecialchars($namaOwner, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow"
                        style="min-width:240px;">

                        <li class="text-center p-3"
                            style="background:linear-gradient(135deg,#f3b6c2,#dceaff);">

                            <div class="profile-circle mx-auto mb-2"
                                 style="width:60px;height:60px;background:white;color:#bd6689;">
                                <i class="bi bi-person-fill fs-1"></i>
                            </div>

                            <strong>
                                <?= htmlspecialchars($namaOwner, ENT_QUOTES, 'UTF-8'); ?>
                            </strong>

                            <div class="small">
                                <?= htmlspecialchars($emailOwner, ENT_QUOTES, 'UTF-8'); ?>
                            </div>

                            <span class="badge bg-primary mt-2">Owner</span>
                        </li>

                        <li class="p-2 d-flex justify-content-between">
                            <a href="<?= htmlspecialchars($dashboardUrl . '?menu=profil'); ?>"
                               class="btn btn-outline-secondary btn-sm">
                                Profil
                            </a>

                            <a href="<?= htmlspecialchars($logoutUrl); ?>"
                               class="btn btn-danger btn-sm">
                                Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="app-sidebar" data-bs-theme="light">

        <div class="sidebar-brand">
            <a href="<?= htmlspecialchars($dashboardUrl); ?>" class="brand-link">

                <img src="dist/assets/img/logo.jpeg"
                     alt="Clairmont Dessert"
                     class="brand-image shadow"
                     onerror="this.style.display='none'">

                <span class="brand-text">Clairmont Dessert</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav>
                <ul class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview"
                    data-accordion="false">

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl); ?>"
                           class="nav-link <?= $menu === 'home' ? 'active' : ''; ?>">
                            <p>🏠 Home</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl . '?menu=produk'); ?>"
                           class="nav-link <?= $menu === 'produk' ? 'active' : ''; ?>">
                            <p>🛍️ Produk</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl . '?menu=customer'); ?>"
                           class="nav-link <?= $menu === 'customer' ? 'active' : ''; ?>">
                            <p>📦 Data Customer</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl . '?menu=transaksi'); ?>"
                           class="nav-link <?= $menu === 'transaksi' ? 'active' : ''; ?>">
                            <p>🛒 Data Transaksi</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl . '?menu=target_sales'); ?>"
                           class="nav-link <?= $menu === 'target_sales' ? 'active' : ''; ?>">
                            <p>🎯 Target Penjualan</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl . '?menu=ulasan'); ?>"
                           class="nav-link <?= $menu === 'ulasan' ? 'active' : ''; ?>">
                            <p>⭐ Kelola Ulasan</p>
                        </a>
                    </li>

                    <li class="nav-item <?= $laporanAktif ? 'menu-open' : ''; ?>">
                        <a href="#" class="nav-link">
                            <p>🧾 Laporan
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= htmlspecialchars($dashboardUrl . '?menu=view_barang'); ?>"
                                   class="nav-link <?= $menu === 'view_barang' ? 'active' : ''; ?>">
                                    <p>Laporan Produk</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= htmlspecialchars($dashboardUrl . '?menu=view_customer'); ?>"
                                   class="nav-link <?= $menu === 'view_customer' ? 'active' : ''; ?>">
                                    <p>Laporan Customer</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= htmlspecialchars($dashboardUrl . '?menu=lap_transaksi'); ?>"
                                   class="nav-link <?= $menu === 'lap_transaksi' ? 'active' : ''; ?>">
                                    <p>Laporan Transaksi</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($dashboardUrl . '?menu=profil'); ?>"
                           class="nav-link <?= $menu === 'profil' ? 'active' : ''; ?>">
                            <p>👤 Profil</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= htmlspecialchars($logoutUrl); ?>" class="nav-link">
                            <p>🚪 Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="app-main">
        <div class="app-content">
            <div class="container-fluid">

                <?php
                switch ($menu) {

                    case 'home':
                        include __DIR__ . '/main_owner.php';
                        break;

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

                    case 'customer':
                        include __DIR__ . '/form/customer/view.php';
                        break;

                    case 'customer_form':
                        include __DIR__ . '/form/customer/form_customer.php';
                        break;

                    case 'transaksi':
                        include __DIR__ . '/form/transaksi/view.php';
                        break;

                    case 'transaksi_form':
                        include __DIR__ . '/form/transaksi/form_transaksi.php';
                        break;

                    case 'load_transaksi':
                        include __DIR__ . '/form/transaksi/load_transaksi.php';
                        break;

                    case 'target_sales':
                        include __DIR__ . '/form/owner/target_sales.php';
                        break;

                    case 'ulasan':
                        include __DIR__ . '/form/owner/ulasan.php';
                        break;

                    case 'view_barang':
                        include __DIR__ . '/laporan/view_barang.php';
                        break;

                    case 'view_customer':
                        include __DIR__ . '/laporan/view_customer.php';
                        break;

                    case 'lap_transaksi':
                        include __DIR__ . '/laporan/lap_transaksi.php';
                        break;

                    case 'edit_barang':
                        include __DIR__ . '/form/barang/edit.php';
                        break;

                    case 'edit_customer':
                        include __DIR__ . '/form/customer/edit.php';
                        break;

                    case 'edit_transaksi':
                        include __DIR__ . '/form/transaksi/edit.php';
                        break;

                    case 'hapus_barang':
                        include __DIR__ . '/form/barang/hapus.php';
                        break;

                    case 'hapus_customer':
                        include __DIR__ . '/form/customer/hapus.php';
                        break;

                    case 'hapus_transaksi':
                        include __DIR__ . '/form/transaksi/edit.php';
                        break;

                    case 'tambah_stok':
                        include __DIR__ . '/form/stok_barang/form_stok.php';
                        break;

                    case 'profil':
                        ?>
                        <div class="account-profile">
                            <div class="mb-4">
                                <h2 class="fw-bold mb-1">Profil Owner</h2>
                                <p class="text-secondary mb-0">
                                    Informasi akun pengelola Clairmont Dessert.
                                </p>
                            </div>

                            <div class="profile-banner mb-4">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="profile-avatar">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <span class="badge rounded-pill mb-2"
                                              style="background:#f3d7e8;color:#70445c;">
                                            OWNER ACCOUNT
                                        </span>
                                        <h3 class="mb-1">
                                            <?= htmlspecialchars($namaOwner, ENT_QUOTES, 'UTF-8'); ?>
                                        </h3>
                                        <span class="text-secondary">Owner Clairmont Dessert</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header p-3">
                                    <h5 class="mb-0 fw-semibold">Informasi Akun</h5>
                                </div>
                                <div class="card-body px-4">
                                    <div class="profile-row">
                                        <small class="text-secondary">Nama</small>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($namaOwner, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </div>
                                    <div class="profile-row">
                                        <small class="text-secondary">Email</small>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($emailOwner, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </div>
                                    <div class="profile-row">
                                        <small class="text-secondary">Role</small>
                                        <div class="fw-semibold">Owner</div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white p-3">
                                    <a href="<?= htmlspecialchars($logoutUrl); ?>"
                                       class="btn btn-outline-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i>
                                        Keluar dari Akun
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'logout':
                        header('Location: ' . $logoutUrl);
                        exit;

                    default:
                        include __DIR__ . '/main_owner.php';
                        break;
                }
                ?>

            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="app-footer">
        <strong>
            Copyright &copy; 2025-2026 Clairmont Dessert.
        </strong>
        All rights reserved.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('sidebarToggle');

    if (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-open');
        });
    }

    document.addEventListener('click', function (e) {
        if (
            window.innerWidth <= 991 &&
            document.body.classList.contains('sidebar-open') &&
            !e.target.closest('.app-sidebar') &&
            !e.target.closest('#sidebarToggle')
        ) {
            document.body.classList.remove('sidebar-open');
        }
    });

    const chartElement = document.querySelector('#sales-chart');

    if (chartElement && typeof ApexCharts !== 'undefined') {

        const options = {
            series: [{
                name: 'Total Penjualan',
                data: <?= json_encode($total ?: [0]); ?>
            }],
            chart: {
                height: 180,
                type: 'area',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            colors: ['#0d6efd'],
            xaxis: {
                type: 'datetime',
                categories: <?= json_encode($bulan ?: [date('Y-m-01')]); ?>
            },
            tooltip: {
                x: { format: 'MMMM yyyy' },
                y: {
                    formatter: function (val) {
                        return 'Rp ' + Number(val).toLocaleString('id-ID');
                    }
                }
            }
        };

        new ApexCharts(chartElement, options).render();
    }
});
</script>

</body>
</html>