
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Register | Clairmont Dessert</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="dist/css/adminlte.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<style>
* {
  font-family: "Segoe UI", sans-serif;
}

body {
  background-color: #ffe4e9;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 25px 0;
}

.register-box {
  width: 440px;
  max-width: 92%;
}

.card {
  background-color: #e6f0ff;
  border-radius: 22px;
  border: none;
  box-shadow: 0 16px 35px rgba(0,0,0,0.15);
  overflow: hidden;
}

.card-header {
  background-color: #f3b6c2;
  color: #3b2f2f;
  text-align: center;
  padding: 1.6rem;
}

.card-header h3 {
  font-weight: 700;
  margin-bottom: 4px;
}

.register-card-body {
  background-color: #ffffff;
  padding: 2.2rem;
}

.input-group-text {
  background-color: #f3b6c2;
  border: none;
  color: #3b2f2f;
  border-radius: 14px 0 0 14px;
  min-width: 48px;
  justify-content: center;
}

.form-control {
  border: 1px solid #e6f0ff;
  border-left: none;
  border-radius: 0 14px 14px 0;
  padding: 12px;
  color: #3b2f2f;
  height: 48px;
}

.form-control:focus {
  box-shadow: none;
  border-color: #f3b6c2;
}

.btn-primary {
  background-color: #f3b6c2;
  border: none;
  border-radius: 16px;
  padding: 12px;
  font-weight: 700;
  color: #3b2f2f;
  transition: 0.25s;
}

.btn-primary:hover {
  background-color: #e7a2b0;
  transform: translateY(-1px);
  color: #3b2f2f;
}

a {
  color: #f3b6c2;
  font-weight: 600;
}

a:hover {
  color: #d98a99;
  text-decoration: none;
}

@media (max-width: 576px) {
  .register-card-body {
    padding: 1.5rem;
  }
}
</style>
</head>

<body class="register-page">

<div class="register-box">

  <div class="card">

    <div class="card-header text-center">
      <h3 class="fw-bold mb-0">Registrasi Member</h3>
      <small>Gabung menjadi bagian dari Clairmont Dessert</small>
    </div>

    <div class="card-body register-card-body">

      <form action="proses_register.php" method="post">

        <!-- NAMA -->
        <div class="input-group mb-3">
          <span class="input-group-text">
            <i class="bi bi-person-fill"></i>
          </span>
          <input
            type="text"
            name="nama"
            class="form-control"
            placeholder="Nama Lengkap"
            maxlength="100"
            autocomplete="name"
            required>
        </div>

        <!-- EMAIL -->
        <div class="input-group mb-3">
          <span class="input-group-text">
            <i class="bi bi-envelope-fill"></i>
          </span>
          <input
            type="email"
            name="email"
            class="form-control"
            placeholder="Email"
            maxlength="100"
            autocomplete="email"
            required>
        </div>

        <!-- NOMOR HP -->
        <div class="input-group mb-3">
          <span class="input-group-text">
            <i class="bi bi-telephone-fill"></i>
          </span>
          <input
            type="tel"
            name="no_hp"
            class="form-control"
            placeholder="Nomor HP"
            maxlength="20"
            autocomplete="tel"
            pattern="[0-9+() -]{8,20}"
            title="Masukkan nomor HP yang valid"
            required>
        </div>

        <!-- PASSWORD -->
        <div class="input-group mb-4">
          <span class="input-group-text">
            <i class="bi bi-lock-fill"></i>
          </span>
          <input
            type="password"
            name="password"
            class="form-control"
            placeholder="Password"
            minlength="8"
            autocomplete="new-password"
            required>
        </div>

        <!-- BUTTON -->
        <button
          type="submit"
          name="btnRegister"
          class="btn btn-primary w-100">

          Daftar Member

        </button>

      </form>

      <p class="text-center mt-3 mb-0">
        Sudah punya akun?
        <a href="login.php">Login</a>
      </p>

    </div>
  </div>
</div>

<script src="dist/js/adminlte.js"></script>

</body>
</html>