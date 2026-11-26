<?php
session_start();
include "../../koneksi2.php";

/* ===============================
   CEK LOGIN SALES
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sales') {
    header("Location: ../../login.php"); // ⬅️ PATH DIPERBAIKI
    exit;
}

/* ===============================
   PROSES UPDATE STATUS
================================ */
if (isset($_POST['id_transaksi'], $_POST['status'])) {

    $id_transaksi = intval($_POST['id_transaksi']);
    $status       = mysqli_real_escape_string($koneksi, $_POST['status']);

    mysqli_query($koneksi, "
        UPDATE tb_transaksi 
        SET status_transaksi = '$status'
        WHERE id_transaksi = $id_transaksi
    ");
}

/* ===============================
   KEMBALI KE KELOLA TRANSAKSI
================================ */
header("Location: ../../dashboard_admin.php?menu=kelola_transaksi");
exit;
