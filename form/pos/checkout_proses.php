
<?php
session_start();
require_once __DIR__ . '/../../koneksi2.php';

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

mysqli_set_charset($koneksi, 'utf8mb4');

/* =====================================
   VALIDASI CSRF
===================================== */
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['pos_csrf']) ||
    !hash_equals($_SESSION['pos_csrf'], $token)
) {
    die('Permintaan tidak valid. Silakan kembali ke POS.');
}

/* =====================================
   VALIDASI KERANJANG
===================================== */
if (
    empty($_SESSION['pos_cart']) ||
    !is_array($_SESSION['pos_cart'])
) {
    die('Keranjang kosong. <a href="pos.php">Kembali ke POS</a>');
}

/* =====================================
   VALIDASI METODE PEMBAYARAN
===================================== */
$metode = $_POST['metode_pembayaran'] ?? '';

if (!in_array($metode, ['Tunai', 'QRIS', 'Transfer'], true)) {
    die('Metode pembayaran tidak valid.');
}

/*
QRIS belum memiliki merchant resmi.
Transaksi QRIS nyata tidak boleh disimpan
sebagai transaksi lunas.
*/
if ($metode === 'QRIS') {
    die(
        'Pembayaran QRIS Clairmont masih dalam mode simulasi. ' .
        'Silakan gunakan Tunai atau Transfer Bank. ' .
        '<a href="pos.php">Kembali ke POS</a>'
    );
}

/* =====================================
   VALIDASI TRANSFER
===================================== */
$transferTerverifikasi = false;

if ($metode === 'Transfer') {

    if (
        !isset($_POST['pembayaran_terverifikasi']) ||
        $_POST['pembayaran_terverifikasi'] !== '1'
    ) {
        die(
            'Konfirmasi penerimaan transfer wajib dilakukan. ' .
            '<a href="pos.php">Kembali ke POS</a>'
        );
    }

    $transferTerverifikasi = true;
}

$idSales = (int)$_SESSION['id_user'];

/* =====================================
   MULAI TRANSAKSI DATABASE
===================================== */
mysqli_begin_transaction($koneksi);

try {

    /* =================================
       VALIDASI AKUN SALES
    ================================= */
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_user
         FROM tb_users
         WHERE id_user = ?
         AND role = 'sales'
         AND status = 'aktif'
         FOR UPDATE"
    );

    if (!$stmt) {
        throw new Exception('Gagal memeriksa akun Sales.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $idSales);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $sales = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$sales) {
        throw new Exception('Akun Sales tidak aktif atau tidak ditemukan.');
    }

    /* =================================
       HUBUNGKAN TRANSAKSI KE MEMBER
       Jika nomor HP diisi, transaksi wajib
       memakai customer yang cocok di tb_customer.
       Jika kosong, transaksi memakai Pembeli Umum.
    ================================= */
    $noHpInput = trim((string)($_POST['no_hp_member'] ?? ''));
    $normalisasiHp = static function ($nomor) {
        $angka = preg_replace('/\D+/', '', (string)$nomor);
        if (strpos($angka, '62') === 0) $angka = '0' . substr($angka, 2);
        return $angka;
    };

    if ($noHpInput !== '') {
        $hpDicari = $normalisasiHp($noHpInput);
        if ($hpDicari === '') {
            throw new Exception('Nomor HP member tidak valid.');
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_customer, no_hp
             FROM tb_customer
             WHERE status = 'aktif'
             AND no_hp IS NOT NULL
             AND no_hp <> ''
             FOR UPDATE"
        );
        if (!$stmt) throw new Exception('Gagal memeriksa nomor HP member.');
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $member = null;
        while ($row = mysqli_fetch_assoc($result)) {
            if ($normalisasiHp($row['no_hp']) === $hpDicari) {
                $member = $row;
                break;
            }
        }
        mysqli_stmt_close($stmt);

        if (!$member) {
            throw new Exception('Nomor HP belum terdaftar sebagai member Clairmont. Periksa nomor HP atau kosongkan jika pembeli umum.');
        }
        $idCustomer = (int)$member['id_customer'];
    } else {
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
        if (!$stmt) throw new Exception('Gagal mencari data customer umum.');
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $customer = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($customer) {
            $idCustomer = (int)$customer['id_customer'];
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO tb_customer (id_user, nama_customer, status, created_at)
                 VALUES (NULL, 'Pembeli Umum', 'aktif', NOW())"
            );
            if (!$stmt || !mysqli_stmt_execute($stmt)) {
                throw new Exception('Gagal membuat customer umum.');
            }
            $idCustomer = mysqli_insert_id($koneksi);
            mysqli_stmt_close($stmt);
        }
    }

    /* =================================
       HITUNG TOTAL DAN VALIDASI PRODUK
    ================================= */
    $cart = $_SESSION['pos_cart'];

    $total = 0;
    $items = [];

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

        if (!$stmt) {
            throw new Exception('Gagal memeriksa produk.');
        }

        mysqli_stmt_bind_param($stmt, 'i', $idProduk);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$product) {
            throw new Exception('Produk tidak tersedia.');
        }

        if ((int)$product['stok'] < $qty) {
            throw new Exception(
                'Stok ' . $product['nama_produk'] .
                ' tidak mencukupi. Stok tersedia: ' .
                (int)$product['stok']
            );
        }

        $harga = (int)$product['harga'];
        $subtotal = $harga * $qty;

        $total += $subtotal;

        $items[] = [
            'id_produk' => $idProduk,
            'nama' => $product['nama_produk'],
            'harga' => $harga,
            'jumlah' => $qty,
            'subtotal' => $subtotal
        ];
    }

    if ($total <= 0) {
        throw new Exception('Total transaksi tidak valid.');
    }

    /* =================================
       HITUNG PEMBAYARAN TUNAI
    ================================= */
    $uangDiterima = null;
    $kembalian = null;

    if ($metode === 'Tunai') {

        $uangInput = $_POST['uang_diterima'] ?? '';

        if (
            $uangInput === '' ||
            !ctype_digit((string)$uangInput)
        ) {
            throw new Exception('Nominal uang diterima tidak valid.');
        }

        $uangDiterima = (int)$uangInput;

        if ($uangDiterima < $total) {
            throw new Exception(
                'Uang pembayaran kurang. Total belanja: Rp ' .
                number_format($total, 0, ',', '.')
            );
        }

        $kembalian = $uangDiterima - $total;
    }

    /* =================================
       SIMPAN TRANSAKSI UTAMA
    ================================= */
    $status = 'selesai';

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

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan transaksi utama.');
    }

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

    /* =================================
       BUAT KODE TRANSAKSI
    ================================= */
    $kode = 'TRX-' . str_pad($idTransaksi, 4, '0', STR_PAD_LEFT);

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE tb_transaksi
         SET kode_transaksi = ?
         WHERE id_transaksi = ?"
    );

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan kode transaksi.');
    }

    mysqli_stmt_bind_param($stmt, 'si', $kode, $idTransaksi);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal membuat kode transaksi.');
    }

    mysqli_stmt_close($stmt);

    /* =================================
       SIMPAN DETAIL DAN UPDATE STOK
    ================================= */
    foreach ($items as $item) {

        $idProduk = $item['id_produk'];
        $harga = $item['harga'];
        $jumlah = $item['jumlah'];
        $subtotal = $item['subtotal'];

        /* DETAIL TRANSAKSI */
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO tb_detail_transaksi
            (id_transaksi, id_produk, harga, jumlah, subtotal)
            VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan detail transaksi.');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'iiiii',
            $idTransaksi,
            $idProduk,
            $harga,
            $jumlah,
            $subtotal
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan detail transaksi.');
        }

        mysqli_stmt_close($stmt);

        /* UPDATE STOK PRODUK */
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE tb_produk
             SET stok = stok - ?
             WHERE id_produk = ?
             AND stok >= ?"
        );

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan update stok.');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'iii',
            $jumlah,
            $idProduk,
            $jumlah
        );

        if (
            !mysqli_stmt_execute($stmt) ||
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {
            mysqli_stmt_close($stmt);

            throw new Exception(
                'Gagal memperbarui stok produk ' . $item['nama']
            );
        }

        mysqli_stmt_close($stmt);

        /* CATAT RIWAYAT STOK */
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

        if (!$stmt) {
            throw new Exception('Gagal menyiapkan riwayat stok.');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'iiss',
            $idProduk,
            $jumlah,
            $tipe,
            $keterangan
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal mencatat riwayat stok.');
        }

        mysqli_stmt_close($stmt);
    }

    /* =================================
       SIMPAN SEMUA TRANSAKSI
    ================================= */
    if (!mysqli_commit($koneksi)) {
        throw new Exception('Gagal menyelesaikan transaksi database.');
    }

    /* =================================
       SIMPAN RINGKASAN UNTUK STRUK
    ================================= */
    $_SESSION['pos_cart'] = [];

    $_SESSION['pos_last_sale'] = [
        'kode' => $kode,
        'total' => $total,
        'metode' => $metode,
        'uang_diterima' => $uangDiterima,
        'kembalian' => $kembalian,
        'pembayaran_terverifikasi' => $transferTerverifikasi,
        'tanggal' => date('Y-m-d H:i:s'),
        'items' => $items
    ];

    header('Location: sukses.php');
    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Gagal</title>
    </head>

    <body style="
        font-family:Segoe UI,Arial;
        background:#fff8fa;
        padding:35px;
        color:#543d4c;
    ">

    <div style="
        max-width:650px;
        margin:auto;
        background:white;
        border:1px solid #f0dce3;
        border-radius:16px;
        padding:25px;
    ">

    <h2>Transaksi Belum Berhasil</h2>

    <p>' . htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    ) . '</p>

    <a href="pos.php">Kembali ke POS</a>

    </div>
    </body>
    </html>';

    exit;
}