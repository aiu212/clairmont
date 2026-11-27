
<?php
session_start();
require_once __DIR__ . '/koneksi2.php';

/* =========================
   VALIDASI LOGIN
========================= */
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'sales'
) {
    header("Location: login.php");
    exit;
}

mysqli_set_charset($koneksi, 'utf8mb4');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$namaTampilan = 'Sales Clairmont';

$emailTampilan = isset($_SESSION['email'])
    ? htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8')
    : '-';

$menu = isset($_GET['menu']) ? $_GET['menu'] : 'home';

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

    <title>Clairmont Dessert | Dashboard Sales</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="dist/css/adminlte.css">

    <style>
        :root {
            --pink: #f3b6c2;
            --lavender: #dceaff;
            --dark: #493747;
        }

        body {
            font-family: 'Source Sans 3', sans-serif;
        }

        .app-header {
            background: linear-gradient(110deg, #f7d4dc, #dceaff) !important;
        }

        .app-sidebar {
            background: #f8d9e0 !important;
        }

        .sidebar-brand {
            background: #f8d9e0 !important;
        }

        .sidebar-menu .nav-link {
            border-radius: 14px;
            margin: 4px 8px;
            font-weight: 600;
            transition: .2s;
        }

        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: linear-gradient(135deg, #f3b6c2, #e6f0ff);
            box-shadow: 0 5px 15px rgba(0,0,0,.10);
        }

        .sidebar-menu .nav-treeview .nav-link {
            margin-left: 15px;
            font-size: 14px;
        }

        .dashboard-title {
            color: var(--dark);
            font-weight: 800;
        }

        .profile-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #7c3aed;
            color: white;
        }

        .app-footer {
            background: linear-gradient(110deg, #f7d4dc, #dceaff) !important;
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
                    <a class="nav-link"
                       data-lte-toggle="sidebar"
                       href="#">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">

                <!-- USER MENU -->
                <li class="nav-item dropdown">

                    <a href="#"
                       class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       data-bs-toggle="dropdown">

                        <div class="profile-circle">
                            <i class="bi bi-person-fill fs-4"></i>
                        </div>

                        <span class="fw-semibold text-dark">
                            Sales Clairmont
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

                            <strong>Sales Clairmont</strong>

                            <div class="small">
                                <?= $emailTampilan ?>
                            </div>

                            <span class="badge bg-success mt-2">
                                Sales
                            </span>

                        </li>

                        <li class="p-2 d-flex justify-content-between">

                            <a href="dashboard_admin.php?menu=profil"
                               class="btn btn-outline-secondary btn-sm">
                                Profil
                            </a>

                            <a href="logout.php"
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

            <a href="dashboard_admin.php"
               class="brand-link text-decoration-none">

                <img src="dist/assets/img/logo.jpeg"
                     alt="Clairmont"
                     class="brand-image opacity-75 shadow">

                <span class="brand-text fw-semibold">
                    Clairmont Dessert
                </span>

            </a>

        </div>

        <div class="sidebar-wrapper">

            <nav class="mt-2">
                <?php include "menu_admin.php"; ?>
            </nav>

        </div>

    </aside>

    <!-- CONTENT -->
    <main class="app-main">

        <?php

        switch ($menu) {

            case 'home':
                include "main_admin.php";
                break;

            /* PRODUK */
            case 'produk':
                include "form/barang/view.php";
                break;

            case 'barang_form':
                include "form/barang/form_barang.php";
                break;

            case 'load_produk':
                include "form/barang/load_produk.php";
                break;

            case 'cart_add':
                include "form/barang/cart_add.php";
                break;

            case 'cart':
                include "form/barang/cart.php";
                break;

            case 'cart_update':
                include "form/barang/cart_update.php";
                break;

            case 'checkout':
                include "form/barang/checkout.php";
                break;

            case 'checkout_proses':
                include "form/barang/checkout_proses.php";
                break;

            case 'checkout_sukses':
                include "form/barang/checkout_sukses.php";
                break;

            case 'checkout_info':
                include "form/barang/checkout_info.php";
                break;

            case 'pesanan':
                include "form/barang/pesanan.php";
                break;

            case 'pesanan_detail':
                include "form/barang/pesanan_detail.php";
                break;

            case 'invoice_pdf':
                include "form/barang/invoice_pdf.php";
                break;

            case 'profil':
                include "form/barang/profil.php";
                break;

            /* CUSTOMER */
            case 'customer':
                include "form/customer/view.php";
                break;

            case 'customer_form':
                include "form/customer/form_customer.php";
                break;

            /* TRANSAKSI */
            case 'transaksi':
                include "form/transaksi/view.php";
                break;

            case 'transaksi_form':
                include "form/transaksi/form_transaksi.php";
                break;

            case 'load_transaksi':
                include "form/transaksi/load_transaksi.php";
                break;

            case 'kelola_transaksi':
                include "form/transaksi/kelola_transaksi.php";
                break;

            /* LAPORAN */
            case 'view_barang':
                include "laporan/view_barang.php";
                break;

            case 'view_customer':
                include "laporan/view_customer.php";
                break;

            case 'lap_transaksi':
                include "laporan/lap_transaksi.php";
                break;

            /* EDIT */
            case 'produk_edit':
                include "form/barang/edit.php";
                break;

            case 'edit_customer':
                include "form/customer/edit.php";
                break;

            case 'edit_transaksi':
                include "form/transaksi/edit.php";
                break;

            /* HAPUS */
            case 'hapus_barang':
                include "form/barang/hapus.php";
                break;

            case 'hapus_customer':
                include "form/customer/hapus.php";
                break;

            case 'hapus_transaksi':
                include "form/transaksi/edit.php";
                break;

            /* STOK */
            case 'tambah_stok':
                include "form/stok_barang/form_stok.php";
                break;
                
            /* SALES */
            case 'target_sales':
                include "form/sales/target_sales.php";
                break;

            default:
                include "main_admin.php";
                break;
        }

        ?>

    </main>

    <!-- FOOTER -->
    <footer class="app-footer">

        <strong>
            Copyright &copy; 2025-2026
            <span class="text-decoration-none">
                Clairmont Dessert
            </span>.
        </strong>

        All rights reserved.

    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

<script src="dist/js/adminlte.js"></script>

</body>
</html>
