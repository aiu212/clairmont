<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi2.php';
mysqli_set_charset($koneksi, 'utf8mb4');
$idProduk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = mysqli_prepare($koneksi, "SELECT p.*,k.nama_kategori FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori=k.id_kategori WHERE p.id_produk=? AND p.status='aktif' LIMIT 1");
mysqli_stmt_bind_param($stmt,'i',$idProduk);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$produk): ?>
<div class="cl-empty"><h2>Produk tidak ditemukan</h2><p>Produk mungkin sudah tidak tersedia.</p><a class="cl-btn" href="dashboard_customer.php?menu=produk">Kembali ke Katalog</a></div>
<?php return; endif;
$isMember = !empty($_SESSION['login']) && ($_SESSION['role'] ?? '') === 'customer';
$idCustomer = 0;
if ($isMember) {
    $uid = (int)($_SESSION['id_user'] ?? 0);
    $s = mysqli_prepare($koneksi, "SELECT id_customer FROM tb_customer WHERE id_user=? AND status='aktif' LIMIT 1");
    mysqli_stmt_bind_param($s, 'i', $uid); mysqli_stmt_execute($s);
    $m = mysqli_fetch_assoc(mysqli_stmt_get_result($s)); mysqli_stmt_close($s);
    $idCustomer = (int)($m['id_customer'] ?? 0);
}
$bolehUlas = false; $ulasanSaya = null;
if ($idCustomer > 0) {
    $s = mysqli_prepare($koneksi, "SELECT 1 FROM tb_transaksi t JOIN tb_detail_transaksi d ON d.id_transaksi=t.id_transaksi WHERE t.id_customer=? AND d.id_produk=? AND t.status_transaksi='selesai' LIMIT 1");
    mysqli_stmt_bind_param($s, 'ii', $idCustomer, $idProduk); mysqli_stmt_execute($s);
    $bolehUlas = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($s)); mysqli_stmt_close($s);
    $s = mysqli_prepare($koneksi, "SELECT rating,komentar FROM tb_ulasan_produk WHERE id_produk=? AND id_customer=? LIMIT 1");
    mysqli_stmt_bind_param($s, 'ii', $idProduk, $idCustomer); mysqli_stmt_execute($s);
    $ulasanSaya = mysqli_fetch_assoc(mysqli_stmt_get_result($s)); mysqli_stmt_close($s);
}
$s = mysqli_prepare($koneksi, "SELECT u.rating,u.komentar,u.created_at,c.nama_customer FROM tb_ulasan_produk u JOIN tb_customer c ON c.id_customer=u.id_customer WHERE u.id_produk=? ORDER BY u.created_at DESC");
mysqli_stmt_bind_param($s, 'i', $idProduk); mysqli_stmt_execute($s); $ulasanResult = mysqli_stmt_get_result($s);
$jumlahUlasan = mysqli_num_rows($ulasanResult); $sumRating = 0; $ulasanList = [];
while ($r = mysqli_fetch_assoc($ulasanResult)) { $sumRating += (int)$r['rating']; $ulasanList[] = $r; }
$rataRating = $jumlahUlasan ? $sumRating / $jumlahUlasan : 0; mysqli_stmt_close($s);
$pesanUlasan = $_GET['ulasan'] ?? '';
?>
<div class="cl-page-head text-start"><a class="cl-link" href="dashboard_customer.php?menu=produk"><i class="bi bi-arrow-left"></i> Kembali ke katalog</a></div>
<div class="cl-product-detail">
 <div class="cl-product-detail-image"><img src="uploads/<?= htmlspecialchars($produk['gambar'] ?? '') ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" onerror="this.src='dist/assets/img/logo.png'"></div>
 <div class="cl-panel">
  <span class="cl-eyebrow"><?= htmlspecialchars($produk['nama_kategori'] ?? 'Clairmont Selection') ?></span>
  <h1><?= htmlspecialchars($produk['nama_produk']) ?></h1>
  <div class="cl-price">Rp <?= number_format((float)$produk['harga'],0,',','.') ?></div>
  <p style="color:#786b79;line-height:1.9"><?= nl2br(htmlspecialchars($produk['deskripsi'] ?? 'Belum ada deskripsi produk.')) ?></p>
  <div class="d-flex gap-2 flex-wrap my-4"><span class="cl-status"><?= (int)$produk['stok']>0?'Tersedia':'Stok Habis' ?></span><span class="cl-status"><?= htmlspecialchars($produk['nama_kategori'] ?? 'Dessert') ?></span></div>
  <div class="cl-panel mt-4" style="background:#fffafd"><strong><i class="bi bi-shop me-2"></i>Pembelian langsung di toko</strong><p class="small text-muted mb-0 mt-2">Produk dapat dibeli dengan datang langsung ke toko. Silakan lakukan transaksi melalui kasir.</p></div>
  <div class="border-top mt-4 pt-3 small text-muted"><i class="bi bi-shield-check me-2"></i>Informasi produk dan harga mengikuti data katalog Clairmont Dessert.</div>
 </div>
</div>
<section id="ulasan" class="cl-panel mt-4">
 <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div><span class="cl-eyebrow">Customer Feedback</span><h2 class="h4 mb-1">Ulasan Produk</h2><div class="text-muted small"><?= number_format($rataRating,1) ?> / 5 · <?= $jumlahUlasan ?> ulasan</div></div>
  <div class="text-warning fs-5"><?= $jumlahUlasan ? str_repeat('★',(int)round($rataRating)).str_repeat('☆',5-(int)round($rataRating)) : '☆☆☆☆☆' ?></div>
 </div>
 <?php if ($pesanUlasan === 'sukses'): ?><div class="alert alert-success">Ulasan berhasil disimpan.</div>
 <?php elseif ($pesanUlasan === 'belum_beli'): ?><div class="alert alert-warning">Ulasan hanya dapat ditulis oleh member yang sudah membeli produk ini melalui POS dan transaksinya sudah selesai.</div>
 <?php elseif ($pesanUlasan === 'member'): ?><div class="alert alert-warning">Akun member tidak ditemukan. Silakan periksa akun Anda.</div>
 <?php elseif ($pesanUlasan === 'invalid' || $pesanUlasan === 'gagal'): ?><div class="alert alert-danger">Ulasan belum berhasil disimpan. Pastikan rating dan komentar sudah diisi.</div><?php endif; ?>
 <?php if ($isMember): ?>
  <?php if ($bolehUlas): ?>
   <form method="post" action="form/barang/proses_ulasan.php" class="p-3 mb-4" style="background:#fffafd;border:1px solid #f0ddee;border-radius:18px">
    <input type="hidden" name="id_produk" value="<?= (int)$idProduk ?>">
    <label class="form-label fw-semibold">Rating produk</label>
    <select name="rating" class="form-select mb-3" required style="max-width:180px"><option value="">Pilih rating</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>" <?= (int)($ulasanSaya['rating'] ?? 0)===$i?'selected':'' ?>><?= $i ?> bintang</option><?php endfor; ?></select>
    <label class="form-label fw-semibold">Tulis ulasan</label>
    <textarea name="komentar" class="form-control mb-3" rows="3" maxlength="1000" required placeholder="Bagaimana pengalaman Anda dengan produk ini?"><?= htmlspecialchars($ulasanSaya['komentar'] ?? '') ?></textarea>
    <button class="cl-btn" type="submit"><?= $ulasanSaya ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?></button>
    <?php if ($ulasanSaya): ?><span class="small text-muted ms-2">Anda sudah pernah memberi ulasan. Ulasan ini bisa diperbarui.</span><?php endif; ?>
   </form>
  <?php else: ?><div class="alert alert-info">Anda sudah login sebagai member. Untuk menulis ulasan, Anda harus pernah membeli produk ini melalui POS dan transaksinya berstatus selesai.</div><?php endif; ?>
 <?php else: ?>
  <div class="alert alert-light border">Anda dapat membaca ulasan produk. Ingin memberikan ulasan? <a href="login.php?next=<?= urlencode('dashboard_customer.php?menu=detail_produk&id='.$idProduk.'#ulasan') ?>">Login sebagai member</a>.</div>
 <?php endif; ?>
 <?php if ($jumlahUlasan): foreach($ulasanList as $u): ?>
  <article class="py-3 border-top">
   <div class="d-flex justify-content-between flex-wrap gap-2"><strong><?= htmlspecialchars($u['nama_customer']) ?></strong><span class="text-warning"><?= str_repeat('★',(int)$u['rating']) . str_repeat('☆',5-(int)$u['rating']) ?></span></div>
   <p class="mb-1 mt-2" style="white-space:pre-line"><?= htmlspecialchars($u['komentar']) ?></p>
   <small class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></small>
  </article>
 <?php endforeach; else: ?><p class="text-muted mb-0">Belum ada ulasan untuk produk ini.</p><?php endif; ?>
</section>
