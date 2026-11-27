<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi2.php';
mysqli_set_charset($koneksi, 'utf8mb4');
$isMember = isset($_SESSION['login'], $_SESSION['role']) && $_SESSION['login'] === true && $_SESSION['role'] === 'customer';
$menu = $_GET['menu'] ?? 'home';
$allowedMenus = ['home','produk','detail_produk','member','profil'];
if (!in_array($menu, $allowedMenus, true)) $menu = 'home';
$memberMenus = ['member','profil'];
if (!$isMember && in_array($menu, $memberMenus, true)) {
    header('Location: login.php?next=' . urlencode('dashboard_customer.php?menu=' . $menu));
    exit;
}
$namaTampil = $_SESSION['nama'] ?? 'Member';
$active = in_array($menu, ['produk','detail_produk'], true) ? 'produk' : $menu;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#fff7fb">
<title>Clairmont Dessert | Sweet Moments, Made with Love</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="customer-template.css">
</head>
<body class="customer-site">
<div class="customer-shell">
<header class="customer-nav">
 <div class="customer-nav-inner">
  <a class="customer-brand" href="dashboard_customer.php">
   <img src="dist/assets/img/logo.jpeg" alt="Clairmont Dessert" onerror="this.style.display='none'">
   <span><strong>Clairmont</strong><small>Patisserie</small></span>
  </a>
  <nav class="customer-links" aria-label="Navigasi utama">
   <a class="<?= $active==='home'?'active':'' ?>" href="dashboard_customer.php">Home</a>
   <a class="<?= $active==='produk'?'active':'' ?>" href="dashboard_customer.php?menu=produk">Katalog Produk</a>
   <a href="dashboard_customer.php#tentang">Tentang Kami</a>
   <a href="dashboard_customer.php#faq">FAQ</a>
   <?php if ($isMember): ?><a class="<?= $active==='member'?'active':'' ?>" href="dashboard_customer.php?menu=member">Member Lounge</a><?php endif; ?>
  </nav>
  <div class="customer-actions">
   <form class="d-none d-xl-flex align-items-center" action="dashboard_customer.php" method="get" style="background:#fff;border:1px solid #f0ddee;border-radius:99px;padding:5px 12px">
    <input type="hidden" name="menu" value="produk"><i class="bi bi-search text-secondary me-2"></i>
    <input name="q" placeholder="Cari kue & patisserie..." style="border:0;outline:0;width:155px;font-size:11px;background:transparent">
   </form>
   <?php if ($isMember): ?>
    <div class="dropdown">
     <button class="customer-user dropdown-toggle" data-bs-toggle="dropdown" type="button"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($namaTampil) ?></button>
     <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2">
      <li><a class="dropdown-item" href="dashboard_customer.php?menu=profil">Profil Saya</a></li>
      <li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="logout.php">Keluar</a></li>
     </ul>
    </div>
   <?php else: ?>
    <a class="customer-login" href="login.php">Login</a>
    <a class="customer-register" href="register.php">Daftar</a>
   <?php endif; ?>
  </div>
 </div>
</header>
<main class="customer-main <?= $menu==='home'?'customer-home':'' ?>">
<?php
switch ($menu) {
 case 'produk': include 'form/barang/view.php'; break;
 case 'detail_produk': include 'form/barang/detail_produk.php'; break;
 case 'member': include 'member_lounge.php'; break;
 case 'profil': include 'form/barang/profil.php'; break;
 default: include 'main_customer.php'; break;
}
?>
</main>
<footer class="customer-footer">
 <div class="customer-footer-inner">
  <div><div class="customer-footer-brand">Clairmont Patisserie</div><p class="mb-0 mt-2">Artisanal desserts, made for your sweetest moments.</p></div>
  <div><strong>Koleksi Menu</strong><p class="mb-0 mt-2">Signature Cakes · French Pastries · Cookies · Hampers</p></div>
  <div><strong>Informasi</strong><p class="mb-0 mt-2"><a href="dashboard_customer.php?menu=produk">Katalog Produk</a> · <a href="dashboard_customer.php#faq">Bantuan & FAQ</a></p></div>
  <small>© <?= date('Y') ?> Clairmont Dessert. All rights reserved.</small>
 </div>
</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>