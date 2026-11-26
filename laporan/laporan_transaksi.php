
<?php
session_start();

require_once __DIR__ . '/koneksi2.php';

mysqli_set_charset($koneksi, 'utf8mb4');

$tanggalHariIni = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Transaksi | Clairmont Dessert</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #fff8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #543d4c;
        }

        .report-container {
            max-width: 850px;
            margin: 45px auto;
            padding: 20px;
        }

        .report-card {
            background: white;
            border: 1px solid #f0dce3;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(140,97,112,.08);
        }

        .report-title {
            font-size: 26px;
            font-weight: 800;
            color: #67475d;
        }

        .form-control {
            border: 1px solid #edcfda;
            border-radius: 10px;
            padding: 12px;
        }

        .form-control:focus {
            border-color: #d98da9;
            box-shadow: 0 0 0 3px rgba(217,141,169,.15);
        }

        .btn-clairmont {
            background: linear-gradient(120deg,#f1b4c5,#dceaff);
            color: #593e50;
            border: none;
            border-radius: 11px;
            padding: 13px;
            font-weight: 700;
        }

        .btn-clairmont:hover {
            color: #593e50;
            opacity: .9;
        }

        .info-box {
            background: #fff4f7;
            border: 1px solid #f0dce3;
            border-radius: 12px;
            padding: 15px;
            color: #846c7b;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="report-container">

    <div class="report-card">

        <div class="text-center mb-4">

            <div style="font-size:40px;">📊</div>

            <div class="report-title">
                Laporan Transaksi
            </div>

            <div class="text-secondary">
                CLAIRMONT DESSERT
            </div>

            <hr>

            <p class="text-secondary mb-0">
                Pilih periode transaksi yang ingin dicetak.
            </p>

        </div>

        <form action="laporan_transaksi_cetak.php"
              method="POST"
              target="_blank">

            <div class="row g-3">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Tanggal Awal
                    </label>

                    <input type="date"
                           name="tgl_awal"
                           class="form-control"
                           value="<?= htmlspecialchars($tanggalHariIni) ?>"
                           max="<?= htmlspecialchars($tanggalHariIni) ?>"
                           required>

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Tanggal Akhir
                    </label>

                    <input type="date"
                           name="tgl_akhir"
                           class="form-control"
                           value="<?= htmlspecialchars($tanggalHariIni) ?>"
                           max="<?= htmlspecialchars($tanggalHariIni) ?>"
                           required>

                </div>

            </div>

            <div class="info-box mt-4">
                <strong>Informasi laporan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Data transaksi Customer.</li>
                    <li>Data transaksi POS oleh Sales.</li>
                    <li>Rincian produk dan jumlah pembelian.</li>
                    <li>Metode dan status pembayaran.</li>
                    <li>Total pendapatan transaksi selesai.</li>
                </ul>
            </div>

            <button type="submit"
                    class="btn btn-clairmont w-100 mt-4">

                🖨️ Cetak Laporan Transaksi

            </button>

            <a href="javascript:history.back()"
               class="btn btn-outline-secondary w-100 mt-2">

                Kembali

            </a>

        </form>

    </div>

</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const awal = document.querySelector('[name="tgl_awal"]').value;
    const akhir = document.querySelector('[name="tgl_akhir"]').value;

    if (awal > akhir) {
        e.preventDefault();
        alert('Tanggal awal tidak boleh melebihi tanggal akhir.');
    }
});
</script>

</body>
</html>