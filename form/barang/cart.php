<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') === 'customer' || empty($_SESSION['login'])) {
    header('Location: ../../dashboard_customer.php?menu=produk');
    exit;
}
?>
<?php
require_once __DIR__ . '/koneksi2.php';
$keranjang = $_SESSION['keranjang'] ?? [];
$total = 0;
?>
<div class="cl-page-head"><span class="cl-eyebrow">Your selection</span><h1 class="cl-page-title">Shopping <em>Bag</em></h1><p>Periksa kembali pilihan dessert sebelum melanjutkan pesanan.</p></div>
<?php if(empty($keranjang)): ?>
 <div class="cl-empty"><i class="bi bi-bag fs-1"></i><h3 class="mt-3">Keranjang masih kosong</h3><p>Yuk, temukan dessert favoritmu.</p><a class="cl-btn mt-2" href="dashboard_customer.php?menu=produk">Jelajahi Produk</a></div>
<?php else: ?>
 <div class="cl-panel table-responsive"><table class="cl-data-table">
 <thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr></thead><tbody>
 <?php foreach($keranjang as $item): $subtotal=(float)$item['harga']*(int)$item['qty']; $total += $subtotal; ?>
 <tr><td><div class="d-flex align-items-center gap-3"><img src="uploads/<?= htmlspecialchars($item['gambar']??'') ?>" onerror="this.src='dist/assets/img/logo.png'" style="width:66px;height:66px;object-fit:cover;border-radius:14px"><strong><?= htmlspecialchars($item['nama_produk']) ?></strong></div></td>
 <td>Rp <?= number_format((float)$item['harga'],0,',','.') ?></td>
 <td><div class="d-flex align-items-center gap-2">
 <form action="form/barang/cart_update.php" method="post"><input type="hidden" name="id" value="<?= (int)$item['id_produk'] ?>"><input type="hidden" name="aksi" value="kurang"><button class="btn btn-sm btn-light" type="submit">−</button></form>
 <strong><?= (int)$item['qty'] ?></strong>
 <form action="form/barang/cart_update.php" method="post"><input type="hidden" name="id" value="<?= (int)$item['id_produk'] ?>"><input type="hidden" name="aksi" value="tambah"><button class="btn btn-sm btn-light" type="submit">+</button></form></div></td>
 <td class="fw-bold">Rp <?= number_format($subtotal,0,',','.') ?></td>
 <td><form action="form/barang/cart_update.php" method="post" onsubmit="return confirm('Hapus produk ini dari keranjang?')"><input type="hidden" name="id" value="<?= (int)$item['id_produk'] ?>"><input type="hidden" name="aksi" value="hapus"><button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form></td></tr>
 <?php endforeach; ?></tbody></table>
 <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top pt-4 mt-3"><a href="dashboard_customer.php?menu=produk" class="cl-link"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a><div class="text-end"><div class="small text-muted">Total Pembayaran</div><div class="fs-4 fw-bold mb-3" style="color:#834f5e">Rp <?= number_format($total,0,',','.') ?></div><a href="dashboard_customer.php?menu=checkout" class="cl-btn">Lanjut Checkout <i class="bi bi-arrow-right"></i></a></div></div>
 </div>
<?php endif; ?>
