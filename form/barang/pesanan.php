<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') === 'customer' || empty($_SESSION['login'])) {
    header('Location: ../../dashboard_customer.php?menu=produk');
    exit;
}
?>
<?php
require_once __DIR__ . '/koneksi2.php';
$id_user=(int)$_SESSION['id_user'];
$stmt=mysqli_prepare($koneksi,"SELECT t.id_transaksi,t.kode_transaksi,t.tanggal_transaksi,t.total_harga,t.status_transaksi FROM tb_transaksi t INNER JOIN tb_customer c ON t.id_customer=c.id_customer WHERE c.id_user=? ORDER BY t.tanggal_transaksi DESC");
mysqli_stmt_bind_param($stmt,'i',$id_user); mysqli_stmt_execute($stmt); $q=mysqli_stmt_get_result($stmt);
?>
<div class="cl-page-head"><span class="cl-eyebrow">Your orders</span><h1 class="cl-page-title">Pesanan <em>Saya</em></h1><p>Pantau pesanan dan lihat kembali detail transaksi kamu.</p></div>
<div class="cl-panel table-responsive">
<?php if(!$q || mysqli_num_rows($q)===0): ?><div class="cl-empty"><i class="bi bi-receipt fs-1"></i><h3 class="mt-3">Belum ada pesanan</h3><p>Pesanan yang kamu buat akan muncul di sini.</p><a class="cl-btn" href="dashboard_customer.php?menu=produk">Mulai Belanja</a></div>
<?php else: ?><table class="cl-data-table"><thead><tr><th>No. Pesanan</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Detail</th></tr></thead><tbody>
<?php while($t=mysqli_fetch_assoc($q)): ?><tr><td class="fw-bold"><?= htmlspecialchars($t['kode_transaksi'] ?: ('TRX-'.$t['id_transaksi'])) ?></td><td><?= date('d M Y',strtotime($t['tanggal_transaksi'])) ?></td><td>Rp <?= number_format((float)$t['total_harga'],0,',','.') ?></td><td><span class="cl-status"><?= htmlspecialchars(ucfirst($t['status_transaksi'])) ?></span></td><td><a class="cl-link" href="dashboard_customer.php?menu=pesanan_detail&id=<?= (int)$t['id_transaksi'] ?>">Lihat Detail <i class="bi bi-arrow-up-right"></i></a></td></tr><?php endwhile; ?>
</tbody></table><?php endif; ?>
</div>
