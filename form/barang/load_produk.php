<?php
session_start();
include "../../koneksi2.php";

$role = $_SESSION['role'] ?? 'customer';
$idKategori = $_GET['kategori'] ?? '';

$sql = mysqli_query($koneksi, "
    SELECT p.id_produk, p.nama_produk, p.harga, p.gambar, p.stok
    FROM tb_produk p
    WHERE p.id_kategori = '$idKategori'
    ORDER BY p.nama_produk
");

if (mysqli_num_rows($sql) == 0) {
    echo "<div class='alert alert-warning'>Produk belum tersedia</div>";
    exit;
}
?>

<!-- ================= CUSTOMER CARD VIEW ================= -->
<div class="row g-4">

<?php while ($p = mysqli_fetch_assoc($sql)) { ?>

<div class="col-lg-4 col-md-6">
  <div class="card border-0 shadow-sm h-100 product-card">

    <!-- IMAGE -->
    <div class="ratio ratio-1x1">
      <img src="uploads/<?= $p['gambar']; ?>"
           class="card-img-top object-fit-cover rounded-top">
    </div>

    <!-- BODY -->
    <div class="card-body text-center">
      <h5 class="fw-semibold mb-2"><?= $p['nama_produk']; ?></h5>

      <p class="fw-bold text-dark fs-5 mb-1">
        Rp <?= number_format($p['harga']); ?>
      </p>

      <p class="text-muted mb-3">
        Stok: <strong><?= $p['stok']; ?></strong>
      </p>

      <!-- CUSTOMER: hanya melihat detail produk -->
      <?php if ($role == 'customer') { ?>
        <a href="dashboard_customer.php?menu=detail_produk&id=<?= (int)$p['id_produk']; ?>"
           class="btn btn-outline-secondary w-100 rounded-pill mt-2">Lihat Detail</a>
      <?php } ?>

      <!-- SALES -->
      <?php if ($role == 'sales') { ?>
        <div class="mt-3">
          <a href="dashboard_admin.php?menu=produk_edit&id=<?= $p['id_produk']; ?>"
             class="btn btn-warning btn-sm me-1">Edit</a>

          <a href="form/barang/hapus.php?id=<?= $p['id_produk']; ?>"
             onclick="return confirm('Hapus produk ini?')"
             class="btn btn-danger btn-sm">Hapus</a>
        </div>
      <?php } ?>

    </div>

  </div>
</div>

<?php } ?>

</div>

<!-- ================= STYLE ================= -->
<style>
.product-card {
  transition: all .3s ease;
}
.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(0,0,0,.15);
}
.product-card img {
  border-radius: 16px 16px 0 0;
}
</style>
