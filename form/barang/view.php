<?php
require_once __DIR__ . '/koneksi2.php';
$kategoriAktif = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$cari = trim($_GET['q'] ?? '');
$kategoriResult = mysqli_query($koneksi, "SELECT id_kategori,nama_kategori FROM tb_kategori WHERE status='aktif' ORDER BY nama_kategori");
$where = ["p.status='aktif'"];
if ($kategoriAktif > 0) $where[] = "p.id_kategori=" . $kategoriAktif;
if ($cari !== '') $where[] = "p.nama_produk LIKE '%" . mysqli_real_escape_string($koneksi,$cari) . "%'";
$sql = "SELECT p.id_produk,p.nama_produk,p.harga,p.gambar,p.deskripsi,p.stok,p.status,k.nama_kategori, COALESCE(AVG(u.rating),0) AS rata_rating, COUNT(u.id_ulasan) AS jumlah_ulasan FROM tb_produk p LEFT JOIN tb_kategori k ON p.id_kategori=k.id_kategori LEFT JOIN tb_ulasan_produk u ON u.id_produk=p.id_produk WHERE " . implode(' AND ',$where) . " GROUP BY p.id_produk ORDER BY p.created_at DESC";
$produkResult = mysqli_query($koneksi,$sql);
?>
<div class="cl-page-head">
 <span class="cl-eyebrow">Clairmont Patisserie · Est. 1997</span>
 <h1 class="cl-page-title">Our Products &amp; <em>Artisanal Delights</em></h1>
 <p>Jelajahi koleksi cake, pastry, cookies, dan dessert pilihan Clairmont.</p>
</div>
<div class="cl-filter-panel">
 <form method="get" action="dashboard_customer.php" class="row g-3 align-items-end">
  <input type="hidden" name="menu" value="produk">
  <div class="col-lg-5"><label class="cl-form-label">Cari produk</label><input class="cl-form-control" type="search" name="q" value="<?= htmlspecialchars($cari) ?>" placeholder="Temukan cake, pastry, cookies..."></div>
  <div class="col-lg-4"><label class="cl-form-label">Kategori</label><select class="cl-form-control" name="kategori"><option value="0">Semua kategori</option><?php if($kategoriResult): while($k=mysqli_fetch_assoc($kategoriResult)): ?><option value="<?= (int)$k['id_kategori'] ?>" <?= $kategoriAktif===(int)$k['id_kategori']?'selected':'' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option><?php endwhile; endif; ?></select></div>
  <div class="col-lg-3 d-flex gap-2"><button class="cl-btn flex-grow-1" type="submit"><i class="bi bi-search"></i> Cari Produk</button><a href="dashboard_customer.php?menu=produk" class="cl-btn cl-btn-outline">Reset</a></div>
 </form>
</div>
<div class="d-flex justify-content-between align-items-center mb-3"><span class="text-muted small">Koleksi produk Clairmont Dessert</span><span class="text-muted small"><?= $produkResult ? mysqli_num_rows($produkResult) : 0 ?> produk ditemukan</span></div>
<div class="cl-product-grid">
<?php if($produkResult && mysqli_num_rows($produkResult)>0): while($p=mysqli_fetch_assoc($produkResult)): ?>
 <article class="cl-product-card">
  <div class="cl-product-img"><img src="uploads/<?= htmlspecialchars($p['gambar'] ?? '') ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" onerror="this.src='dist/assets/img/logo.png'"></div>
  <div class="cl-product-info">
   <span class="cl-product-tag"><?= htmlspecialchars($p['nama_kategori'] ?? 'Dessert') ?></span>
   <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
   <div class="small text-warning mb-2"><?= (int)$p['jumlah_ulasan'] > 0 ? str_repeat('★',(int)round((float)$p['rata_rating'])).str_repeat('☆',5-(int)round((float)$p['rata_rating'])) : '☆☆☆☆☆' ?> <span class="text-muted">(<?= (int)$p['jumlah_ulasan'] ?> ulasan)</span></div>
   <p><?= htmlspecialchars(mb_strimwidth(strip_tags($p['deskripsi'] ?? ''),0,105,'…')) ?></p>
   <div class="cl-price">Rp <?= number_format((float)$p['harga'],0,',','.') ?></div>
   <div class="cl-card-actions">
    <a href="dashboard_customer.php?menu=detail_produk&id=<?= (int)$p['id_produk'] ?>" class="cl-btn cl-btn-outline">Lihat Detail</a>
    <?php if((int)$p['stok']<=0): ?><span class="cl-status">Stok Habis</span><?php endif; ?>
   </div>
  </div>
 </article>
<?php endwhile; else: ?><div class="cl-empty" style="grid-column:1/-1"><i class="bi bi-search fs-1"></i><h3 class="mt-3">Produk tidak ditemukan</h3><p>Coba kata kunci atau kategori yang berbeda.</p></div><?php endif; ?>
</div>