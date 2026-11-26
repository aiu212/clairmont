<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'sales') {
    header('Location: ../../login.php');
    exit;
}
$sale = isset($_SESSION['pos_last_sale']) ? $_SESSION['pos_last_sale'] : null;
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Transaksi Berhasil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#fff8fa;font-family:Segoe UI,Arial;color:#543d4c}.receipt{max-width:520px;margin:8vh auto;background:white;border:1px solid #f0dce3;border-radius:20px;padding:32px;box-shadow:0 10px 30px #8c61701a}.check{font-size:54px;color:#4b9b6b}</style></head><body>
<div class="receipt text-center">
<?php if ($sale) { ?>
<div class="check">✓</div><h2 class="fw-bold">Transaksi Berhasil!</h2><p class="text-secondary">Pembayaran POS berhasil dicatat.</p><hr>
<div class="d-flex justify-content-between py-2"><span>Kode transaksi</span><strong><?php echo htmlspecialchars($sale['kode'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
<div class="d-flex justify-content-between py-2"><span>Tanggal</span><strong><?php echo htmlspecialchars($sale['tanggal'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
<div class="d-flex justify-content-between py-2"><span>Metode</span><strong><?php echo htmlspecialchars($sale['metode'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
<div class="d-flex justify-content-between py-3 fs-5"><span>Total</span><strong>Rp <?php echo number_format((int)$sale['total'],0,',','.'); ?></strong></div>
<button class="btn btn-outline-secondary w-100 mb-2" onclick="window.print()">Cetak Bukti</button>
<?php unset($_SESSION['pos_last_sale']); ?>
<?php } else { ?><h3>Tidak ada ringkasan transaksi.</h3><?php } ?>
<a href="pos.php" class="btn w-100" style="background:linear-gradient(120deg,#f1b4c5,#dceaff);color:#593e50;font-weight:700">Kembali ke POS</a>
</div></body></html>
