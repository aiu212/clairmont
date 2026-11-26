<?php
session_start();
require_once __DIR__ . '/../../koneksi2.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'sales') {
    header('Location: ../../login.php');
    exit;
}

mysqli_set_charset($koneksi, 'utf8mb4');
$token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if (empty($_SESSION['pos_csrf']) || !hash_equals($_SESSION['pos_csrf'], $token)) {
    die('Permintaan tidak valid. Silakan kembali ke halaman POS.');
}
if (empty($_SESSION['pos_cart']) || !is_array($_SESSION['pos_cart'])) {
    die('Keranjang kosong. <a href="pos.php">Kembali ke POS</a>');
}

$metode = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : '';
if (!in_array($metode, array('Tunai', 'QRIS', 'Transfer'), true)) {
    die('Metode pembayaran tidak valid.');
}
$idSales = (int)$_SESSION['id_user'];

mysqli_begin_transaction($koneksi);
try {
    // Pastikan akun yang sedang login benar-benar Sales aktif.
    $stmt = mysqli_prepare($koneksi, "SELECT id_user FROM tb_users WHERE id_user = ? AND role = 'sales' AND status = 'aktif' FOR UPDATE");
    mysqli_stmt_bind_param($stmt, 'i', $idSales);
    mysqli_stmt_execute($stmt);
    $userResult = mysqli_stmt_get_result($stmt);
    $sales = mysqli_fetch_assoc($userResult);
    mysqli_stmt_close($stmt);
    if (!$sales) { throw new Exception('Akun Sales tidak aktif atau tidak ditemukan.'); }

    // Cari atau buat satu data customer khusus transaksi kasir umum.
    $stmt = mysqli_prepare($koneksi, "SELECT id_customer FROM tb_customer WHERE nama_customer = 'Pembeli Umum' AND id_user IS NULL ORDER BY id_customer ASC LIMIT 1 FOR UPDATE");
    mysqli_stmt_execute($stmt);
    $custResult = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($custResult);
    mysqli_stmt_close($stmt);

    if ($customer) {
        $idCustomer = (int)$customer['id_customer'];
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_customer (id_user, nama_customer, status, created_at) VALUES (NULL, 'Pembeli Umum', 'aktif', NOW())");
        if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membuat data Pembeli Umum.'); }
        $idCustomer = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);
    }

    $cart = $_SESSION['pos_cart'];
    $total = 0;
    $items = array();

    // Lock setiap produk dan hitung ulang harga dari database (bukan dari browser).
    foreach ($cart as $idProduk => $qty) {
        $idProduk = (int)$idProduk;
        $qty = (int)$qty;
        if ($idProduk <= 0 || $qty <= 0) { throw new Exception('Isi keranjang tidak valid.'); }

        $stmt = mysqli_prepare($koneksi, "SELECT id_produk, nama_produk, harga, stok FROM tb_produk WHERE id_produk = ? AND status = 'aktif' FOR UPDATE");
        mysqli_stmt_bind_param($stmt, 'i', $idProduk);
        mysqli_stmt_execute($stmt);
        $prodResult = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($prodResult);
        mysqli_stmt_close($stmt);

        if (!$product) { throw new Exception('Salah satu produk tidak tersedia.'); }
        if ((int)$product['stok'] < $qty) { throw new Exception('Stok ' . $product['nama_produk'] . ' tidak mencukupi. Stok tersedia: ' . (int)$product['stok']); }

        $price = (int)$product['harga'];
        $subtotal = $price * $qty;
        $total += $subtotal;
        $items[] = array('id_produk' => $idProduk, 'jumlah' => $qty, 'harga' => $price, 'subtotal' => $subtotal, 'nama' => $product['nama_produk']);
    }

    if ($total <= 0) { throw new Exception('Total transaksi tidak valid.'); }

    // Transaksi kasir dibayar di tempat; Sales mengonfirmasi pembayaran sebelum menyimpan.
    $status = 'selesai';
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_transaksi (kode_transaksi, id_customer, id_sales, tanggal_transaksi, total_harga, metode_pembayaran, status_transaksi, bukti_transfer) VALUES (NULL, ?, ?, NOW(), ?, ?, ?, NULL)");
    mysqli_stmt_bind_param($stmt, 'iiiss', $idCustomer, $idSales, $total, $metode, $status);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal menyimpan transaksi utama.'); }
    $idTransaksi = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    $kode = 'TRX-' . str_pad($idTransaksi, 4, '0', STR_PAD_LEFT);
    $stmt = mysqli_prepare($koneksi, "UPDATE tb_transaksi SET kode_transaksi = ? WHERE id_transaksi = ?");
    mysqli_stmt_bind_param($stmt, 'si', $kode, $idTransaksi);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membuat kode transaksi.'); }
    mysqli_stmt_close($stmt);

    foreach ($items as $item) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_detail_transaksi (id_transaksi, id_produk, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiiii', $idTransaksi, $item['id_produk'], $item['harga'], $item['jumlah'], $item['subtotal']);
        if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal menyimpan detail transaksi.'); }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($koneksi, "UPDATE tb_produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?");
        mysqli_stmt_bind_param($stmt, 'iii', $item['jumlah'], $item['id_produk'], $item['jumlah']);
        if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal memperbarui stok produk ' . $item['nama'] . '.');
        }
        mysqli_stmt_close($stmt);

        $tipe = 'keluar';
        $keterangan = 'Penjualan POS ' . $kode . ' oleh Sales ID ' . $idSales;
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_stok (id_produk, jumlah, tipe, keterangan, tanggal) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 'iiss', $item['id_produk'], $item['jumlah'], $tipe, $keterangan);
        if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal mencatat riwayat stok.'); }
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($koneksi);
    $_SESSION['pos_cart'] = array();
    $_SESSION['pos_last_sale'] = array('kode' => $kode, 'total' => $total, 'metode' => $metode, 'tanggal' => date('Y-m-d H:i:s'));
    header('Location: sukses.php');
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Transaksi Gagal</title><body style="font-family:Segoe UI,Arial;background:#fff8fa;padding:35px;color:#543d4c"><div style="max-width:650px;margin:auto;background:white;border:1px solid #f0dce3;border-radius:16px;padding:25px"><h2>Transaksi belum berhasil</h2><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p><a href="pos.php">Kembali ke POS</a></div></body></html>';
    exit;
}
