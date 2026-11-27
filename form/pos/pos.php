<?php
session_start();
require_once __DIR__ . '/../../koneksi2.php';

/* ===============================
   VALIDASI LOGIN SALES
================================ */
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'sales'
) {
    header('Location: ../../login.php');
    exit;
}

mysqli_set_charset($koneksi, 'utf8mb4');

/* ===============================
   SESSION KERANJANG & CSRF
================================ */
if (!isset($_SESSION['pos_cart']) || !is_array($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = array();
}

if (empty($_SESSION['pos_csrf'])) {
    $_SESSION['pos_csrf'] = bin2hex(random_bytes(16));
}

/* ===============================
   FUNGSI ESCAPE HTML
================================ */
function pos_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* ===============================
   PROSES KERANJANG
================================ */
$flash = '';
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {

    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (!hash_equals($_SESSION['pos_csrf'], $token)) {

        $flash = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
        $flashType = 'danger';

    } else {

        $action = $_POST['cart_action'];
        $idProduk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;

        /* TAMBAH PRODUK */
        if ($action === 'add' && $idProduk > 0) {

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id_produk, nama_produk, harga, stok
                 FROM tb_produk
                 WHERE id_produk = ? AND status = 'aktif'"
            );

            mysqli_stmt_bind_param($stmt, 'i', $idProduk);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            if (!$product) {

                $flash = 'Produk tidak ditemukan atau sedang nonaktif.';
                $flashType = 'danger';

            } else {

                $currentQty = isset($_SESSION['pos_cart'][$idProduk])
                    ? (int)$_SESSION['pos_cart'][$idProduk]
                    : 0;

                if ($currentQty + 1 > (int)$product['stok']) {

                    $flash = 'Jumlah melebihi stok yang tersedia.';
                    $flashType = 'warning';

                } else {

                    $_SESSION['pos_cart'][$idProduk] = $currentQty + 1;

                    $flash = $product['nama_produk'] . ' ditambahkan ke keranjang.';
                }
            }

        }

        /* UPDATE JUMLAH */
        elseif ($action === 'update' && $idProduk > 0) {

            $qty = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

            if ($qty <= 0) {

                unset($_SESSION['pos_cart'][$idProduk]);

                $flash = 'Produk dihapus dari keranjang.';

            } else {

                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT stok FROM tb_produk
                     WHERE id_produk = ? AND status = 'aktif'"
                );

                mysqli_stmt_bind_param($stmt, 'i', $idProduk);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);

                mysqli_stmt_close($stmt);

                if (!$product || $qty > (int)$product['stok']) {

                    $flash = 'Jumlah tidak valid atau melebihi stok.';
                    $flashType = 'warning';

                } else {

                    $_SESSION['pos_cart'][$idProduk] = $qty;

                    $flash = 'Keranjang berhasil diperbarui.';
                }
            }

        }

        /* HAPUS PRODUK */
        elseif ($action === 'remove' && $idProduk > 0) {

            unset($_SESSION['pos_cart'][$idProduk]);

            $flash = 'Produk dihapus dari keranjang.';

        }

        /* KOSONGKAN KERANJANG */
        elseif ($action === 'clear') {

            $_SESSION['pos_cart'] = array();

            $flash = 'Keranjang dikosongkan.';
        }
    }
}

/* ===============================
   PENCARIAN PRODUK
================================ */
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($keyword !== '') {

    $like = '%' . $keyword . '%';

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, kode_produk, nama_produk, harga, stok, gambar
         FROM tb_produk
         WHERE status = 'aktif'
         AND nama_produk LIKE ?
         ORDER BY nama_produk ASC"
    );

    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);

    $productsResult = mysqli_stmt_get_result($stmt);

} else {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, kode_produk, nama_produk, harga, stok, gambar
         FROM tb_produk
         WHERE status = 'aktif'
         ORDER BY nama_produk ASC"
    );

    mysqli_stmt_execute($stmt);

    $productsResult = mysqli_stmt_get_result($stmt);
}

$products = array();

while ($row = mysqli_fetch_assoc($productsResult)) {
    $products[] = $row;
}

mysqli_stmt_close($stmt);

/* ===============================
   DATA KERANJANG
================================ */
$cartRows = array();
$cartTotal = 0;

if (!empty($_SESSION['pos_cart'])) {

    $ids = array_map('intval', array_keys($_SESSION['pos_cart']));
    $idList = implode(',', $ids);

    $cartResult = mysqli_query(
        $koneksi,
        "SELECT id_produk, nama_produk, harga, stok
         FROM tb_produk
         WHERE id_produk IN ($idList)
         AND status = 'aktif'"
    );

    while ($row = mysqli_fetch_assoc($cartResult)) {

        $qty = (int)$_SESSION['pos_cart'][$row['id_produk']];

        $row['jumlah'] = $qty;
        $row['subtotal'] = (int)$row['harga'] * $qty;

        $cartTotal += $row['subtotal'];

        $cartRows[] = $row;
    }
}

/* JUMLAH ITEM */
$cartCount = 0;

foreach ($_SESSION['pos_cart'] as $qty) {
    $cartCount += (int)$qty;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>POS Kasir | Clairmont Dessert</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../dist/css/adminlte.css">
<style>


/* SIDEBAR POS */
.sidebar-pos {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: #f7d9e1;
    padding: 20px 15px;
    overflow-y: auto;
    z-index: 1050;
}

.sidebar-pos .sidebar-brand {
    font-size: 19px;
    font-weight: 800;
    color: #553b4a;
    padding: 10px 8px 30px;
}

.sidebar-pos .nav-link {
    display: block;
    color: #433744;
    padding: 13px 15px;
    margin-bottom: 7px;
    border-radius: 12px;
    text-decoration: none;
}

.sidebar-pos .nav-link:hover {
    background: rgba(255,255,255,.55);
}

.sidebar-pos .nav-link.active {
    background: linear-gradient(100deg, #f2b6ca, #dce7ff);
    font-weight: 700;
}

.sidebar-pos .nav-treeview {
    padding-left: 15px;
}

.sidebar-pos .nav-treeview .nav-link {
    font-size: 14px;
    padding: 10px;
}

/* KONTEN POS */
.topbar {
    margin-left: 250px;
}

.pos-main {
    margin-left: 250px;
}

/* RESPONSIVE */
@media (max-width: 767px) {
    .sidebar-pos {
        width: 200px;
    }

    .topbar {
        margin-left: 200px;
    }

    .pos-main {
        margin-left: 200px;
    }
}


:root {
    --pink: #f3b6c2;
    --pink-dark: #bd6689;
    --lav: #e6f0ff;
    --ink: #433744;
    --soft: #fff8fa;
}

/* SAMAKAN TAMPILAN DENGAN DASHBOARD SALES */

/* Background halaman */
body {
    background: #f4f6f9 !important;
    color: #343a40;
    font-family: 'Source Sans 3', sans-serif;
}

/* Sidebar */
.app-sidebar {
    background: #f8d7df !important;
}

.sidebar-brand {
    background: #f8d7df !important;
    border-bottom: 1px solid rgba(0,0,0,.05);
}

/* Header */
.app-header {
    background: linear-gradient(110deg, #f7d4dc, #dceaff) !important;
    border-bottom: 1px solid #efd0da;
}

/* Konten utama */
.app-main {
    background: #f4f6f9;
}

/* Judul halaman */
.page-title {
    color: #343a40;
}

.topbar {
    background: linear-gradient(110deg, #f7d4dc, #dceaff);
    border-bottom: 1px solid #efd0da;
}

.brand {
    font-weight: 800;
    letter-spacing: .3px;
    color: #553b4a;
}

.page-title {
    font-weight: 800;
    color: #60475a;
}

.panel {
    background: #fff;
    border: 1px solid #f0dce3;
    border-radius: 18px;
    box-shadow: 0 7px 22px rgba(100,65,85,.07);
}

.product-card {
    border: 1px solid #f0dce3;
    border-radius: 16px;
    overflow: hidden;
    height: 100%;
    transition: .2s;
    background: #fff;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 9px 20px rgba(100,65,85,.1);
}

.product-thumb {
    height: 125px;
    background: linear-gradient(135deg,#fde7ed,#edf4ff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b66c8c;
    font-size: 2.4rem;
}

.product-thumb img {
    width: 100%;
    height: 125px;
    object-fit: cover;
}

.btn-clair {
    background: linear-gradient(120deg,#f1b4c5,#dceaff);
    border: 0;
    color: #593e50;
    font-weight: 700;
    border-radius: 11px;
}

.btn-clair:hover {
    filter: brightness(.97);
    color: #593e50;
}

.price {
    color: #a84f77;
    font-weight: 800;
}

.muted {
    color: #8d7c88;
}

.table thead th {
    background: #fbe8ee;
    color: #65485a;
    border-bottom: 0;
}

.total-box {
    background: linear-gradient(120deg,#fff0f4,#eef5ff);
    border-radius: 14px;
    padding: 16px;
}

.form-control,
.form-select {
    border-color: #ead5df;
    border-radius: 10px;
}

.badge-stock {
    background: #edf7f0;
    color: #34734c;
}

/* ===============================
   PAYMENT BOX
================================ */

.payment-box {
    background: linear-gradient(120deg,#fff0f4,#eef5ff);
    border: 1px solid #f0dce3;
    border-radius: 14px;
    padding: 15px;
}

.payment-box .form-check-input:checked {
    background-color: #bd6689;
    border-color: #bd6689;
}

.payment-box .form-control:focus,
.payment-box .form-select:focus {
    border-color: #bd6689;
    box-shadow: 0 0 0 .2rem rgba(189,102,137,.15);
}

/* KEMBALIAN */

#jumlahKembalian {
    font-size: 18px;
}

/* QRIS */

.qris-placeholder {
    background: #fff;
    border: 2px dashed #d9a9bd;
    border-radius: 14px;
    padding: 28px 18px;
    text-align: center;
}

.qris-icon {
    font-size: 48px;
    color: #bd6689;
}

@media(max-width:767px) {

    .product-thumb,
    .product-thumb img {
        height: 100px;
    }
}

</style>

</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">


<!-- ===============================
     HEADER
================================ -->


<nav class="app-header navbar navbar-expand px-3 px-lg-4 py-3">

    <a href="../../dashboard_admin.php"
       class="navbar-brand brand mb-0 text-decoration-none">

        🍰 CLAIRMONT DESSERT

        <small class="d-block fw-normal muted"
               style="font-size:11px">
            POINT OF SALE
        </small>

    </a>

    <div class="text-end">

        <div class="fw-bold">
            Sales Clairmont
        </div>

        <small class="muted">
            Sales • <?= (int)$_SESSION['id_user']; ?>
        </small>

    </div>

</nav>

<aside class="app-sidebar" data-bs-theme="light">
    <div class="sidebar-brand">
        <a href="../../dashboard_admin.php" class="brand-link text-decoration-none">
            <img src="../../dist/assets/img/logo.jpeg"
                 alt="Clairmont"
                 class="brand-image opacity-75 shadow">
            <span class="brand-text fw-semibold">Clairmont Dessert</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <?php include __DIR__ . '/../../menu_admin.php'; ?>
        </nav>
    </div>
</aside>

<main class="app-main">
    <div class="container-fluid px-3 px-lg-4 py-4">

    <!-- JUDUL HALAMAN -->

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

        <div>

            <h2 class="page-title mb-1">
                POS / Kasir
            </h2>

            <div class="muted">
                Pilih produk, masukkan ke keranjang, lalu proses pembayaran.
            </div>

        </div>

        <a href="../../dashboard_admin.php"
           class="btn btn-outline-secondary">
            ← Dashboard Sales
        </a>

    </div>


    <!-- NOTIFIKASI -->

    <?php if ($flash !== '') { ?>

        <div class="alert alert-<?php echo pos_h($flashType); ?> alert-dismissible fade show"
             role="alert">

            <?php echo pos_h($flash); ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    <?php } ?>


    <div class="row g-4">

        <!-- ===============================
             DAFTAR PRODUK
        ================================ -->

        <section class="col-xl-8">

            <div class="panel p-3 p-lg-4">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

                    <div>

                        <h5 class="fw-bold mb-0">
                            Daftar Produk
                        </h5>

                        <small class="muted">
                            Produk aktif dari database Clairmont
                        </small>

                    </div>

                    <!-- PENCARIAN -->

                    <form method="get"
                          class="d-flex gap-2"
                          style="max-width:340px;width:100%">

                        <input class="form-control"
                               type="search"
                               name="q"
                               value="<?php echo pos_h($keyword); ?>"
                               placeholder="Cari nama produk...">

                        <button class="btn btn-clair px-3">
                            Cari
                        </button>

                    </form>

                </div>


                <div class="row g-3">

                    <?php if (count($products) === 0) { ?>

                        <div class="col-12">

                            <div class="text-center muted py-5">
                                Produk tidak ditemukan.
                            </div>

                        </div>

                    <?php } ?>


                    <?php foreach ($products as $p) { ?>

                        <div class="col-6 col-md-4 col-lg-3">

                            <div class="product-card">

                                <div class="product-thumb">

                                    <?php if (!empty($p['gambar'])) { ?>

                                        <img
                                            src="../../<?php echo pos_h(ltrim($p['gambar'], '/')); ?>"
                                            alt="<?php echo pos_h($p['nama_produk']); ?>"
                                            onerror="this.style.display='none';this.parentNode.innerHTML='🧁';">

                                    <?php } else { ?>

                                        🧁

                                    <?php } ?>

                                </div>


                                <div class="p-3">

                                    <div class="fw-bold mb-1">
                                        <?php echo pos_h($p['nama_produk']); ?>
                                    </div>

                                    <div class="price mb-1">
                                        Rp <?php echo number_format((int)$p['harga'],0,',','.'); ?>
                                    </div>

                                    <div class="mb-3">

                                        <span class="badge badge-stock">
                                            Stok: <?php echo (int)$p['stok']; ?>
                                        </span>

                                    </div>


                                    <!-- TAMBAH KERANJANG -->

                                    <form method="post">

                                        <input type="hidden"
                                               name="csrf_token"
                                               value="<?php echo pos_h($_SESSION['pos_csrf']); ?>">

                                        <input type="hidden"
                                               name="cart_action"
                                               value="add">

                                        <input type="hidden"
                                               name="id_produk"
                                               value="<?php echo (int)$p['id_produk']; ?>">

                                        <button class="btn btn-clair w-100 btn-sm"
                                                type="submit"
                                                <?php echo ((int)$p['stok'] < 1) ? 'disabled' : ''; ?>>

                                            + Tambah

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            </div>

        </section>


        <!-- ===============================
             KERANJANG
        ================================ -->

        <aside class="col-xl-4">

            <div class="panel p-3 p-lg-4 position-sticky"
                 style="top:16px">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h5 class="fw-bold mb-0">
                        🛒 Keranjang
                    </h5>

                    <span class="badge rounded-pill text-bg-secondary">
                        <?php echo $cartCount; ?> item
                    </span>

                </div>


                <?php if (count($cartRows) === 0) { ?>

                    <div class="text-center py-5 muted">

                        Keranjang masih kosong.

                        <br>

                        <span style="font-size:2rem">
                            🛍️
                        </span>

                    </div>

                <?php } else { ?>


                    <!-- TABEL KERANJANG -->

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>

                                <tr>
                                    <th>Produk</th>
                                    <th style="width:85px">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($cartRows as $item) { ?>

                                    <tr>

                                        <td>

                                            <div class="fw-semibold">
                                                <?php echo pos_h($item['nama_produk']); ?>
                                            </div>

                                            <small class="muted">
                                                Rp <?php echo number_format((int)$item['harga'],0,',','.'); ?>
                                            </small>


                                            <!-- HAPUS PRODUK -->

                                            <form method="post" class="mt-1">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?php echo pos_h($_SESSION['pos_csrf']); ?>">

                                                <input type="hidden"
                                                       name="cart_action"
                                                       value="remove">

                                                <input type="hidden"
                                                       name="id_produk"
                                                       value="<?php echo (int)$item['id_produk']; ?>">

                                                <button class="btn btn-link btn-sm text-danger p-0"
                                                        type="submit">

                                                    Hapus

                                                </button>

                                            </form>

                                        </td>


                                        <!-- UPDATE JUMLAH -->

                                        <td>

                                            <form method="post">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?php echo pos_h($_SESSION['pos_csrf']); ?>">

                                                <input type="hidden"
                                                       name="cart_action"
                                                       value="update">

                                                <input type="hidden"
                                                       name="id_produk"
                                                       value="<?php echo (int)$item['id_produk']; ?>">

                                                <input class="form-control form-control-sm"
                                                       type="number"
                                                       name="jumlah"
                                                       min="0"
                                                       max="<?php echo (int)$item['stok']; ?>"
                                                       value="<?php echo (int)$item['jumlah']; ?>"
                                                       onchange="this.form.submit()">

                                            </form>

                                        </td>


                                        <td class="text-end fw-semibold">

                                            Rp <?php echo number_format((int)$item['subtotal'],0,',','.'); ?>

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- TOTAL -->

                    <div class="total-box mb-3">

                        <div class="d-flex justify-content-between align-items-center">

                            <span class="fw-semibold">
                                Total pembayaran
                            </span>

                            <strong class="price fs-4">

                                Rp <?php echo number_format($cartTotal,0,',','.'); ?>

                            </strong>

                        </div>

                    </div>


                    <!-- ===============================
                         FORM PEMBAYARAN
                    ================================ -->

                    <form method="post"
                          action="checkout_proses.php"
                          id="formPembayaran"
                          data-total="<?php echo (int)$cartTotal; ?>">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?php echo pos_h($_SESSION['pos_csrf']); ?>">


                        <!-- PEMBELI -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Pembeli
                            </label>

                            <input class="form-control"
                                   value="Pembeli Umum"
                                   readonly>

                            <small class="muted">
                                Transaksi kasir dicatat sebagai Pembeli Umum.
                            </small>

                        </div>


                        <!-- METODE PEMBAYARAN -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Metode Pembayaran
                            </label>

                            <select class="form-select"
                                    name="metode_pembayaran"
                                    id="metodePembayaran"
                                    required>

                                <option value="Tunai">
                                    💵 Tunai
                                </option>

                                <option value="QRIS">
                                    📱 QRIS
                                </option>

                                <option value="Transfer">
                                    🏦 Transfer Bank
                                </option>

                            </select>

                        </div>


                        <!-- ===============================
                             PEMBAYARAN TUNAI
                        ================================ -->

                        <div id="areaTunai"
                             class="payment-box mb-3">

                            <label class="form-label fw-semibold">
                                Uang Diterima (Rp)
                            </label>

                            <input type="number"
                                   class="form-control"
                                   name="uang_diterima"
                                   id="uangDiterima"
                                   min="0"
                                   placeholder="Masukkan nominal uang">


                            <div class="mt-3">

                                <div class="d-flex justify-content-between">

                                    <span>
                                        Total Belanja
                                    </span>

                                    <strong>
                                        Rp <?php echo number_format($cartTotal,0,',','.'); ?>
                                    </strong>

                                </div>


                                <div class="d-flex justify-content-between mt-2">

                                    <span>
                                        Kembalian
                                    </span>

                                    <strong class="price"
                                            id="jumlahKembalian">

                                        Rp 0

                                    </strong>

                                </div>

                            </div>

                        </div>


                        <!-- ===============================
                             QRIS SIMULASI
                        ================================ -->

                        <div id="areaQRIS"
                             class="payment-box mb-3"
                             style="display:none;">

                            <div class="text-center mb-3">

                                <div style="font-size:42px;">
                                    📱
                                </div>

                                <h6 class="fw-bold">
                                    Pembayaran QRIS
                                </h6>

                                <span class="badge bg-warning text-dark">
                                    MODE SIMULASI
                                </span>

                            </div>


                            <div class="qris-placeholder">

                                <div class="qris-icon">
                                    ▦
                                </div>

                                <strong>
                                    QRIS Clairmont
                                </strong>

                                <p class="muted small mt-2 mb-0">

                                    QRIS merchant belum tersedia.
                                    Kode QR resmi akan ditampilkan di area ini
                                    setelah Clairmont memiliki QRIS merchant.

                                </p>

                            </div>


                            <div class="alert alert-warning small mt-3 mb-0">

                                Pembayaran QRIS belum dapat diproses secara nyata.
                                Jangan menganggap transaksi sudah lunas.

                            </div>

                        </div>


                        <!-- ===============================
                             TRANSFER BANK
                        ================================ -->

                        <div id="areaTransfer"
                             class="payment-box mb-3"
                             style="display:none;">

                            <div class="fw-semibold mb-2">
                                🏦 Konfirmasi Transfer Bank
                            </div>

                            <p class="muted small">

                                Periksa mutasi rekening dan pastikan dana
                                sudah masuk ke rekening toko.

                            </p>


                            <div class="form-check">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="pembayaran_terverifikasi"
                                       value="1"
                                       id="konfirmasiTransfer">

                                <label class="form-check-label"
                                       for="konfirmasiTransfer">

                                    Saya telah memastikan dana transfer
                                    benar-benar sudah diterima.

                                </label>

                            </div>

                        </div>


                        <!-- TOMBOL PROSES -->

                        <button type="submit"
                                class="btn btn-clair w-100 py-2 fw-bold"
                                id="tombolBayar"
                                <?php echo ($cartTotal <= 0) ? 'disabled' : ''; ?>>

                            Proses Pembayaran

                        </button>

                    </form>


                    <!-- KOSONGKAN KERANJANG -->

                    <form method="post"
                          class="mt-2"
                          onsubmit="return confirm('Kosongkan seluruh keranjang?');">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?php echo pos_h($_SESSION['pos_csrf']); ?>">

                        <input type="hidden"
                               name="cart_action"
                               value="clear">

                        <button class="btn btn-outline-secondary w-100 btn-sm">

                            Kosongkan Keranjang

                        </button>

                    </form>


                <?php } ?>

            </div>

        </aside>

       </div>
</main>
</div>


<!-- BOOTSTRAP -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../../dist/js/adminlte.js"></script>

<!-- ===============================
     JAVASCRIPT PEMBAYARAN
================================ -->

<script>

const metode = document.getElementById('metodePembayaran');

const areaTunai = document.getElementById('areaTunai');
const areaQRIS = document.getElementById('areaQRIS');
const areaTransfer = document.getElementById('areaTransfer');

const uang = document.getElementById('uangDiterima');

const konfirmasi = document.getElementById('konfirmasiTransfer');

const kembalian = document.getElementById('jumlahKembalian');

const formBayar = document.getElementById('formPembayaran');

const tombolBayar = document.getElementById('tombolBayar');


if (formBayar) {

    const total = Number(formBayar.dataset.total);


    /* FORMAT RUPIAH */

    function formatRupiah(angka) {

        return 'Rp ' + angka.toLocaleString('id-ID');

    }


    /* HITUNG KEMBALIAN */

    function hitungKembalian() {

        const nominal = Number(uang.value) || 0;

        const hasil = nominal - total;

        kembalian.textContent =
            formatRupiah(Math.max(0, hasil));

    }


    /* ATUR METODE PEMBAYARAN */

    function aturPembayaran() {

        const pilihan = metode.value;


        /* RESET KONFIRMASI TRANSFER */

        if (pilihan !== 'Transfer') {

            konfirmasi.checked = false;

        }


        /* TAMPILKAN PANEL SESUAI METODE */

        areaTunai.style.display =
            pilihan === 'Tunai' ? 'block' : 'none';

        areaQRIS.style.display =
            pilihan === 'QRIS' ? 'block' : 'none';

        areaTransfer.style.display =
            pilihan === 'Transfer' ? 'block' : 'none';


        /* INPUT UANG TUNAI */

        uang.required = pilihan === 'Tunai';

        uang.disabled = pilihan !== 'Tunai';


        /* KONFIRMASI TRANSFER */

        konfirmasi.required = pilihan === 'Transfer';

        konfirmasi.disabled = pilihan !== 'Transfer';


        /* QRIS BELUM TERSEDIA */

        tombolBayar.disabled =
            pilihan === 'QRIS' || total <= 0;


        if (pilihan === 'QRIS') {

            tombolBayar.textContent =
                'QRIS Belum Tersedia';

        } else {

            tombolBayar.textContent =
                'Proses Pembayaran';

        }


        hitungKembalian();

    }


    /* EVENT PERUBAHAN METODE */

    metode.addEventListener('change', aturPembayaran);


    /* EVENT INPUT UANG */

    uang.addEventListener('input', hitungKembalian);


    /* VALIDASI SEBELUM CHECKOUT */

    formBayar.addEventListener('submit', function(e) {


        /* QRIS TIDAK BISA DIPROSES */

        if (metode.value === 'QRIS') {

            e.preventDefault();

            alert(
                'QRIS Clairmont masih dalam mode simulasi dan belum dapat menerima pembayaran nyata.'
            );

            return;

        }


        /* VALIDASI TUNAI */

        if (
            metode.value === 'Tunai' &&
            (uang.value === '' || Number(uang.value) < total)
        ) {

            e.preventDefault();

            alert('Uang yang diterima belum mencukupi!');

            uang.focus();

            return;

        }


        /* VALIDASI TRANSFER */

        if (
            metode.value === 'Transfer' &&
            !konfirmasi.checked
        ) {

            e.preventDefault();

            alert('Pastikan transfer sudah diterima!');

            return;

        }


        /* KONFIRMASI TRANSAKSI */

        if (!confirm('Lanjutkan transaksi?')) {

            e.preventDefault();

        }

    });


    /* JALANKAN SAAT HALAMAN DIBUKA */

    aturPembayaran();

}

</script>

</body>
</html>