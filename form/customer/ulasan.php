<?php
// =============================================
// FITUR ULASAN CUSTOMER - CLAIRMONT DESSERT
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../koneksi2.php';

mysqli_set_charset($koneksi, 'utf8mb4');

if (!isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    $_SESSION['role'] !== 'customer') {
    echo '<div class="alert alert-warning">Silakan login sebagai Customer.</div>';
    return;
}

if (!function_exists('ulasan_h')) {
    function ulasan_h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// =============================================
// IDENTITAS CUSTOMER
// =============================================

$idUser = (int)($_SESSION['id_user'] ?? 0);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_customer, nama_customer
     FROM tb_customer
     WHERE id_user = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);

$resultCustomer = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($resultCustomer);

mysqli_stmt_close($stmt);

if (!$customer) {
    echo '<div class="alert alert-warning">
        Data Customer belum terhubung dengan akun Anda.
    </div>';
    return;
}

$idCustomer = (int)$customer['id_customer'];

// =============================================
// CSRF TOKEN
// =============================================

if (empty($_SESSION['csrf_ulasan'])) {
    $_SESSION['csrf_ulasan'] = bin2hex(random_bytes(32));
}

$pesanUlasan = '';
$jenisPesan = 'success';

// =============================================
// SIMPAN ULASAN
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['kirim_ulasan'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_ulasan'], $token)) {

        $pesanUlasan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
        $jenisPesan = 'danger';

    } else {

        $idProduk = (int)($_POST['id_produk'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $komentar = trim($_POST['ulasan'] ?? '');

        if ($idProduk <= 0 || $rating < 1 || $rating > 5) {

            $pesanUlasan = 'Data produk atau rating tidak valid.';
            $jenisPesan = 'danger';

        } elseif ((function_exists('mb_strlen') ? mb_strlen($komentar, 'UTF-8') : strlen($komentar)) > 1000) {

            $pesanUlasan = 'Ulasan maksimal 1000 karakter.';
            $jenisPesan = 'danger';

        } else {

            // Pastikan produk pernah dibeli customer dari transaksi selesai.

            $cek = mysqli_prepare(
                $koneksi,
                "SELECT 1
                 FROM tb_transaksi t
                 INNER JOIN tb_detail_transaksi d ON d.id_transaksi = t.id_transaksi
                 WHERE t.id_customer = ?
                   AND d.id_produk = ?
                   AND t.status_transaksi = 'selesai'
                 LIMIT 1"
            );

            if (!$cek) {
                $adaTransaksi = false;
            } else {
                mysqli_stmt_bind_param($cek, 'ii', $idCustomer, $idProduk);
                $cekBerhasil = mysqli_stmt_execute($cek);
                $transaksiValid = $cekBerhasil ? mysqli_stmt_get_result($cek) : false;
                $adaTransaksi = $transaksiValid ? mysqli_fetch_assoc($transaksiValid) : false;
                mysqli_stmt_close($cek);
            }

            if (!$adaTransaksi) {

                $pesanUlasan = 'Transaksi tidak ditemukan atau belum selesai.';
                $jenisPesan = 'danger';

            } else {

                // Skema database menyimpan satu ulasan per produk per customer.
                $insert = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO tb_ulasan_produk (id_produk, id_customer, rating, komentar)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        rating = VALUES(rating),
                        komentar = VALUES(komentar),
                        updated_at = CURRENT_TIMESTAMP"
                );

                if (!$insert) {
                    $pesanUlasan = 'Ulasan belum dapat disimpan. Periksa struktur tabel ulasan.';
                    $jenisPesan = 'danger';
                } else {
                    mysqli_stmt_bind_param(
                        $insert,
                        'iiis',
                        $idProduk,
                        $idCustomer,
                        $rating,
                        $komentar
                    );

                    if (mysqli_stmt_execute($insert)) {

                        $pesanUlasan = 'Terima kasih! Ulasan produk berhasil disimpan.';
                        $jenisPesan = 'success';

                    } else {

                        $pesanUlasan = 'Ulasan gagal disimpan. Silakan coba kembali.';
                        $jenisPesan = 'danger';

                    }

                    mysqli_stmt_close($insert);
                }
            }
        }
    }
}

// =============================================
// AMBIL DAFTAR PRODUK DARI TRANSAKSI SELESAI
// =============================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT DISTINCT
        p.id_produk,
        p.nama_produk,
        p.gambar,
        u.id_ulasan,
        u.rating,
        u.komentar,
        u.created_at AS tanggal_ulasan
     FROM tb_transaksi t
     INNER JOIN tb_detail_transaksi d ON d.id_transaksi = t.id_transaksi
     INNER JOIN tb_produk p ON p.id_produk = d.id_produk
     LEFT JOIN tb_ulasan_produk u
        ON u.id_produk = p.id_produk AND u.id_customer = t.id_customer
     WHERE t.id_customer = ?
       AND t.status_transaksi = 'selesai'
     ORDER BY p.nama_produk"
);

$transaksiList = [];
$errorDaftarUlasan = false;

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $idCustomer);
    if (mysqli_stmt_execute($stmt)) {
        $resultTransaksi = mysqli_stmt_get_result($stmt);
        if ($resultTransaksi) {
            while ($row = mysqli_fetch_assoc($resultTransaksi)) {
                $transaksiList[] = $row;
            }
        } else {
            $errorDaftarUlasan = true;
        }
    } else {
        $errorDaftarUlasan = true;
    }
    mysqli_stmt_close($stmt);
} else {
    $errorDaftarUlasan = true;
}

// =============================================
// HITUNG STATISTIK ULASAN
// =============================================

$totalUlasan = 0;
$totalRating = 0;

foreach ($transaksiList as $item) {
    if ($item['id_ulasan'] !== null) {
        $totalUlasan++;
        $totalRating += (int)$item['rating'];
    }
}

$rataRating = $totalUlasan > 0
    ? number_format($totalRating / $totalUlasan, 1)
    : '0.0';

?>

<style>
.ulasan-wrapper {
    padding: 25px 15px;
}

.ulasan-header {
    margin-bottom: 25px;
}

.ulasan-header h2 {
    color: #553746;
    font-weight: 700;
}

.ulasan-stat {
    background: white;
    border: 1px solid #f1dce6;
    border-radius: 16px;
    padding: 22px;
    height: 100%;
}

.ulasan-stat small {
    color: #888;
}

.ulasan-stat h3 {
    color: #bd7797;
    font-weight: 700;
    margin-top: 10px;
}

.ulasan-card {
    background: white;
    border: 1px solid #f0dce6;
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 18px;
    box-shadow: 0 4px 15px rgba(0,0,0,.035);
}

.ulasan-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 8px;
    margin: 12px 0;
}

.ulasan-stars input {
    display: none;
}

.ulasan-stars label {
    font-size: 32px;
    color: #ddd;
    cursor: pointer;
}

.ulasan-stars label:hover,
.ulasan-stars label:hover ~ label,
.ulasan-stars input:checked ~ label {
    color: #ffc107;
}

.ulasan-button {
    background: linear-gradient(135deg,#d98eae,#b96f96);
    border: none;
    color: white;
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 600;
}

.ulasan-button:hover {
    color: white;
    opacity: .9;
}

.ulasan-history {
    background: #fff9fc;
    border-radius: 12px;
    padding: 15px;
}

.ulasan-empty {
    padding: 35px;
    text-align: center;
    color: #888;
    background: white;
    border-radius: 15px;
    border: 1px dashed #e6c8d7;
}
</style>

<div class="ulasan-wrapper">

    <div class="ulasan-header">
        <h2>
            <i class="bi bi-chat-heart"></i>
            Ulasan Pembeli
        </h2>

        <p class="text-secondary">
            Bagikan pengalaman Anda menikmati produk Clairmont Dessert.
        </p>
    </div>

    <?php if ($pesanUlasan !== ''): ?>
        <div class="alert alert-<?= ulasan_h($jenisPesan) ?>">
            <?= ulasan_h($pesanUlasan) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorDaftarUlasan): ?>
        <div class="alert alert-danger">
            Daftar ulasan gagal dimuat. Pastikan tabel tb_ulasan_produk sudah tersedia.
        </div>
    <?php endif; ?>

    <!-- STATISTIK -->

    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="ulasan-stat">
                <small>Total Ulasan Saya</small>
                <h3>
                    <?= $totalUlasan ?>
                    <i class="bi bi-chat-square-text"></i>
                </h3>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ulasan-stat">
                <small>Rata-rata Rating Saya</small>
                <h3>
                    <?= $rataRating ?> / 5
                    <i class="bi bi-star-fill text-warning"></i>
                </h3>
            </div>
        </div>

    </div>

    <!-- PRODUK YANG BISA DIULAS -->

    <h5 class="fw-bold mb-3">
        <i class="bi bi-pencil-square"></i>
        Berikan Ulasan
    </h5>

    <?php
    $adaBelumDiulas = false;

    foreach ($transaksiList as $item):
        if ($item['id_ulasan'] !== null) continue;

        $adaBelumDiulas = true;
    ?>

        <div class="ulasan-card">

            <div class="d-flex justify-content-between flex-wrap gap-2">

                <div>
                    <h6 class="fw-bold mb-1">
                        <?= ulasan_h($item['nama_produk']) ?>
                    </h6>
                </div>

                <span class="badge bg-success align-self-start">
                    Pernah Dibeli
                </span>

            </div>

            <hr>

            <form method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= ulasan_h($_SESSION['csrf_ulasan']) ?>">

                <input type="hidden"
                       name="id_produk"
                       value="<?= (int)$item['id_produk'] ?>">

                <label class="fw-bold">
                    Bagaimana pengalaman Anda?
                </label>

                <div class="ulasan-stars">

                    <?php for ($i = 5; $i >= 1; $i--): ?>

                        <input
                            type="radio"
                            id="star<?= $i ?>_<?= (int)$item['id_produk'] ?>"
                            name="rating"
                            value="<?= $i ?>"
                            required
                        >

                        <label
                            for="star<?= $i ?>_<?= (int)$item['id_produk'] ?>"
                            title="<?= $i ?> bintang">
                            ★
                        </label>

                    <?php endfor; ?>

                </div>

                <label class="form-label fw-semibold">
                    Komentar Anda
                </label>

                <textarea
                    name="ulasan"
                    class="form-control mb-3"
                    rows="3"
                    maxlength="1000"
                    placeholder="Ceritakan pengalaman Anda dengan produk ini..."
                ></textarea>

                <button
                    type="submit"
                    name="kirim_ulasan"
                    value="1"
                    class="ulasan-button">

                    <i class="bi bi-send"></i>
                    Kirim Ulasan Produk

                </button>

            </form>

        </div>

    <?php endforeach; ?>

    <?php if (!$adaBelumDiulas): ?>

        <div class="ulasan-empty mb-4">
            <i class="bi bi-chat-square-heart fs-1"></i>

            <h6 class="mt-3 fw-bold">
            Belum Ada Produk yang Perlu Diulas
            </h6>

            <p class="mb-0">
                Produk dari transaksi yang sudah selesai dan belum diulas
                akan muncul di sini.
            </p>
        </div>

    <?php endif; ?>

    <!-- RIWAYAT ULASAN -->

    <h5 class="fw-bold mb-3 mt-4">
        <i class="bi bi-clock-history"></i>
        Riwayat Ulasan Saya
    </h5>

    <?php
    $adaRiwayat = false;

    foreach ($transaksiList as $item):
        if ($item['id_ulasan'] === null) continue;

        $adaRiwayat = true;
    ?>

        <div class="ulasan-card">

            <div class="d-flex justify-content-between flex-wrap">

                <div>
                    <h6 class="fw-bold">
                        <?= ulasan_h($item['nama_produk']) ?>
                    </h6>

                    <small class="text-secondary">
                        <?= date('d-m-Y H:i', strtotime($item['tanggal_ulasan'])) ?>
                    </small>
                </div>

                <span class="badge bg-success align-self-start">
                    Sudah Diulas
                </span>

            </div>

            <div class="text-warning fs-4 mt-2">
                <?= str_repeat('★', (int)$item['rating']) ?>
                <span class="text-secondary small">
                    <?= (int)$item['rating'] ?>/5
                </span>
            </div>

            <div class="ulasan-history mt-2">
                <?= nl2br(ulasan_h($item['komentar'] ?: 'Tidak ada komentar.')) ?>
            </div>

        </div>

    <?php endforeach; ?>

    <?php if (!$adaRiwayat): ?>
        <p class="text-secondary">
            Anda belum memiliki riwayat ulasan.
        </p>
    <?php endif; ?>

</div>
