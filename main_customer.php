<?php
require_once __DIR__ . '/koneksi2.php';
$kategoriList = [];
$qKat = mysqli_query($koneksi, "SELECT id_kategori,nama_kategori,gambar FROM tb_kategori WHERE status='aktif' ORDER BY id_kategori LIMIT 8");
if ($qKat) while ($row = mysqli_fetch_assoc($qKat)) $kategoriList[] = $row;
$produkList = [];
$qProduk = mysqli_query($koneksi, "SELECT id_produk,nama_produk,harga,gambar,deskripsi,stok,status FROM tb_produk WHERE status='aktif' ORDER BY created_at DESC LIMIT 8");
if ($qProduk) while ($row = mysqli_fetch_assoc($qProduk)) $produkList[] = $row;

// Ulasan Home diambil langsung dari database, bukan testimoni statis.
$ulasanHome = [];
$ringkasanUlasan = ['jumlah' => 0, 'rata' => 0];
$qRingkasan = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah, COALESCE(AVG(rating),0) AS rata FROM tb_ulasan_produk");
if ($qRingkasan) $ringkasanUlasan = mysqli_fetch_assoc($qRingkasan);
$qUlasanHome = mysqli_query($koneksi, "SELECT u.rating, u.komentar, u.created_at, c.nama_customer, p.nama_produk
    FROM tb_ulasan_produk u
    INNER JOIN tb_customer c ON c.id_customer = u.id_customer
    INNER JOIN tb_produk p ON p.id_produk = u.id_produk
    ORDER BY u.created_at DESC, u.id_ulasan DESC LIMIT 3");
if ($qUlasanHome) while ($row = mysqli_fetch_assoc($qUlasanHome)) $ulasanHome[] = $row;
?>
<div class="cl-hero">
 <div class="cl-hero-content">
  <span class="cl-eyebrow"><i class="bi bi-stars"></i> Crafted with care · Freshly made</span>
  <h1>Sweet Moments,<br><em>Made with Love.</em></h1>
  <p>Temukan cake, pastry, cookies, dan dessert pilihan untuk menemani momen spesialmu. Setiap kreasi dibuat dengan penuh perhatian agar setiap gigitan terasa istimewa.</p>
  <div class="d-flex flex-wrap gap-3">
   <a href="dashboard_customer.php?menu=produk" class="cl-btn">Jelajahi Produk <i class="bi bi-arrow-up-right"></i></a>
   <?php if (empty($_SESSION['login'])): ?><a href="register.php" class="cl-btn cl-btn-outline"><i class="bi bi-person-plus"></i> Gabung Member</a><?php endif; ?>
  </div>
  <div class="cl-trust-row">
   <div class="cl-trust"><i class="bi bi-heart-fill me-2"></i>Dibuat dengan penuh cinta</div>
   <div class="cl-trust"><i class="bi bi-cake2 me-2"></i>Beragam pilihan dessert</div>
   <div class="cl-trust"><i class="bi bi-gift me-2"></i>Untuk setiap momen spesial</div>
  </div>
 </div>
</div>

<section class="cl-section">
 <div class="cl-section-head">
  <div><span class="cl-eyebrow">Explore our collection</span><h2>Koleksi Favorit <em>Pilihanmu</em></h2><p class="cl-section-sub">Temukan berbagai pilihan manis yang bisa kamu nikmati bersama orang tersayang.</p></div>
  <a class="cl-link" href="dashboard_customer.php?menu=produk">Lihat semua produk <i class="bi bi-arrow-up-right"></i></a>
 </div>
 <div class="cl-category-grid">
 <?php foreach($kategoriList as $kat): ?>
  <a class="cl-category-card" href="dashboard_customer.php?menu=produk&kategori=<?= (int)$kat['id_kategori'] ?>">
   <img src="uploads/kategori/<?= htmlspecialchars($kat['gambar'] ?: 'dessertbox.jpeg') ?>" alt="<?= htmlspecialchars($kat['nama_kategori']) ?>" onerror="this.src='dist/assets/img/logo.png'">
   <span><?= htmlspecialchars($kat['nama_kategori']) ?></span>
  </a>
 <?php endforeach; ?>
 </div>
</section>

<section class="cl-section" id="koleksi-favorit">
 <div class="cl-section-head">
  <div><span class="cl-eyebrow">Made for your happy days</span><h2>Best Seller <em>Collection</em></h2><p class="cl-section-sub">Pilihan produk Clairmont yang tersedia untuk menemani hari-harimu.</p></div>
  <a class="cl-link" href="dashboard_customer.php?menu=produk">Jelajahi katalog <i class="bi bi-arrow-up-right"></i></a>
 </div>
 <div class="cl-product-grid">
 <?php if ($produkList): foreach($produkList as $p): ?>
  <article class="cl-product-card">
   <div class="cl-product-img"><img src="uploads/<?= htmlspecialchars($p['gambar'] ?? '') ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" onerror="this.src='dist/assets/img/logo.png'"></div>
   <div class="cl-product-info">
    <span class="cl-product-tag"><?= ($p['status']==='promo')?'Special Offer':'Clairmont Selection' ?></span>
    <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
    <p><?= htmlspecialchars(mb_strimwidth(strip_tags($p['deskripsi'] ?? ''),0,100,'…')) ?></p>
    <div class="cl-price">Rp <?= number_format((float)$p['harga'],0,',','.') ?></div>
    <div class="cl-card-actions">
     <a class="cl-btn cl-btn-outline" href="dashboard_customer.php?menu=detail_produk&id=<?= (int)$p['id_produk'] ?>">Lihat Detail</a>

    </div>
   </div>
  </article>
 <?php endforeach; else: ?><div class="cl-empty" style="grid-column:1/-1">Produk belum tersedia.</div><?php endif; ?>
 </div>
</section>


<!-- SECTION: Cerita Manis dari Keluarga Clairmont (Home) -->
<section class="cl-section cl-home-testimonials" id="cerita-pelanggan">
 <div class="cl-testimonial-heading">
  <div class="cl-testimonial-badge"><span>★</span>
   <?php if ((int)$ringkasanUlasan['jumlah'] > 0): ?>
    <?= number_format((float)$ringkasanUlasan['rata'], 1, ',', '.') ?> / 5 dari <?= (int)$ringkasanUlasan['jumlah'] ?> Ulasan Pelanggan
   <?php else: ?>Belum ada ulasan pelanggan<?php endif; ?>
  </div>
  <h2>Cerita Manis dari <em>Keluarga Clairmont</em></h2>
  <p>Pengalaman pelanggan Clairmont yang dibagikan langsung melalui ulasan produk.</p>
 </div>
 <div class="cl-testimonials">
  <?php if ($ulasanHome): foreach ($ulasanHome as $u):
   $namaUlasan = $u['nama_customer'] ?: 'Pelanggan Clairmont';
   $inisial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($namaUlasan, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($namaUlasan, 0, 1));
   $ratingUlasan = max(1, min(5, (int)$u['rating']));
  ?>
  <article class="cl-quote cl-testimonial-card">
   <div class="cl-testimonial-person">
    <div class="cl-testimonial-avatar" aria-hidden="true"><?= htmlspecialchars($inisial) ?></div>
    <div><h3><?= htmlspecialchars($namaUlasan) ?></h3><p>Pelanggan Clairmont</p></div>
   </div>
   <div class="cl-testimonial-stars" aria-label="Rating <?= $ratingUlasan ?> dari 5"><?= str_repeat('★', $ratingUlasan) . str_repeat('☆', 5 - $ratingUlasan) ?></div>
   <p class="cl-testimonial-comment">&ldquo;<?= nl2br(htmlspecialchars($u['komentar'])) ?>&rdquo;</p>
   <div class="cl-testimonial-order"><span>Produk: <?= htmlspecialchars($u['nama_produk']) ?></span><strong><?= str_repeat('★', $ratingUlasan) ?></strong></div>
  </article>
  <?php endforeach; else: ?>
   <div class="cl-empty" style="grid-column:1/-1">Belum ada ulasan pelanggan. Ulasan yang dikirim pada halaman detail produk akan tampil di sini.</div>
  <?php endif; ?>
 </div>
</section>

<section class="cl-section">
 <div class="cl-about" id="tentang">
  <div><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCzXm4y2nejI7LmDcOQVu25fsinLUA8HMKy0L03SquWmpLE7jJdeU1rzaCZ_QCnDuERBeU2mAPg_KpwPN6xuHv_FUdDv-2A1F0Sv1GcI-TuBTvMYqh5ZKw_ghs6R3Bu3hE1rm-hfgPgg27ubdoPfsurWmF3V5HvoFwqBgz9EfbQzdmB9kW5hJaG98QHB6gbYwWcwLrOmKD91_h7aV7R5FwgHP9_l0T8rph_v5P2IXA" alt="Clairmont patisserie" onerror="this.style.display='none'"></div>
  <div><span class="cl-eyebrow">Our story</span><h2>Hadirkan momen manis dalam setiap kesempatan.</h2><p class="cl-section-sub">Clairmont Dessert menghadirkan beragam pilihan cake dan dessert untuk menemani waktu santai maupun perayaan istimewa. Kami percaya, hal sederhana seperti berbagi dessert bisa menjadi kenangan yang berarti.</p><a class="cl-btn mt-4" href="dashboard_customer.php?menu=produk">Temukan Favoritmu <i class="bi bi-arrow-right"></i></a></div>
 </div>
</section>
<section class="cl-section" id="faq">
 <div class="cl-section-head"><div><span class="cl-eyebrow">Need a little help?</span><h2>Frequently Asked <em>Questions</em></h2><p class="cl-section-sub">Informasi singkat mengenai katalog dan pembelian produk Clairmont.</p></div></div>
 <div class="accordion" id="customerFaq">
  <div class="accordion-item border-0 rounded-4 overflow-hidden mb-2"><h3 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Bagaimana cara memesan produk?</button></h3><div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#customerFaq"><div class="accordion-body">Pelanggan dapat melihat katalog produk melalui website. Untuk membeli, silakan datang langsung ke toko dan melakukan transaksi melalui kasir (POS).</div></div></div>
  <div class="accordion-item border-0 rounded-4 overflow-hidden mb-2"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Apakah harus menjadi Member untuk melihat produk?</button></h3><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#customerFaq"><div class="accordion-body">Tidak. Semua pengunjung dapat melihat katalog dan detail produk tanpa harus login atau menjadi Member.</div></div></div>
  <div class="accordion-item border-0 rounded-4 overflow-hidden mb-2"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Metode pembayaran apa yang tersedia?</button></h3><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#customerFaq"><div class="accordion-body">Pembelian dilakukan langsung di toko melalui kasir. Pilihan pembayaran dapat ditanyakan kepada petugas kasir.</div></div></div>
 </div>
</section>
