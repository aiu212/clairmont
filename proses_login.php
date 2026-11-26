<?php
session_start();
require_once 'koneksi2.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnLogin'])) {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['txtUsername'] ?? '');
$password = $_POST['txtPassword'] ?? '';

$stmt = mysqli_prepare($koneksi, "SELECT id_user, nama, email, password, role, status FROM tb_users WHERE email = ? LIMIT 1");
if (!$stmt) {
    die('Terjadi kesalahan saat menyiapkan login.');
}
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    echo "<script>alert('Email tidak ditemukan!'); window.location='login.php';</script>";
    exit;
}

if (($user['status'] ?? '') !== 'aktif') {
    echo "<script>alert('Akun tidak aktif. Hubungi pengelola sistem.'); window.location='login.php';</script>";
    exit;
}

$dbPassword = $user['password'];
$valid = password_verify($password, $dbPassword);

// Kompatibilitas untuk akun lama yang password-nya masih tersimpan sebagai teks biasa.
if (!$valid && hash_equals((string)$dbPassword, (string)$password)) {
    $valid = true;
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $update = mysqli_prepare($koneksi, "UPDATE tb_users SET password = ? WHERE id_user = ?");
    mysqli_stmt_bind_param($update, 'si', $newHash, $user['id_user']);
    mysqli_stmt_execute($update);
    mysqli_stmt_close($update);
}

if (!$valid) {
    echo "<script>alert('Password salah!'); window.location='login.php';</script>";
    exit;
}

$role = $user['role'];
if (!in_array($role, ['sales', 'customer'], true)) {
    echo "<script>alert('Role akun belum disesuaikan. Jalankan migrasi role terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

session_regenerate_id(true);
$_SESSION['login'] = true;
$_SESSION['id_user'] = (int)$user['id_user'];
$_SESSION['email'] = $user['email'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['role'] = $role;

if ($role === 'sales') {
    header('Location: dashboard_admin.php'); // dashboard operasional Sales yang dipakai sementara
} else {
    header('Location: dashboard_customer.php');
}
exit;
?>
