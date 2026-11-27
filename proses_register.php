
<?php
session_start();

require_once __DIR__ . '/koneksi2.php';

mysqli_set_charset($koneksi, 'utf8mb4');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* =====================================
   VALIDASI REQUEST
===================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btnRegister'])) {
    header('Location: register.php');
    exit;
}

/* =====================================
   AMBIL INPUT
===================================== */

$nama     = trim($_POST['nama'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$no_hp    = trim($_POST['no_hp'] ?? '');
$password = $_POST['password'] ?? '';

/* =====================================
   VALIDASI INPUT
===================================== */

if ($nama === '' || $email === '' || $no_hp === '' || $password === '') {
    echo "<script>alert('Semua data wajib diisi.');window.location='register.php';</script>";
    exit;
}

if (strlen($nama) > 100) {
    echo "<script>alert('Nama terlalu panjang.');window.location='register.php';</script>";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    echo "<script>alert('Format email tidak valid.');window.location='register.php';</script>";
    exit;
}

if (!preg_match('/^[0-9+() -]{8,20}$/', $no_hp)) {
    echo "<script>alert('Format nomor HP tidak valid.');window.location='register.php';</script>";
    exit;
}

if (strlen($password) < 8) {
    echo "<script>alert('Password minimal 8 karakter.');window.location='register.php';</script>";
    exit;
}

/* =====================================
   CEK EMAIL SUDAH TERDAFTAR
===================================== */

try {

    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id_user FROM tb_users WHERE email = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($cek, 's', $email);
    mysqli_stmt_execute($cek);

    $hasil = mysqli_stmt_get_result($cek);
    $userLama = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($cek);

    if ($userLama) {
        echo "<script>alert('Email sudah terdaftar. Silakan gunakan email lain.');window.location='register.php';</script>";
        exit;
    }

    /* =====================================
       HASH PASSWORD
    ===================================== */

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    /* =====================================
       MULAI TRANSAKSI DATABASE
    ===================================== */

    mysqli_begin_transaction($koneksi);

    /* =====================================
       SIMPAN KE TB_USERS
    ===================================== */

    $kode_user = NULL;
    $role = 'customer';
    $status = 'aktif';

    $stmtUser = mysqli_prepare(
        $koneksi,
        "INSERT INTO tb_users
        (kode_user, nama, email, password, role, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );

    mysqli_stmt_bind_param(
        $stmtUser,
        'ssssss',
        $kode_user,
        $nama,
        $email,
        $passwordHash,
        $role,
        $status
    );

    mysqli_stmt_execute($stmtUser);

    $id_user = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmtUser);

    /* =====================================
       GENERATE KODE USER
    ===================================== */

    $kode_user = 'USR-' . str_pad($id_user, 3, '0', STR_PAD_LEFT);

    $updateUser = mysqli_prepare(
        $koneksi,
        "UPDATE tb_users SET kode_user = ? WHERE id_user = ?"
    );

    mysqli_stmt_bind_param(
        $updateUser,
        'si',
        $kode_user,
        $id_user
    );

    mysqli_stmt_execute($updateUser);
    mysqli_stmt_close($updateUser);

    /* =====================================
       SIMPAN KE TB_CUSTOMER
    ===================================== */

    $kode_customer = NULL;

    $stmtCustomer = mysqli_prepare(
        $koneksi,
        "INSERT INTO tb_customer
        (kode_customer, id_user, nama_customer, email, no_hp, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );

    mysqli_stmt_bind_param(
        $stmtCustomer,
        'sissss',
        $kode_customer,
        $id_user,
        $nama,
        $email,
        $no_hp,
        $status
    );

    mysqli_stmt_execute($stmtCustomer);

    $id_customer = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmtCustomer);

    /* =====================================
       GENERATE KODE CUSTOMER
    ===================================== */

    $kode_customer = 'CST-' . str_pad($id_customer, 3, '0', STR_PAD_LEFT);

    $updateCustomer = mysqli_prepare(
        $koneksi,
        "UPDATE tb_customer SET kode_customer = ? WHERE id_customer = ?"
    );

    mysqli_stmt_bind_param(
        $updateCustomer,
        'si',
        $kode_customer,
        $id_customer
    );

    mysqli_stmt_execute($updateCustomer);
    mysqli_stmt_close($updateCustomer);

    /* =====================================
       COMMIT DATABASE
    ===================================== */

    mysqli_commit($koneksi);

    echo "<script>
        alert('Registrasi berhasil! Selamat bergabung menjadi Member Clairmont Dessert. Silakan login.');
        window.location='login.php';
    </script>";
    exit;

} catch (Throwable $e) {

    /* =====================================
       BATALKAN JIKA TERJADI ERROR
    ===================================== */

    try {
        mysqli_rollback($koneksi);
    } catch (Throwable $rollbackError) {
        // Tidak melakukan apa-apa jika rollback gagal
    }

    error_log('Registrasi Member Error: ' . $e->getMessage());

    echo "<script>
        alert('Registrasi gagal. Silakan periksa data dan coba kembali.');
        window.location='register.php';
    </script>";
    exit;
}
?>