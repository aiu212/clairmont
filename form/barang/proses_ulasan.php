<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi2.php';
mysqli_set_charset($koneksi, 'utf8mb4');

function kembali($id, $pesan) {
    header('Location: ../../dashboard_customer.php?menu=detail_produk&id=' . (int)$id . '&ulasan=' . urlencode($pesan) . '#ulasan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../dashboard_customer.php?menu=produk'); exit;
}
if (empty($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'customer') {
    $id = (int)($_POST['id_produk'] ?? 0);
    header('Location: ../../login.php?next=' . urlencode('dashboard_customer.php?menu=detail_produk&id=' . $id)); exit;
}
$idProduk = (int)($_POST['id_produk'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$komentar = trim($_POST['komentar'] ?? '');
$idUser = (int)($_SESSION['id_user'] ?? 0);
if ($idProduk < 1 || $rating < 1 || $rating > 5 || $komentar === '' || mb_strlen($komentar) > 1000) {
    kembali($idProduk, 'invalid');
}

$stmt = mysqli_prepare($koneksi, "SELECT id_customer FROM tb_customer WHERE id_user=? AND status='aktif' LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$member) kembali($idProduk, 'member');
$idCustomer = (int)$member['id_customer'];

// Ulasan hanya dapat dibuat oleh member yang memiliki transaksi selesai untuk produk tersebut.
$stmt = mysqli_prepare($koneksi, "SELECT 1 FROM tb_transaksi t JOIN tb_detail_transaksi d ON d.id_transaksi=t.id_transaksi WHERE t.id_customer=? AND d.id_produk=? AND t.status_transaksi='selesai' LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $idCustomer, $idProduk);
mysqli_stmt_execute($stmt);
$pernahBeli = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$pernahBeli) kembali($idProduk, 'belum_beli');

$stmt = mysqli_prepare($koneksi, "INSERT INTO tb_ulasan_produk (id_produk,id_customer,rating,komentar) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), komentar=VALUES(komentar), updated_at=CURRENT_TIMESTAMP");
mysqli_stmt_bind_param($stmt, 'iiis', $idProduk, $idCustomer, $rating, $komentar);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
kembali($idProduk, $ok ? 'sukses' : 'gagal');
?>