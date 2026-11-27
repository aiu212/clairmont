<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login Member | Clairmont Patisserie</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="customer-template.css"></head>
<body class="customer-site">
<div class="container py-4"><a class="customer-brand" href="dashboard_customer.php"><span><strong>Clairmont</strong><small>Patisserie</small></span></a>
<div class="cl-auth-wrap">
 <div class="cl-auth-visual"><span class="cl-eyebrow">Clairmont Member Circle</span><h2 class="mt-4">Sweet moments are better when shared.</h2><p class="mb-0">Masuk ke akunmu untuk melihat informasi akun Member Clairmont.</p></div>
 <div class="cl-auth-form align-self-center"><span class="cl-eyebrow">Welcome back</span><h1>Masuk ke Akun</h1><p class="text-muted mb-4">Silakan login untuk masuk ke akun Member Clairmont.</p>
 <form action="proses_login.php" method="post">
  <input type="hidden" name="next" value="<?= htmlspecialchars($_GET['next'] ?? '', ENT_QUOTES) ?>">
  <label class="cl-form-label">Alamat Email</label><input type="email" name="txtUsername" autocomplete="username" class="form-control mb-3" placeholder="nama@email.com" required>
  <label class="cl-form-label">Kata Sandi</label><input type="password" name="txtPassword" autocomplete="current-password" class="form-control mb-4" placeholder="Masukkan kata sandi" required>
  <button type="submit" name="btnLogin" class="cl-btn w-100">Login <i class="bi bi-arrow-right"></i></button>
 </form>
 <p class="text-center mt-4 mb-2 small text-muted">Belum punya akun? <a href="register.php" style="color:#834f5e;font-weight:700">Daftar Member</a></p>
 <p class="text-center small"><a href="dashboard_customer.php" style="color:#786b79">Kembali ke halaman utama</a></p>
 </div>
</div></div></body></html>