
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

        $idTransaksi = (int)($_POST['id_transaksi'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $komentar = trim($_POST['ulasan'] ?? '');

        if ($idTransaksi <= 0 || $rating < 1 || $rating > 5) {

            $pesanUlasan = 'Data transaksi atau rating tidak valid.';
            $jenisPesan = 'danger';

        } elseif (strlen($komentar) > 5000) {

            $pesanUlasan = 'Ulasan maksimal 5000 karakter.';
            $jenisPesan = 'danger';

        } else {

            // Pastikan transaksi milik customer
            // dan sudah selesai.

            $cek = mysqli_prepare(
                $koneksi,
                "SELECT id_transaksi
                 FROM tb_transaksi
                 WHERE id_transaksi = ?
                   AND id_customer = ?
                   AND status_transaksi = 'selesai'
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $cek,
                'ii',
                $idTransaksi,
                $idCustomer
            );

            mysqli_stmt_execute($cek);
            $transaksiValid = mysqli_stmt_get_result($cek);
            $adaTransaksi = mysqli_fetch_assoc($transaksiValid);
            mysqli_stmt_close($cek);

            if (!$adaTransaksi) {

                $pesanUlasan = 'Transaksi tidak ditemukan atau belum selesai.';
                $jenisPesan = 'danger';

            } else {

                // Pastikan transaksi belum pernah diulas.

                $cekUlasan = mysqli_prepare(
                    $koneksi,
                    "SELECT id_ulasan
                     FROM tb_ulasan
                     WHERE id_transaksi = ?
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $cekUlasan,
                    'i',
                    $idTransaksi
                );

                mysqli_stmt_execute($cekUlasan);
                $resultUlasan = mysqli_stmt_get_result($cekUlasan);
                $sudahUlasan = mysqli_fetch_assoc($resultUlasan);
                mysqli_stmt_close($cekUlasan);

                if ($sudahUlasan) {

                    $pesanUlasan = 'Transaksi ini sudah pernah diberikan ulasan.';
                    $jenisPesan = 'warning';

                } else {

                    $insert = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO tb_ulasan
                         (id_customer, id_transaksi, rating, ulasan)
                         VALUES (?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $insert,
                        'iiis',
                        $idCustomer,
                        $idTransaksi,
                        $rating,
                        $komentar
                    );

                    if (mysqli_stmt_execute($insert)) {

                        $pesanUlasan = 'Terima kasih! Ulasan Anda berhasil dikirim.';
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
// AMBIL DAFTAR TRANSAKSI SELESAI
// =============================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        t.id_transaksi,
        t.kode_transaksi,
        t.tanggal_transaksi,
        t.total_harga,
        u.id_ulasan,
        u.rating,
        u.ulasan,
        u.tanggal AS tanggal_ulasan
     FROM tb_transaksi t
     LEFT JOIN tb_ulasan u
        ON t.id_transaksi = u.id_transaksi
     WHERE t.id_customer = ?
       AND t.status_transaksi = 'selesai'
     ORDER BY t.tanggal_transaksi DESC"
);

mysqli_stmt_bind_param($stmt, 'i', $idCustomer);
mysqli_stmt_execute($stmt);

$resultTransaksi = mysqli_stmt_get_result($stmt);

$transaksiList = [];

while ($row = mysqli_fetch_assoc($resultTransaksi)) {
    $transaksiList[] = $row;
}

mysqli_stmt_close($stmt);

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

    <!-- TRANSAKSI YANG BISA DIULAS -->

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
                        Transaksi <?= ulasan_h($item['kode_transaksi'] ?: $item['id_transaksi']) ?>
                    </h6>

                    <small class="text-secondary">
                        <?= date('d-m-Y H:i', strtotime($item['tanggal_transaksi'])) ?>
                    </small>
                </div>

                <span class="badge bg-success align-self-start">
                    Transaksi Selesai
                </span>

            </div>

            <div class="mt-3">
                <strong>Total Pembelian:</strong>
                Rp <?= number_format($item['total_harga'], 0, ',', '.') ?>
            </div>

            <hr>

            <form method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= ulasan_h($_SESSION['csrf_ulasan']) ?>">

                <input type="hidden"
                       name="id_transaksi"
                       value="<?= (int)$item['id_transaksi'] ?>">

                <label class="fw-bold">
                    Bagaimana pengalaman Anda?
                </label>

                <div class="ulasan-stars">

                    <?php for ($i = 5; $i >= 1; $i--): ?>

                        <input
                            type="radio"
                            id="star<?= $i ?>_<?= (int)$item['id_transaksi'] ?>"
                            name="rating"
                            value="<?= $i ?>"
                            required
                        >

                        <label
                            for="star<?= $i ?>_<?= (int)$item['id_transaksi'] ?>"
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
                    maxlength="5000"
                    placeholder="Ceritakan pengalaman Anda membeli produk Clairmont Dessert..."
                ></textarea>

                <button
                    type="submit"
                    name="kirim_ulasan"
                    value="1"
                    class="ulasan-button">

                    <i class="bi bi-send"></i>
                    Kirim Ulasan

                </button>

            </form>

        </div>

    <?php endforeach; ?>

    <?php if (!$adaBelumDiulas): ?>

        <div class="ulasan-empty mb-4">
            <i class="bi bi-chat-square-heart fs-1"></i>

            <h6 class="mt-3 fw-bold">
                Belum Ada Transaksi yang Perlu Diulas
            </h6>

            <p class="mb-0">
                Transaksi yang sudah selesai dan belum diulas
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
                        Transaksi <?= ulasan_h($item['kode_transaksi'] ?: $item['id_transaksi']) ?>
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
                <?= nl2br(ulasan_h($item['ulasan'] ?: 'Tidak ada komentar.')) ?>
            </div>

        </div>

    <?php endforeach; ?>

    <?php if (!$adaRiwayat): ?>
        <p class="text-secondary">
            Anda belum memiliki riwayat ulasan.
        </p>
    <?php endif; ?>

</div>
