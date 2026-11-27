<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (($_SESSION['role'] ?? '') === 'customer' || empty($_SESSION['login'])) {
    header('Location: ../../dashboard_customer.php?menu=produk');
    exit;
}
?>
<div class="container mt-5 text-center">
  <h3 class="fw-bold text-success">✅ Pesanan Berhasil</h3>
  <p>Terima kasih telah berbelanja</p>


  <a href="dashboard_customer.php"
     class="btn btn-secondary mt-3">
     Kembali ke Home
  </a>
</div>
