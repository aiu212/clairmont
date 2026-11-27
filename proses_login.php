
<?php
session_start();
require_once __DIR__ . '/koneksi2.php';

mysqli_set_charset($koneksi, 'utf8mb4');

/* =====================================
   VALIDASI REQUEST
===================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnLogin'])) {
    header('Location: login.php');
    exit;
}

/* =====================================
   AMBIL INPUT
===================================== */

$email = trim($_POST['txtUsername'] ?? '');
$password = $_POST['txtPassword'] ?? '';

if ($email === '' || $password === '') {
    echo "<script>alert('Email dan password wajib diisi.');window.location='login.php';</script>";
    exit;
}

/* =====================================
   CARI AKUN
===================================== */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_user, nama, email, password, role, status
     FROM tb_users
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {
    die('Terjadi kesalahan saat menyiapkan login.');
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {
    echo "<script>alert('Email tidak ditemukan.');window.location='login.php';</script>";
    exit;
}

/* =====================================
   VALIDASI STATUS AKUN
===================================== */

if ($user['status'] !== 'aktif') {
    echo "<script>alert('Akun tidak aktif. Hubungi Owner.');window.location='login.php';</script>";
    exit;
}

/* =====================================
   VERIFIKASI PASSWORD
===================================== */

$dbPassword = (string)$user['password'];

$valid = password_verify($password, $dbPassword);

/*
Kompatibilitas akun lama yang masih
menggunakan password teks biasa.
*/

if (!$valid && hash_equals($dbPassword, $password)) {

    $valid = true;
    $newHash = password_hash($password, PASSWORD_DEFAULT);

    $update = mysqli_prepare(
        $koneksi,
        "UPDATE tb_users SET password = ? WHERE id_user = ?"
    );

    if ($update) {
        mysqli_stmt_bind_param(
            $update,
            'si',
            $newHash,
            $user['id_user']
        );

        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    }
}

if (!$valid) {
    echo "<script>alert('Password salah.');window.location='login.php';</script>";
    exit;
}

/* =====================================
   VALIDASI ROLE
===================================== */

$role = $user['role'];

$roleDiizinkan = ['owner', 'sales', 'customer'];

if (!in_array($role, $roleDiizinkan, true)) {
    echo "<script>alert('Role akun tidak valid. Hubungi Owner.');window.location='login.php';</script>";
    exit;
}

/* =====================================
   BUAT SESSION LOGIN
===================================== */

session_regenerate_id(true);

$_SESSION['login'] = true;
$_SESSION['id_user'] = (int)$user['id_user'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $role;

/* =====================================
   ARAHKAN SESUAI ROLE
===================================== */

switch ($role) {

    case 'owner':
        header('Location: dashboard_owner.php');
        break;

    case 'sales':
        header('Location: dashboard_admin.php');
        break;

    case 'customer':
        header('Location: dashboard_customer.php');
        break;

    default:
        session_destroy();
        header('Location: login.php');
        break;
}

exit;
?>
