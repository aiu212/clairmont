
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../koneksi2.php';
mysqli_set_charset($koneksi, 'utf8mb4');

// Validasi Owner
if (
    !isset($_SESSION['login'], $_SESSION['role']) ||
    $_SESSION['login'] !== true ||
    $_SESSION['role'] !== 'owner'
) {
    exit('Akses ditolak.');
}

// =====================================
// HAPUS ULASAN
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_ulasan'])) {

    $idUlasan = (int) ($_POST['id_ulasan'] ?? 0);

    if ($idUlasan > 0) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM tb_ulasan WHERE id_ulasan = ?"
        );

        mysqli_stmt_bind_param($stmt, 'i', $idUlasan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: dashboard_owner.php?menu=ulasan');
    exit;
}

// =====================================
// STATISTIK ULASAN
// =====================================
$qStatistik = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS total_ulasan,
        COALESCE(AVG(rating), 0) AS rata_rating
    FROM tb_ulasan
");

$statistik = mysqli_fetch_assoc($qStatistik);

$totalUlasan = (int) $statistik['total_ulasan'];
$rataRating = (float) $statistik['rata_rating'];

// =====================================
// DISTRIBUSI RATING
// =====================================
$qRating = mysqli_query($koneksi, "
    SELECT rating, COUNT(*) AS jumlah
    FROM tb_ulasan
    GROUP BY rating
");

// =====================================
// DATA SELURUH ULASAN
// =====================================
$qUlasan = mysqli_query($koneksi, "
    SELECT
        u.id_ulasan,
        u.id_customer,
        u.id_transaksi,
        u.rating,
        u.ulasan,
        u.tanggal,
        c.nama_customer,
        t.kode_transaksi
    FROM tb_ulasan u
    LEFT JOIN tb_customer c
        ON u.id_customer = c.id_customer
    LEFT JOIN tb_transaksi t
        ON u.id_transaksi = t.id_transaksi
    ORDER BY u.tanggal DESC
");

if (!$qUlasan) {
    echo '<div class="alert alert-danger">
        Gagal mengambil data ulasan: ' .
        htmlspecialchars(mysqli_error($koneksi)) .
    '</div>';
    return;
}

$jumlahRating = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

if ($qRating) {
    while ($r = mysqli_fetch_assoc($qRating)) {
        $jumlahRating[(int)$r['rating']] = (int)$r['jumlah'];
    }
}
?>

<style>
.ulasan-card {
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: white;
    padding: 22px;
    height: 100%;
    box-shadow: 0 3px 12px rgba(0,0,0,.04);
}

.ulasan-number {
    font-size: 29px;
    font-weight: 700;
    margin-top: 10px;
}

.ulasan-star {
    color: #f5a623;
    letter-spacing: 2px;
    white-space: nowrap;
}

.ulasan-table th {
    background: #f5f7fa;
    white-space: nowrap;
}

.ulasan-comment {
    min-width: 180px;
    max-width: 300px;
    white-space: normal;
    overflow-wrap: anywhere;
}

.rating-progress {
    height: 8px;
    background: #edf0f5;
    border-radius: 10px;
    overflow: hidden;
}

.rating-progress div {
    height: 100%;
    background: #f5a623;
}

</style>

<div class="container-fluid py-3">

    <!-- JUDUL -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-chat-heart text-primary"></i>
                Kelola Ulasan Pembeli
            </h2>

            <p class="text-secondary mb-0">
                Pantau penilaian dan pengalaman customer Clairmont Dessert.
            </p>
        </div>

        <span class="badge bg-primary p-3">
            <i class="bi bi-star-fill"></i>
            Feedback Customer
        </span>

    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="ulasan-card">
                <div class="text-secondary">Total Ulasan</div>

                <div class="ulasan-number text-primary">
                    <?= number_format($totalUlasan, 0, ',', '.'); ?>
                </div>

                <small class="text-secondary">
                    Seluruh ulasan customer
                </small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ulasan-card">
                <div class="text-secondary">Rata-rata Rating</div>

                <div class="ulasan-number text-warning">
                    <?= number_format($rataRating, 1); ?>
                    <i class="bi bi-star-fill"></i>
                    <span class="fs-6 text-secondary">/ 5</span>
                </div>

                <small class="text-secondary">
                    Penilaian keseluruhan customer
                </small>
            </div>
        </div>

    </div>

    <!-- DISTRIBUSI RATING -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                Distribusi Rating
            </h5>
        </div>

        <div class="card-body">

            <?php for ($i = 5; $i >= 1; $i--):
                $jumlah = $jumlahRating[$i];
                $persen = $totalUlasan > 0
                    ? ($jumlah / $totalUlasan) * 100
                    : 0;
            ?>

                <div class="row align-items-center mb-3">

                    <div class="col-2 col-md-1 fw-semibold">
                        <?= $i; ?>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>

                    <div class="col-7 col-md-9">
                        <div class="rating-progress">
                            <div style="width: <?= $persen; ?>%"></div>
                        </div>
                    </div>

                    <div class="col-3 col-md-2 text-end text-secondary">
                        <?= $jumlah; ?> ulasan
                    </div>

                </div>

            <?php endfor; ?>

        </div>
    </div>

    <!-- TABEL ULASAN -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-chat-square-text text-primary"></i>
                Seluruh Ulasan Customer
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle ulasan-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Customer</th>
                            <th>Kode Transaksi</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($qUlasan) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($qUlasan)): ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars(
                                        $row['nama_customer'] ?? 'Customer'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['kode_transaksi'] ?? '-'
                                    ); ?>
                                </td>

                                <td>
                                    <span class="ulasan-star">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= (int)$row['rating']
                                                ? '★'
                                                : '☆';
                                        }
                                        ?>
                                    </span>

                                    <small class="text-secondary">
                                        (<?= (int)$row['rating']; ?>/5)
                                    </small>
                                </td>

                                <td class="ulasan-comment">
                                    <?php if (!empty(trim($row['ulasan'] ?? ''))): ?>

                                        <?= nl2br(htmlspecialchars($row['ulasan'])); ?>

                                    <?php else: ?>

                                        <span class="text-secondary fst-italic">
                                            Tidak ada komentar
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd-m-Y H:i',
                                        strtotime($row['tanggal'])
                                    ); ?>
                                </td>

                                <td>
                                    <form method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus ulasan ini?')">

                                        <input type="hidden"
                                               name="id_ulasan"
                                               value="<?= (int)$row['id_ulasan']; ?>">

                                        <button type="submit"
                                                name="hapus_ulasan"
                                                value="1"
                                                class="btn btn-danger btn-sm">

                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </form>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">
                                <i class="bi bi-chat-square-text fs-2 d-block mb-2"></i>
                                Belum ada ulasan dari customer.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>
