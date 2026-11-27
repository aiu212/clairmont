<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') === 'customer' || empty($_SESSION['login'])) {
    header('Location: ../../dashboard_customer.php?menu=produk');
    exit;
}
?>
<?php
require_once __DIR__ . '/koneksi2.php';
$id_transaksi=isset($_GET['id'])?(int)$_GET['id']:0;
$id_user=(int)$_SESSION['id_user'];
$stmt=mysqli_prepare($koneksi,"SELECT t.* FROM tb_transaksi t INNER JOIN tb_customer c ON t.id_customer=c.id_customer WHERE t.id_transaksi=? AND c.id_user=? LIMIT 1");
mysqli_stmt_bind_param($stmt,'ii',$id_transaksi,$id_user); mysqli_stmt_execute($stmt); $transaksi=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
if(!$transaksi){echo "<div class='cl-empty'><h3>Pesanan tidak ditemukan</h3><a class='cl-btn' href='dashboard_customer.php?menu=pesanan'>Kembali</a></div>";return;}
$stmtD=mysqli_prepare($koneksi,"SELECT d.jumlah,d.harga,d.subtotal,p.nama_produk,p.gambar FROM tb_detail_transaksi d JOIN tb_produk p ON d.id_produk=p.id_produk WHERE d.id_transaksi=?");
mysqli_stmt_bind_param($stmtD,'i',$id_transaksi); mysqli_stmt_execute($stmtD); $qDetail=mysqli_stmt_get_result($stmtD);
?>
<div class="cl-page-head"><span class="cl-eyebrow">Order details</span><h1 class="cl-page-title">Detail <em>Pesanan</em></h1><p>Informasi lengkap transaksi kamu.</p></div>
<div class="cl-panel mb-4"><div class="row g-3"><div class="col-md-3"><small class="text-muted">Kode Transaksi</small><div class="fw-bold"><?= htmlspecialchars($transaksi['kode_transaksi'] ?: 'TRX-'.$transaksi['id_transaksi']) ?></div></div><div class="col-md-3"><small class="text-muted">Tanggal</small><div class="fw-bold"><?= date('d M Y H:i',strtotime($transaksi['tanggal_transaksi'])) ?></div></div><div class="col-md-3"><small class="text-muted">Status</small><div><span class="cl-status"><?= htmlspecialchars(ucfirst($transaksi['status_transaksi'])) ?></span></div></div><div class="col-md-3"><small class="text-muted">Total Pembayaran</small><div class="fw-bold" style="color:#834f5e">Rp <?= number_format((float)$transaksi['total_harga'],0,',','.') ?></div></div></div></div>
<div class="cl-panel table-responsive"><h2 class="h5 mb-3" style="font-family:'Playfair Display',serif">Produk dalam Pesanan</h2><table class="cl-data-table"><thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead><tbody>
<?php while($d=mysqli_fetch_assoc($qDetail)): ?><tr><td><div class="d-flex align-items-center gap-3"><img src="uploads/<?= htmlspecialchars($d['gambar']??'') ?>" onerror="this.src='dist/assets/img/logo.png'" style="width:58px;height:58px;object-fit:cover;border-radius:12px"><strong><?= htmlspecialchars($d['nama_produk']) ?></strong></div></td><td>Rp <?= number_format((float)$d['harga'],0,',','.') ?></td><td><?= (int)$d['jumlah'] ?></td><td class="fw-bold">Rp <?= number_format((float)($d['subtotal'] ?? ($d['harga']*$d['jumlah'])),0,',','.') ?></td></tr><?php endwhile; ?>
</tbody></table><div class="d-flex justify-content-end mt-4"><a class="cl-btn cl-btn-outline" href="dashboard_customer.php?menu=pesanan"><i class="bi bi-arrow-left"></i> Kembali ke Pesanan</a></div></div>
