
<?php
require_once __DIR__ . '/koneksi2.php';
require_once __DIR__ . '/FPDF/fpdf.php';

mysqli_set_charset($koneksi, 'utf8mb4');

/* =====================================
   VALIDASI PERIODE LAPORAN
===================================== */

$tglAwal  = $_POST['tgl_awal'] ?? '';
$tglAkhir = $_POST['tgl_akhir'] ?? '';

function validTanggal($tanggal) {
    $d = DateTime::createFromFormat('!Y-m-d', $tanggal);
    return $d && $d->format('Y-m-d') === $tanggal;
}

if (!validTanggal($tglAwal) || !validTanggal($tglAkhir)) {
    die('Tanggal laporan tidak valid.');
}

if ($tglAwal > $tglAkhir) {
    die('Tanggal awal tidak boleh melebihi tanggal akhir.');
}

$tanggalMulai = $tglAwal . ' 00:00:00';

$tanggalSelesai = date(
    'Y-m-d H:i:s',
    strtotime($tglAkhir . ' +1 day')
);

/* =====================================
   FUNGSI BANTU PDF
===================================== */

function teksPDF($teks) {
    return iconv(
        'UTF-8',
        'windows-1252//TRANSLIT//IGNORE',
        (string)$teks
    );
}

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function isiCell($pdf, $lebar, $tinggi, $teks, $align = 'L') {

    $teks = teksPDF($teks);

    while (
        strlen($teks) > 0 &&
        $pdf->GetStringWidth($teks) > $lebar - 2
    ) {
        $teks = substr($teks, 0, -1);
    }

    $pdf->Cell($lebar, $tinggi, $teks, 1, 0, $align);
}

/* =====================================
   QUERY DATA TRANSAKSI
===================================== */

$sql = "
    SELECT
        t.id_transaksi,
        t.kode_transaksi,
        t.tanggal_transaksi,
        t.total_harga,
        t.metode_pembayaran,
        t.status_transaksi,

        c.nama_customer,

        s.nama AS nama_sales,

        p.nama_produk,

        d.harga,
        d.jumlah,
        d.subtotal

    FROM tb_transaksi t

    LEFT JOIN tb_customer c
        ON t.id_customer = c.id_customer

    LEFT JOIN tb_users s
        ON t.id_sales = s.id_user

    LEFT JOIN tb_detail_transaksi d
        ON t.id_transaksi = d.id_transaksi

    LEFT JOIN tb_produk p
        ON d.id_produk = p.id_produk

    WHERE t.tanggal_transaksi >= ?
      AND t.tanggal_transaksi < ?

    ORDER BY
        t.tanggal_transaksi ASC,
        t.id_transaksi ASC,
        d.id_detail ASC
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die('Query laporan gagal: ' . mysqli_error($koneksi));
}

mysqli_stmt_bind_param(
    $stmt,
    'ss',
    $tanggalMulai,
    $tanggalSelesai
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die('Gagal mengambil data transaksi.');
}

/* =====================================
   QUERY RINGKASAN LAPORAN
===================================== */

$sqlRingkasan = "
    SELECT
        COUNT(*) AS jumlah_transaksi,

        COALESCE(SUM(
            CASE
                WHEN status_transaksi = 'selesai'
                THEN 1 ELSE 0
            END
        ), 0) AS transaksi_selesai,

        COALESCE(SUM(
            CASE
                WHEN status_transaksi = 'selesai'
                THEN total_harga ELSE 0
            END
        ), 0) AS total_pendapatan

    FROM tb_transaksi

    WHERE tanggal_transaksi >= ?
      AND tanggal_transaksi < ?
";

$stmtRingkasan = mysqli_prepare($koneksi, $sqlRingkasan);

if (!$stmtRingkasan) {
    die('Query ringkasan gagal: ' . mysqli_error($koneksi));
}

mysqli_stmt_bind_param(
    $stmtRingkasan,
    'ss',
    $tanggalMulai,
    $tanggalSelesai
);

mysqli_stmt_execute($stmtRingkasan);

$hasilRingkasan = mysqli_stmt_get_result($stmtRingkasan);

$ringkasan = mysqli_fetch_assoc($hasilRingkasan);

/* =====================================
   HEADER TABEL
===================================== */

function headerTabel($pdf) {

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(245, 225, 235);

    $kolom = array(
        array('Kode', 23),
        array('Tanggal', 28),
        array('Customer', 32),
        array('Sales', 24),
        array('Produk', 39),
        array('Qty', 11),
        array('Harga', 24),
        array('Subtotal', 25),
        array('Metode', 20),
        array('Status', 21),
        array('Total Bayar', 30)
    );

    foreach ($kolom as $k) {
        $pdf->Cell($k[1], 9, $k[0], 1, 0, 'C', true);
    }

    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8);
}

/* =====================================
   HEADER UTAMA
===================================== */

function headerUtama($pdf, $tglAwal, $tglAkhir) {

    $pdf->SetFont('Arial', 'B', 16);

    $pdf->Cell(
        0,
        10,
        teksPDF('LAPORAN TRANSAKSI PENJUALAN'),
        0,
        1,
        'C'
    );

    $pdf->SetFont('Arial', '', 10);

    $pdf->Cell(
        0,
        7,
        teksPDF('Sistem Informasi Penjualan - Clairmont Dessert'),
        0,
        1,
        'C'
    );

    $pdf->Cell(
        0,
        7,
        'Periode: ' .
        date('d-m-Y', strtotime($tglAwal)) .
        ' s/d ' .
        date('d-m-Y', strtotime($tglAkhir)),
        0,
        1,
        'C'
    );

    $pdf->Ln(5);
}

/* =====================================
   MEMBUAT PDF
===================================== */

$pdf = new FPDF('L', 'mm', 'A4');

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 12);

$pdf->AddPage();

headerUtama($pdf, $tglAwal, $tglAkhir);

headerTabel($pdf);

/* =====================================
   ISI DATA TRANSAKSI
===================================== */

$totalBaris = 0;
$transaksiTerakhir = null;

while ($row = mysqli_fetch_assoc($result)) {

    // Menghindari baris tabel terpotong.
    if ($pdf->GetY() + 9 > 198) {
        $pdf->AddPage();
        headerTabel($pdf);
    }

    $idTransaksi = $row['id_transaksi'];

    $tanggal = date(
        'd-m-Y H:i',
        strtotime($row['tanggal_transaksi'])
    );

    $customer = $row['nama_customer'] ?: 'Pembeli Umum';
    $sales = $row['nama_sales'] ?: '-';
    $produk = $row['nama_produk'] ?: '-';

    $harga = $row['harga'] !== null
        ? rupiah($row['harga'])
        : '-';

    $jumlah = $row['jumlah'] !== null
        ? $row['jumlah']
        : '-';

    $subtotal = $row['subtotal'] !== null
        ? rupiah($row['subtotal'])
        : rupiah($row['total_harga']);

    $totalBayar = '';

    // Total bayar ditampilkan satu kali
    // pada setiap transaksi.

    if ($transaksiTerakhir != $idTransaksi) {

        $totalBayar = rupiah($row['total_harga']);

        $transaksiTerakhir = $idTransaksi;
    }

    $pdf->SetFont('Arial', '', 8);

    isiCell($pdf, 23, 9, $row['kode_transaksi'] ?: '-');
    isiCell($pdf, 28, 9, $tanggal);
    isiCell($pdf, 32, 9, $customer);
    isiCell($pdf, 24, 9, $sales);
    isiCell($pdf, 39, 9, $produk);

    isiCell($pdf, 11, 9, $jumlah, 'C');
    isiCell($pdf, 24, 9, $harga, 'R');
    isiCell($pdf, 25, 9, $subtotal, 'R');

    isiCell(
        $pdf,
        20,
        9,
        $row['metode_pembayaran'],
        'C'
    );

    isiCell(
        $pdf,
        21,
        9,
        ucfirst($row['status_transaksi']),
        'C'
    );

    isiCell($pdf, 30, 9, $totalBayar, 'R');

    $pdf->Ln();

    $totalBaris++;
}

mysqli_stmt_close($stmt);

/* =====================================
   JIKA DATA KOSONG
===================================== */

if ($totalBaris == 0) {

    $pdf->SetFont('Arial', 'I', 10);

    $pdf->Cell(
        277,
        12,
        teksPDF('Tidak ada transaksi pada periode yang dipilih.'),
        1,
        1,
        'C'
    );
}

/* =====================================
   RINGKASAN LAPORAN
===================================== */

// Menyediakan ruang untuk ringkasan
// agar tidak terpotong ke halaman berikutnya.

$ruangRingkasan = 37;
$batasHalaman = 198;

if ($pdf->GetY() + $ruangRingkasan > $batasHalaman) {
    $pdf->AddPage();
}

$pdf->Ln(3);

// Judul ringkasan

$pdf->SetFont('Arial', 'B', 12);

$pdf->Cell(
    0,
    7,
    teksPDF('RINGKASAN LAPORAN'),
    0,
    1
);

// Jumlah transaksi

$pdf->SetFont('Arial', '', 10);

$pdf->Cell(70, 6, 'Jumlah Transaksi', 0, 0);

$pdf->Cell(
    60,
    6,
    ': ' . (int)$ringkasan['jumlah_transaksi'],
    0,
    1
);

// Transaksi selesai

$pdf->Cell(70, 6, 'Transaksi Selesai', 0, 0);

$pdf->Cell(
    60,
    6,
    ': ' . (int)$ringkasan['transaksi_selesai'],
    0,
    1
);

// Total pendapatan

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    70,
    7,
    'TOTAL PENDAPATAN',
    0,
    0
);

$pdf->Cell(
    60,
    7,
    ': ' . rupiah($ringkasan['total_pendapatan']),
    0,
    1
);

/* =====================================
   FOOTER
===================================== */

$pdf->Ln(2);

$pdf->SetFont('Arial', 'I', 9);

$pdf->Cell(
    0,
    6,
    teksPDF('Dicetak melalui Sistem Informasi Clairmont Dessert'),
    0,
    1,
    'R'
);

/* =====================================
   TUTUP STATEMENT
===================================== */

mysqli_stmt_close($stmtRingkasan);

/* =====================================
   OUTPUT PDF
===================================== */

$pdf->Output(
    'I',
    'Laporan_Transaksi_Clairmont.pdf'
);

exit;
?>
