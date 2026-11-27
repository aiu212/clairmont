<?php
require_once __DIR__ . '/koneksi2.php';
$id_user=(int)$_SESSION['id_user'];
$stmt=mysqli_prepare($koneksi,"SELECT u.nama,u.email,c.no_hp,c.alamat,c.kode_customer FROM tb_users u LEFT JOIN tb_customer c ON u.id_user=c.id_user WHERE u.id_user=? LIMIT 1");
mysqli_stmt_bind_param($stmt,'i',$id_user);mysqli_stmt_execute($stmt);$user=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: ['nama'=>'','email'=>'','no_hp'=>'','alamat'=>'','kode_customer'=>''];mysqli_stmt_close($stmt);
?>
<div class="cl-page-head"><span class="cl-eyebrow">Your account</span><h1 class="cl-page-title">Profil <em>Member</em></h1><p>Informasi akun Member Clairmont.</p></div>
<div class="cl-account-grid">
 <div class="cl-panel"><div class="d-flex align-items-center gap-3 mb-4"><div class="rounded-circle d-grid place-items-center" style="width:58px;height:58px;background:#f5e3f4;color:#834f5e;font-size:24px"><i class="bi bi-person"></i></div><div><h2 class="h5 mb-1"><?= htmlspecialchars($user['nama']) ?></h2><span class="cl-status">Member Clairmont</span></div></div>
  <div class="py-2 border-bottom"><small class="text-muted">Kode Member</small><div class="fw-semibold"><?= htmlspecialchars($user['kode_customer'] ?: '-') ?></div></div>
  <div class="py-2 border-bottom"><small class="text-muted">Email</small><div class="fw-semibold"><?= htmlspecialchars($user['email']) ?></div></div>
  <div class="py-2 border-bottom"><small class="text-muted">Nomor Telepon</small><div class="fw-semibold"><?= htmlspecialchars($user['no_hp'] ?: '-') ?></div></div>
  <div class="py-2"><small class="text-muted">Alamat</small><div class="fw-semibold"><?= nl2br(htmlspecialchars($user['alamat'] ?: '-')) ?></div></div>
  <a href="logout.php" class="btn btn-outline-danger btn-sm mt-3"><i class="bi bi-box-arrow-right me-1"></i>Keluar dari Akun</a>
 </div>
 <div class="cl-panel"><h2 class="h5" style="font-family:'Playfair Display',serif">Ingin membeli produk Clairmont?</h2><p class="text-muted">Silakan datang langsung ke toko. Pemesanan dan pembayaran produk dilakukan melalui kasir (POS).</p><a class="cl-btn" href="dashboard_customer.php?menu=produk">Lihat Katalog <i class="bi bi-arrow-right"></i></a></div>
</div>
