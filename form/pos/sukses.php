
<?php
session_start();

/* =====================================
   VALIDASI LOGIN SALES
===================================== */
if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'sales'
) {
    header('Location: ../../login.php');
    exit;
}

/* =====================================
   AMBIL DATA TRANSAKSI
===================================== */
$sale = $_SESSION['pos_last_sale'] ?? null;

if (!$sale || !is_array($sale)) {
    header('Location: pos.php');
    exit;
}

/*
Data tidak langsung dihapus agar halaman struk
masih bisa ditampilkan ketika browser di-refresh.
*/

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rupiah($value) {
    return 'Rp ' . number_format((int)$value, 0, ',', '.');
}

$kode = $sale['kode'] ?? '-';
$total = (int)($sale['total'] ?? 0);
$metode = $sale['metode'] ?? '-';
$tanggal = $sale['tanggal'] ?? date('Y-m-d H:i:s');
$items = $sale['items'] ?? [];

$uangDiterima = $sale['uang_diterima'] ?? null;
$kembalian = $sale['kembalian'] ?? null;

$transferTerverifikasi =
    !empty($sale['pembayaran_terverifikasi']);

$namaMetode = [
    'Tunai' => 'Tunai',
    'QRIS' => 'QRIS (Simulasi)',
    'Transfer' => 'Transfer Bank'
];

$metodeTampil = $namaMetode[$metode] ?? $metode;

$timestamp = strtotime($tanggal);

$tanggalTampil = $timestamp
    ? date('d-m-Y H:i:s', $timestamp)
    : e($tanggal);

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Transaksi Berhasil | Clairmont Dessert</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

* {
    box-sizing: border-box;
}

body {
    background: #fff8fa;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #543d4c;
    padding: 25px 15px;
}

.receipt {
    max-width: 550px;
    margin: 35px auto;
    background: white;
    border: 1px solid #f0dce3;
    border-radius: 22px;
    padding: 35px;
    box-shadow: 0 10px 35px rgba(140,97,112,.10);
}

.success-icon {
    width: 75px;
    height: 75px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: #e4f5eb;
    color: #32945b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 43px;
    font-weight: bold;
}

.brand {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #67475d;
}

.subtitle {
    color: #a38b9a;
    font-size: 13px;
    letter-spacing: 2px;
}

.divider {
    border-top: 1px dashed #d9b5c6;
    margin: 22px 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 9px 0;
    font-size: 14px;
}

.info-row span {
    color: #927d8b;
}

.info-row strong {
    text-align: right;
    color: #493747;
}

.product-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin-top: 12px;
}

.product-table th {
    background: #fceaf0;
    padding: 10px 7px;
    text-align: left;
}

.product-table td {
    padding: 10px 7px;
    border-bottom: 1px solid #f1e4e9;
}

.product-table .right {
    text-align: right;
}

.total-box {
    background: linear-gradient(120deg,#fceaf0,#eaf1ff);
    padding: 20px;
    border-radius: 15px;
    margin-top: 20px;
}

.total-box strong {
    display: block;
    font-size: 26px;
    color: #b34f7b;
    margin-top: 5px;
}

.payment-box {
    border: 1px solid #f0dce3;
    border-radius: 15px;
    padding: 17px;
    margin-top: 16px;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 9px 0;
    font-size: 14px;
}

.payment-row span {
    color: #927d8b;
}

.payment-row strong {
    text-align: right;
}

.change-row {
    border-top: 1px dashed #d9b5c6;
    margin-top: 7px;
    padding-top: 13px;
}

.change-row strong {
    color: #b34f7b;
    font-size: 20px;
}

.status-ok {
    color: #32945b !important;
}

.status-warning {
    color: #b47a16 !important;
}

.note {
    background: #fff5d7;
    border: 1px solid #ffe39a;
    padding: 13px;
    border-radius: 10px;
    font-size: 13px;
    color: #80621b;
    margin-top: 18px;
}

.btn-clairmont {
    background: linear-gradient(120deg,#f1b4c5,#dceaff);
    color: #593e50;
    border: none;
    font-weight: 700;
    padding: 13px;
    border-radius: 12px;
}

.btn-clairmont:hover {
    color: #593e50;
    opacity: .9;
}

@media print {

    @page {
        size: 80mm auto;
        margin: 5mm;
    }

    body {
        background: white;
        padding: 0;
        color: black;
    }

    .receipt {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 5px;
        border: none;
        box-shadow: none;
        border-radius: 0;
    }

    .no-print {
        display: none !important;
    }

    .brand,
    .total-box strong,
    .change-row strong {
        color: black;
    }

    .total-box {
        background: white;
        border: 1px dashed #777;
    }

    .payment-box {
        border: 1px solid #aaa;
    }

    .success-icon {
        background: white;
        color: black;
        border: 1px solid #aaa;
    }
}

</style>

</head>

<body>

<div class="receipt">

    <!-- HEADER -->
    <div class="text-center">

        <div class="success-icon">✓</div>

        <div class="brand">
            🍰 CLAIRMONT DESSERT
        </div>

        <div class="subtitle">
            POINT OF SALE
        </div>

        <h3 class="fw-bold mt-4">
            Transaksi Berhasil!
        </h3>

        <p class="text-secondary mb-0">
            Terima kasih telah melakukan transaksi.
        </p>

    </div>

    <div class="divider"></div>

    <!-- INFORMASI TRANSAKSI -->
    <h6 class="fw-bold mb-3">
        INFORMASI TRANSAKSI
    </h6>

    <div class="info-row">
        <span>Kode Transaksi</span>
        <strong><?= e($kode) ?></strong>
    </div>

    <div class="info-row">
        <span>Tanggal</span>
        <strong><?= e($tanggalTampil) ?></strong>
    </div>

    <div class="info-row">
        <span>Customer</span>
        <strong>Pembeli Umum</strong>
    </div>

    <div class="info-row">
        <span>Metode Pembayaran</span>
        <strong><?= e($metodeTampil) ?></strong>
    </div>

    <div class="divider"></div>

    <!-- RINCIAN PEMBELIAN -->
    <h6 class="fw-bold mb-2">
        RINCIAN PEMBELIAN
    </h6>

    <?php if (!empty($items)) { ?>

        <table class="product-table">

            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($items as $item) { ?>

                    <tr>

                        <td>
                            <?= e($item['nama'] ?? '-') ?>

                            <br>

                            <small class="text-secondary">
                                <?= rupiah($item['harga'] ?? 0) ?>/item
                            </small>
                        </td>

                        <td class="right">
                            <?= (int)($item['jumlah'] ?? 0) ?>
                        </td>

                        <td class="right">
                            <?= rupiah($item['subtotal'] ?? 0) ?>
                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

    <?php } else { ?>

        <p class="text-secondary small">
            Rincian produk belum tersedia.
        </p>

    <?php } ?>

    <!-- TOTAL -->
    <div class="total-box">

        <div class="small fw-semibold">
            TOTAL PEMBAYARAN
        </div>

        <strong>
            <?= rupiah($total) ?>
        </strong>

    </div>

    <!-- PEMBAYARAN TUNAI -->
    <?php if ($metode === 'Tunai') { ?>

        <div class="payment-box">

            <h6 class="fw-bold mb-2">
                RINCIAN PEMBAYARAN TUNAI
            </h6>

            <div class="payment-row">
                <span>Total Belanja</span>
                <strong><?= rupiah($total) ?></strong>
            </div>

            <div class="payment-row">
                <span>Uang Diterima</span>
                <strong>
                    <?= $uangDiterima !== null
                        ? rupiah($uangDiterima)
                        : 'Belum tercatat' ?>
                </strong>
            </div>

            <div class="payment-row change-row">
                <span>Kembalian</span>
                <strong>
                    <?= $kembalian !== null
                        ? rupiah($kembalian)
                        : 'Belum tercatat' ?>
                </strong>
            </div>

        </div>

    <?php } ?>

    <!-- PEMBAYARAN TRANSFER -->
    <?php if ($metode === 'Transfer') { ?>

        <div class="payment-box">

            <h6 class="fw-bold mb-2">
                KONFIRMASI TRANSFER BANK
            </h6>

            <div class="payment-row">

                <span>Status Pembayaran</span>

                <?php if ($transferTerverifikasi) { ?>

                    <strong class="status-ok">
                        ✓ Dana telah dikonfirmasi
                    </strong>

                <?php } else { ?>

                    <strong class="status-warning">
                        Belum terverifikasi
                    </strong>

                <?php } ?>

            </div>

            <div class="payment-row">
                <span>Total Transfer</span>
                <strong><?= rupiah($total) ?></strong>
            </div>

        </div>

    <?php } ?>

    <!-- QRIS -->
    <?php if ($metode === 'QRIS') { ?>

        <div class="note">
            <strong>QRIS MODE SIMULASI</strong><br>
            Transaksi ini merupakan simulasi pembayaran.
            Struk ini bukan bukti pembayaran QRIS merchant
            yang telah terverifikasi.
        </div>

    <?php } ?>

    <div class="divider"></div>

    <!-- FOOTER -->
    <div class="text-center">

        <p class="fw-bold mb-1">
            Terima Kasih!
        </p>

        <small class="text-secondary">
            Semoga hari Anda menyenangkan.
        </small>

        <p class="small mt-3 mb-0">
            Simpan bukti transaksi ini sebagai referensi.
        </p>

    </div>

    <!-- TOMBOL -->
    <div class="no-print mt-4">

        <button
            onclick="window.print()"
            class="btn btn-clairmont w-100 mb-2">

            🖨️ Cetak Struk

        </button>

        <a href="pos.php"
           class="btn btn-outline-secondary w-100 mb-2">

            ← Kembali ke POS

        </a>

    </div>

</div>

</body>
</html>