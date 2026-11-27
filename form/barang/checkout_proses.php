<?php
// Proses pemesanan online dinonaktifkan. Transaksi hanya melalui kasir POS di toko.
http_response_code(403);
echo 'Pemesanan online tidak tersedia. Silakan datang langsung ke toko untuk melakukan transaksi melalui kasir.';
exit;
