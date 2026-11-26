
<?php
session_start();
require_once __DIR__ . '/../../koneksi2.php';

/* =========================================
   1. VALIDASI LOGIN SALES
========================================= */

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

/* =========================================
   2. VALIDASI CSRF
========================================= */

$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['pos_csrf']) ||
    !hash_equals($_SESSION['pos_csrf'], $token)
) {
    die('Permintaan tidak valid. Silakan kembali ke POS.');
}

/* =========================================
   3. VALIDASI KERANJANG
========================================= */

if (
    empty($_SESSION['pos_cart']) ||
    !is_array($_SESSION['pos_cart'])
) {
    die('Keranjang kosong. <a href="pos.php">Kembali ke POS</a>');
}

/* =========================================
   4. VALIDASI METODE PEMBAYARAN
========================================= */

$metode = $_POST['metode_pembayaran'] ?? '';

if (!in_array($metode, array('Tunai', 'QRIS', 'Transfer'), true)) {
    die('Metode pembayaran tidak valid.');
}

/*
   QRIS masih simulasi.
   Transaksi tidak boleh disimpan sebelum
   Clairmont memiliki QRIS merchant resmi.
*/

if ($metode === 'QRIS') {
    die('QRIS Clairmont masih dalam mode simulasi. Transaksi belum dapat diproses. <a href="pos.php">Kembali ke POS</a>');
}

/* =========================================
   5. VALIDASI PEMBAYARAN TUNAI
========================================= */

$uangDiterima = 0;
$kembalian = 0;

if ($metode === 'Tunai') {

    $inputUang = $_POST['uang_diterima'] ?? '';

    if (
        !is_string($inputUang) ||
        !preg_match('/^[0-9]+$/D', $inputUang)
    ) {
        die('Nominal uang diterima tidak valid.');
    }

    $uangDiterima = (int)$inputUang;

    if ($uangDiterima <= 0) {
        die('Nominal uang diterima harus lebih dari nol.');
    }
}

/* =========================================
   6. VALIDASI TRANSFER
========================================= */

if ($metode === 'Transfer') {

    if (
        !isset($_POST['pembayaran_terverifikasi']) ||
        $_POST['pembayaran_terverifikasi'] !== '1'
    ) {
        die('Transfer belum dikonfirmasi. Pastikan dana sudah diterima. <a href="pos.php">Kembali ke POS</a>');
    }
}

/* =========================================
   7. IDENTITAS SALES
========================================= */

$idSales = (int)$_SESSION['id_user'];

mysqli_begin_transaction($koneksi);

try {

    /* =====================================
       8. PASTIKAN SALES AKTIF
    ===================================== */

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_user
         FROM tb_users
         WHERE id_user = ?
         AND role = 'sales'
         AND status = 'aktif'
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, 'i', $idSales);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $sales = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$sales) {
        throw new Exception('Akun Sales tidak aktif atau tidak ditemukan.');
    }

    /* =====================================
       9. CARI CUSTOMER UMUM
    ===================================== */

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_customer
         FROM tb_customer
         WHERE nama_customer = 'Pembeli Umum'
         AND id_user IS NULL
         ORDER BY id_customer ASC
         LIMIT 1
         FOR UPDATE"
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($customer) {

        $idCustomer = (int)$customer['id_customer'];

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO tb_customer
            (id_user, nama_customer, status, created_at)
            VALUES (NULL, 'Pembeli Umum', 'aktif', NOW())"
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal membuat data Pembeli Umum.');
        }

        $idCustomer = mysqli_insert_id($koneksi);

        mysqli_stmt_close($stmt);
    }

    /* =====================================
       10. VALIDASI PRODUK DAN STOK
    ===================================== */

    $cart = $_SESSION['pos_cart'];

    $total = 0;
    $items = array();

    foreach ($cart as $idProduk => $qty) {

        $idProduk = (int)$idProduk;
        $qty = (int)$qty;

        if ($idProduk <= 0 || $qty <= 0) {
            throw new Exception('Isi keranjang tidak valid.');
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_produk, nama_produk, harga, stok
             FROM tb_produk
             WHERE id_produk = ?
             AND status = 'aktif'
             FOR UPDATE"
        );

        mysqli_stmt_bind_param($stmt, 'i', $idProduk);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$product) {
            throw new Exception('Produk tidak ditemukan atau sudah tidak aktif.');
        }

        if ((int)$product['stok'] < $qty) {
            throw new Exception(
                'Stok ' . $product['nama_produk'] .
                ' tidak mencukupi. Stok tersedia: ' .
                (int)$product['stok']
            );
        }

        $harga = (int)$product['harga'];

        if ($harga <= 0) {
            throw new Exception('Harga produk tidak valid.');
        }

        $subtotal = $harga * $qty;

        $total += $subtotal;

        $items[] = array(
            'id_produk' => $idProduk,
            'nama' => $product['nama_produk'],
            'harga' => $harga,
            'jumlah' => $qty,
            'subtotal' => $subtotal
        );
    }

    if ($total <= 0) {
        throw new Exception('Total transaksi tidak valid.');
    }

    /* =====================================
       11. VALIDASI ULANG PEMBAYARAN
    ===================================== */

    if ($metode === 'Tunai') {

        if ($uangDiterima < $total) {
            throw new Exception(
                'Uang yang diterima kurang. Total belanja Rp ' .
                number_format($total, 0, ',', '.')
            );
        }

        $kembalian = $uangDiterima - $total;
    }

    /*
       Status selesai diberikan hanya setelah
       pembayaran dikonfirmasi oleh Sales.
    */

    $status = 'selesai';

    /* =====================================
       12. SIMPAN TRANSAKSI UTAMA
    ===================================== */

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO tb_transaksi
        (
            kode_transaksi,
            id_customer,
            id_sales,
            tanggal_transaksi,
            total_harga,
            metode_pembayaran,
            status_transaksi,
            bukti_transfer
        )
        VALUES (NULL, ?, ?, NOW(), ?, ?, ?, NULL)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'iiiss',
        $idCustomer,
        $idSales,
        $total,
        $metode,
        $status
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menyimpan transaksi utama.');
    }

    $idTransaksi = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt);

    /* =====================================
       13. BUAT KODE TRANSAKSI
    ===================================== */

    $kode = 'TRX-' . str_pad(
        $idTransaksi,
        4,
        '0',
        STR_PAD_LEFT
    );

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE tb_transaksi
         SET kode_transaksi = ?
         WHERE id_transaksi = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        'si',
        $kode,
        $idTransaksi
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal membuat kode transaksi.');
    }

    mysqli_stmt_close($stmt);

    /* =====================================
       14. SIMPAN DETAIL TRANSAKSI
    ===================================== */

    foreach ($items as $item) {

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO tb_detail_transaksi
            (id_transaksi, id_produk, harga, jumlah, subtotal)
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'iiiii',
            $idTransaksi,
            $item['id_produk'],
            $item['harga'],
            $item['jumlah'],
            $item['subtotal']
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan detail transaksi.');
        }

        mysqli_stmt_close($stmt);

        /* =================================
           15. KURANGI STOK PRODUK
        ================================= */

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE tb_produk
             SET stok = stok - ?
             WHERE id_produk = ?
             AND stok >= ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'iii',
            $item['jumlah'],
            $item['id_produk'],
            $item['jumlah']
        );

        if (!mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                'Gagal memperbarui stok produk ' .
                $item['nama']
            );
        }

        if (mysqli_stmt_affected_rows($stmt) !== 1) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                'Stok produk ' . $item['nama'] .
                ' gagal diperbarui.'
            );
        }

        mysqli_stmt_close($stmt);

        /* =================================
           16. CATAT RIWAYAT STOK
        ================================= */

        $tipe = 'keluar';

        $keterangan =
            'Penjualan POS ' . $kode .
            ' oleh Sales ID ' . $idSales;

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO tb_stok
            (id_produk, jumlah, tipe, keterangan, tanggal)
            VALUES (?, ?, ?, ?, NOW())"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'iiss',
            $item['id_produk'],
            $item['jumlah'],
            $tipe,
            $keterangan
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal mencatat riwayat stok.');
        }

        mysqli_stmt_close($stmt);
    }

    /* =====================================
       17. SIMPAN TRANSAKSI
    ===================================== */

    mysqli_commit($koneksi);

    /* =====================================
       18. KOSONGKAN KERANJANG
    ===================================== */

    $_SESSION['pos_cart'] = array();

    /* =====================================
       19. SIMPAN INFORMASI PENJUALAN
    ===================================== */

    $_SESSION['pos_last_sale'] = array(

        'kode' => $kode,

        'total' => $total,

        'metode' => $metode,

        'uang_diterima' => $uangDiterima,

        'kembalian' => $kembalian,

        'tanggal' => date('Y-m-d H:i:s')

    );

    /* =====================================
       20. ARAHKAN KE HALAMAN SUKSES
    ===================================== */

    header('Location: sukses.php');
    exit;


} catch (Exception $e) {

    mysqli_rollback($koneksi);

    echo '
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Transaksi Gagal</title>

        <style>
            body {
                font-family: Segoe UI, Arial, sans-serif;
                background: #fff8fa;
                padding: 35px;
                color: #543d4c;
            }

            .card {
                max-width: 650px;
                margin: auto;
                background: white;
                border: 1px solid #f0dce3;
                border-radius: 16px;
                padding: 25px;
            }

            a {
                display: inline-block;
                background: #f3b6c2;
                padding: 10px 18px;
                border-radius: 9px;
                color: #543d4c;
                text-decoration: none;
                font-weight: bold;
            }
        </style>
    </head>

    <body>

        <div class="card">

            <h2>Transaksi Belum Berhasil</h2>

            <p>'
            . htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            ) .
            '</p>

            <a href="pos.php">
                Kembali ke POS
            </a>

        </div>

    </body>
    </html>';

    exit;
}