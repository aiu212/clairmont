<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Daftar Member | Clairmont Patisserie</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="customer-template.css"></head>
<body class="customer-site">
<div class="container py-4"><a class="customer-brand" href="dashboard_customer.php"><span><strong>Clairmont</strong><small>Patisserie</small></span></a>
<div class="cl-auth-wrap">
 <div class="cl-auth-visual"><span class="cl-eyebrow">Join the sweet circle</span><h2 class="mt-4">A little sweetness, a lot of memories.</h2><p class="mb-0">Buat akun Member untuk menyimpan data diri dan melihat informasi produk Clairmont.</p></div>
 <div class="cl-auth-form align-self-center"><span class="cl-eyebrow">Become a member</span><h1>Daftar Member Baru</h1><p class="text-muted mb-4">Isi data berikut untuk membuat akun Clairmont.</p>
 <form action="proses_register.php" method="post">
  <label class="cl-form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control mb-3" placeholder="Nama lengkap" required>
  <label class="cl-form-label">Alamat Email</label><input type="email" name="email" class="form-control mb-3" placeholder="nama@email.com" required>
  <label class="cl-form-label">Nomor Telepon</label><input type="tel" name="no_hp" class="form-control mb-3" placeholder="Contoh: 081234567890" pattern="[0-9+() -]{8,20}" maxlength="20" required>
  <label class="cl-form-label">Alamat</label><textarea name="alamat" class="form-control mb-3" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
  <label class="cl-form-label">Kata Sandi</label><input type="password" name="password" minlength="6" class="form-control mb-4" placeholder="Minimal 6 karakter" required>
  <button type="submit" name="btnRegister" class="cl-btn w-100">Buat Akun <i class="bi bi-arrow-right"></i></button>
 </form>
 <p class="text-center mt-4 mb-2 small text-muted">Sudah punya akun? <a href="login.php" style="color:#834f5e;font-weight:700">Login</a></p>
 <p class="text-center small"><a href="dashboard_customer.php" style="color:#786b79">Kembali ke halaman utama</a></p>
 </div>
</div></div></body></html>