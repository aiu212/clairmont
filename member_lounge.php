<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi2.php';
$id_user = (int)($_SESSION['id_user'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT c.kode_customer, c.nama_customer FROM tb_customer c WHERE c.id_user=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: ['kode_customer'=>'', 'nama_customer'=>($_SESSION['nama'] ?? 'Member')];
mysqli_stmt_close($stmt);
?>
<div class="cl-page-head"><span class="cl-eyebrow">Clairmont Member Lounge</span><h1 class="cl-page-title">Welcome, <em><?= htmlspecialchars($member['nama_customer']) ?></em></h1><p>Terima kasih telah menjadi bagian dari Clairmont Patisserie.</p></div>
<div class="cl-panel mb-4" style="background:linear-gradient(115deg,#ffeffc,#eaddff)">
 <span class="cl-eyebrow">Member account</span>
 <h2 class="mt-3 mb-1" style="font:500 30px 'Playfair Display',serif"><?= htmlspecialchars($member['nama_customer']) ?></h2>
 <p class="mb-0 text-muted">Nomor Member: <?= htmlspecialchars($member['kode_customer'] ?: '-') ?></p>
</div>
<div class="cl-panel">
 <h2 class="h5" style="font-family:'Playfair Display',serif">Informasi Pembelian</h2>
 <p class="text-muted">Website Clairmont digunakan untuk melihat katalog, harga, dan informasi produk. Website tidak melayani pemesanan atau pembayaran online.</p>
 <p class="text-muted">Jika ingin membeli produk, silakan datang langsung ke toko. Pemesanan dan pembayaran akan dilayani oleh kasir melalui sistem POS.</p>
 <div class="d-flex flex-wrap gap-2"><a class="cl-btn" href="dashboard_customer.php?menu=produk">Lihat Katalog <i class="bi bi-arrow-right"></i></a><a class="cl-btn cl-btn-outline" href="dashboard_customer.php?menu=profil">Lihat Profil</a></div>
</div>
