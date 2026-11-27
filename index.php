
<?php
session_start();
require_once __DIR__ . '/koneksi2.php';

mysqli_set_charset($koneksi, 'utf8mb4');

// Jika sudah login, arahkan ke dashboard sesuai role.
if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
    if ($_SESSION['role'] === 'owner') {
        header('Location: dashboard_owner.php');
        exit;
    } elseif ($_SESSION['role'] === 'sales') {
        header('Location: dashboard_admin.php');
        exit;
    } elseif ($_SESSION['role'] === 'customer') {
        header('Location: dashboard_customer.php');
        exit;
    }
}

// Ambil kategori
$kategoriAktif = filter_input(INPUT_GET, 'kategori', FILTER_VALIDATE_INT);

$qKategori = mysqli_query(
    $koneksi,
    "SELECT id_kategori, nama_kategori, gambar
     FROM tb_kategori
     ORDER BY id_kategori ASC"
);

$kategori = [];
if ($qKategori) {
    while ($row = mysqli_fetch_assoc($qKategori)) {
        $kategori[] = $row;
    }
}

// Ambil produk berdasarkan kategori
if ($kategoriAktif) {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, id_kategori, nama_produk, harga, gambar, status
         FROM tb_produk
         WHERE id_kategori = ?
         ORDER BY id_produk DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $kategoriAktif);
} else {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, id_kategori, nama_produk, harga, gambar, status
         FROM tb_produk
         ORDER BY id_produk DESC"
    );
}

mysqli_stmt_execute($stmt);
$resultProduk = mysqli_stmt_get_result($stmt);

$produk = [];
while ($row = mysqli_fetch_assoc($resultProduk)) {
    $produk[] = $row;
}
mysqli_stmt_close($stmt);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Clairmont Dessert | Sweet Moments</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
:root {
    --pink: #f3b6c2;
    --pink-dark: #d98a9b;
    --cream: #fff8f5;
    --blue: #e6f0ff;
    --brown: #493747;
}

* {
    font-family: "Segoe UI", sans-serif;
}

html {
    scroll-behavior: smooth;
}

body {
    background: var(--cream);
    color: var(--brown);
}

/* NAVBAR */
.navbar {
    background: rgba(255,255,255,.96);
    box-shadow: 0 3px 15px rgba(0,0,0,.05);
}

.brand {
    font-weight: 800;
    letter-spacing: 1px;
    color: var(--brown);
    font-size: 21px;
}

.nav-link {
    color: var(--brown);
    font-weight: 600;
}

.nav-link:hover {
    color: var(--pink-dark);
}

.btn-clairmont {
    background: linear-gradient(135deg, #f3b6c2, #e6f0ff);
    color: var(--brown);
    border: none;
    border-radius: 25px;
    padding: 11px 25px;
    font-weight: 700;
}

.btn-clairmont:hover {
    color: var(--brown);
    transform: translateY(-2px);
}

/* HERO */
.hero {
    background: linear-gradient(120deg, #ffe4e9, #e6f0ff);
    padding: 75px 0;
}

.hero h1 {
    font-size: clamp(35px, 5vw, 58px);
    font-weight: 800;
    line-height: 1.2;
}

.hero p {
    color: #786472;
    font-size: 17px;
    line-height: 1.8;
}

.hero-image {
    width: 100%;
    max-height: 390px;
    object-fit: cover;
    border-radius: 25px;
    box-shadow: 0 12px 30px rgba(0,0,0,.12);
}

/* SECTION */
.section-title {
    font-weight: 800;
    text-align: center;
    margin-bottom: 10px;
}

.section-subtitle {
    color: #927d8b;
    text-align: center;
    margin-bottom: 35px;
}

/* CATEGORY */
.category-card {
    background: white;
    border: none;
    border-radius: 18px;
    overflow: hidden;
    transition: .3s;
    box-shadow: 0 5px 18px rgba(0,0,0,.06);
    height: 100%;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 22px rgba(0,0,0,.1);
}

.category-card img {
    height: 160px;
    width: 100%;
    object-fit: cover;
}

/* PRODUCT */
.product-card {
    background: white;
    border: none;
    border-radius: 18px;
    overflow: hidden;
    height: 100%;
    box-shadow: 0 5px 18px rgba(0,0,0,.06);
    transition: .3s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-card img {
    height: 190px;
    width: 100%;
    object-fit: cover;
}

.product-name {
    font-weight: 700;
    min-height: 42px;
}

.product-price {
    color: #bd6581;
    font-weight: 800;
    font-size: 18px;
}

/* PROMO */
.promo-section {
    background: linear-gradient(120deg, #ffe4e9, #e6f0ff);
    padding: 55px 0;
}

.promo-img {
    width: 100%;
    border-radius: 18px;
}

/* ABOUT */
.about-box {
    background: white;
    padding: 35px;
    border-radius: 22px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

/* FOOTER */
footer {
    background: #493747;
    color: white;
    padding: 35px 0;
}

footer p {
    color: #e9dce4;
}

@media(max-width:768px) {
    .hero {
        padding: 45px 0;
    }

    .hero-image {
        margin-top: 30px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
<div class="container">

    <a class="navbar-brand brand" href="index.php">
        🍰 CLAIRMONT DESSERT
    </a>

    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarGuest">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarGuest">

        <ul class="navbar-nav mx-auto">
            <li class="nav-item">
                <a class="nav-link" href="#home">Home</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#tentang">Tentang Kami</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#kategori">Kategori</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#produk">Menu</a>
            </li>
        </ul>

        <div class="d-flex gap-2">
            <a href="login.php" class="btn btn-outline-secondary rounded-pill px-4">
                Login
            </a>

            <a href="register.php" class="btn btn-clairmont">
                Daftar Member
            </a>
        </div>

    </div>
</div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
<div class="container">
<div class="row align-items-center">

    <div class="col-lg-6">

        <span class="badge rounded-pill text-dark mb-3"
              style="background:#f3b6c2;padding:10px 17px;">
            Welcome to Clairmont Dessert
        </span>

        <h1>
            Sweet Moments,<br>
            Delivered with Love.
        </h1>

        <p class="mt-3">
            Temukan berbagai pilihan dessert dan bakery
            dengan cita rasa istimewa untuk menemani
            setiap momen manis Anda.
        </p>

        <div class="d-flex flex-wrap gap-3 mt-4">

            <a href="#produk" class="btn btn-clairmont">
                Lihat Menu
            </a>

            <a href="register.php" class="btn btn-outline-dark rounded-pill px-4">
                Gabung Member
            </a>

        </div>

    </div>

    <div class="col-lg-6">
        <img src="dist/assets/img/banner.png"
             class="hero-image"
             alt="Clairmont Dessert">
    </div>

</div>
</div>
</section>

<!-- TENTANG -->
<section class="py-5" id="tentang">
<div class="container">

    <h2 class="section-title">About Clairmont Dessert</h2>

    <p class="section-subtitle">
        Sweetness in Every Moment
    </p>

    <div class="about-box text-center">

        <h4 class="fw-bold mb-3">
            Selamat Datang di Clairmont Dessert
        </h4>

        <p class="mb-0">
            Clairmont Dessert hadir untuk memberikan
            pengalaman menikmati berbagai pilihan
            bakery dan dessert yang menyenangkan.
            Temukan menu favorit Anda dan jadikan
            setiap momen lebih istimewa bersama kami.
        </p>

    </div>

</div>
</section>

<!-- KATEGORI -->
<section class="py-5" id="kategori">
<div class="container">

    <h2 class="section-title">Shop By Categories</h2>

    <p class="section-subtitle">
        Temukan berbagai kategori menu favorit Anda
    </p>

    <div class="row">

    <?php if (count($kategori) > 0) { ?>

        <?php foreach ($kategori as $k) { ?>

        <div class="col-md-4 col-sm-6 mb-4">

            <a href="index.php?kategori=<?= (int)$k['id_kategori']; ?>#produk"
               class="text-decoration-none text-dark">

                <div class="category-card">

                    <img src="uploads/kategori/<?= e($k['gambar']); ?>"
                         alt="<?= e($k['nama_kategori']); ?>">

                    <div class="p-3 text-center">
                        <h5 class="fw-bold mb-0">
                            <?= e($k['nama_kategori']); ?>
                        </h5>
                    </div>

                </div>

            </a>

        </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12">
            <div class="alert alert-info">
                Kategori belum tersedia.
            </div>
        </div>

    <?php } ?>

    </div>
</div>
</section>

<!-- PRODUK -->
<section class="py-5" id="produk"
         style="background:#fff0f3;">

<div class="container">

    <h2 class="section-title">Our Featured Menu</h2>

    <p class="section-subtitle">
        Pilihan dessert spesial dari Clairmont
    </p>

    <!-- FILTER -->
    <div class="text-center mb-4">

        <a href="index.php#produk"
           class="btn btn-sm rounded-pill px-4 mb-2
           <?= !$kategoriAktif ? 'btn-dark' : 'btn-outline-dark'; ?>">
            Semua Produk
        </a>

        <?php foreach ($kategori as $k) { ?>

            <a href="index.php?kategori=<?= (int)$k['id_kategori']; ?>#produk"
               class="btn btn-sm rounded-pill px-4 mb-2
               <?= ($kategoriAktif == $k['id_kategori']) ? 'btn-dark' : 'btn-outline-dark'; ?>">
                <?= e($k['nama_kategori']); ?>
            </a>

        <?php } ?>

    </div>

    <div class="row">

    <?php if (count($produk) > 0) { ?>

        <?php foreach ($produk as $p) { ?>

        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">

            <div class="product-card">

                <img src="uploads/<?= e($p['gambar']); ?>"
                     alt="<?= e($p['nama_produk']); ?>">

                <div class="p-3">

                    <h6 class="product-name">
                        <?= e($p['nama_produk']); ?>
                    </h6>

                    <?php if ($p['status'] === 'promo') { ?>
                        <span class="badge bg-danger mb-2">
                            Promo
                        </span>
                    <?php } ?>

                    <div class="product-price">
                        Rp <?= number_format((float)$p['harga'], 0, ',', '.'); ?>
                    </div>

                    <a href="register.php"
                       class="btn btn-clairmont w-100 mt-3">
                        Gabung Member
                    </a>

                </div>

            </div>

        </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12">
            <div class="alert alert-info text-center">
                Belum ada produk pada kategori ini.
            </div>
        </div>

    <?php } ?>

    </div>

</div>
</section>

<!-- PROMO -->
<section class="promo-section">
<div class="container">

    <h2 class="section-title">Special Promotions</h2>

    <p class="section-subtitle">
        Jangan lewatkan promo menarik dari Clairmont
    </p>

    <div class="row">

        <?php
        $promo = [
            'dist/assets/img/promo1.png',
            'dist/assets/img/promo2.png',
            'dist/assets/img/promo3.png',
            'dist/assets/img/promo4.png'
        ];

        foreach ($promo as $img) {
        ?>

        <div class="col-md-3 col-6 mb-3">
            <img src="<?= e($img); ?>"
                 class="promo-img"
                 alt="Promo Clairmont">
        </div>

        <?php } ?>

    </div>

</div>
</section>

<!-- MEMBER CTA -->
<section class="py-5">
<div class="container">

    <div class="about-box text-center"
         style="background:linear-gradient(120deg,#fce3e9,#e6f0ff);">

        <h2 class="fw-bold">
            Become a Clairmont Member!
        </h2>

        <p class="mt-3">
            Bergabunglah menjadi member Clairmont
            dan nikmati pengalaman berbelanja yang lebih menarik.
        </p>

        <a href="register.php" class="btn btn-clairmont mt-2">
            Daftar Member Sekarang
        </a>

    </div>

</div>
</section>

<!-- FOOTER -->
<footer>
<div class="container text-center">

    <h4 class="fw-bold">
        🍰 CLAIRMONT DESSERT
    </h4>

    <p class="mt-2 mb-2">
        Sweet moments, delivered with love.
    </p>

    <p class="small mb-0">
        &copy; <?= date('Y'); ?> Clairmont Dessert.
        All Rights Reserved.
    </p>

</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>