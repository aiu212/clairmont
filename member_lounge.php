<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi2.php';
mysqli_set_charset($koneksi, 'utf8mb4');

$memberEsc = static function ($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($angka) {
    return 'Rp ' . number_format((int)$angka, 0, ',', '.');
};

$idUser = (int)($_SESSION['id_user'] ?? 0);
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_customer, kode_customer, nama_customer, email, no_hp, alamat, created_at
     FROM tb_customer WHERE id_user = ? AND status = 'aktif' LIMIT 1"
);
if (!$stmt) {
    echo '<div class="member-empty"><h2>Member Lounge belum dapat dibuka</h2><p>Terjadi kendala saat membaca data member.</p></div>';
    return;
}
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$member = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$member) {
    echo '<section class="member-lounge"><div class="member-empty"><span class="member-kicker">CLAIRMONT MEMBER</span><h2>Data member belum ditemukan</h2><p>Akun ini belum terhubung dengan data customer. Silakan hubungi admin untuk memeriksa akun member kamu.</p></div></section>';
    return;
}
$idCustomer = (int)$member['id_customer'];

// Ringkasan dihitung langsung dari transaksi POS milik customer yang sedang login.
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS jumlah_transaksi,
            SUM(CASE WHEN status_transaksi = 'selesai' THEN 1 ELSE 0 END) AS transaksi_selesai,
            COALESCE(SUM(CASE WHEN status_transaksi = 'selesai' THEN total_harga ELSE 0 END), 0) AS total_belanja
     FROM tb_transaksi WHERE id_customer = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $idCustomer);
mysqli_stmt_execute($stmt);
$ringkasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
mysqli_stmt_close($stmt);
$ringkasan += ['jumlah_transaksi' => 0, 'transaksi_selesai' => 0, 'total_belanja' => 0];

// Ambil transaksi dan rincian produk dari POS.
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT t.id_transaksi, t.kode_transaksi, t.tanggal_transaksi,
            t.total_harga, t.metode_pembayaran, t.status_transaksi,
            d.id_detail, d.jumlah, d.harga, d.subtotal, p.nama_produk
     FROM tb_transaksi t
     LEFT JOIN tb_detail_transaksi d ON d.id_transaksi = t.id_transaksi
     LEFT JOIN tb_produk p ON p.id_produk = d.id_produk
     WHERE t.id_customer = ?
     ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC, d.id_detail ASC"
);
mysqli_stmt_bind_param($stmt, 'i', $idCustomer);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = [];
while ($row = mysqli_fetch_assoc($result)) {
    $id = (int)$row['id_transaksi'];
    if (!isset($transaksi[$id])) {
        $transaksi[$id] = [
            'kode' => $row['kode_transaksi'] ?: ('TRX-' . $id),
            'tanggal' => $row['tanggal_transaksi'],
            'total' => (int)$row['total_harga'],
            'metode' => $row['metode_pembayaran'],
            'status' => $row['status_transaksi'],
            'items' => []
        ];
    }
    if ($row['id_detail'] !== null) {
        $transaksi[$id]['items'][] = [
            'nama' => $row['nama_produk'] ?: 'Produk tidak tersedia',
            'jumlah' => (int)$row['jumlah'],
            'harga' => (int)$row['harga'],
            'subtotal' => (int)$row['subtotal']
        ];
    }
}
mysqli_stmt_close($stmt);

$statusLabel = [
    'selesai' => 'Selesai',
    'proses' => 'Diproses',
    'pending' => 'Menunggu',
    'batal' => 'Dibatalkan'
];
$statusClass = [
    'selesai' => 'is-done',
    'proses' => 'is-process',
    'pending' => 'is-pending',
    'batal' => 'is-cancelled'
];
$inisial = 'M';
$namaParts = preg_split('/\s+/', trim((string)$member['nama_customer']));
if (!empty($namaParts[0])) $inisial = strtoupper(substr($namaParts[0], 0, 1));
if (!empty($namaParts[1])) $inisial .= strtoupper(substr($namaParts[1], 0, 1));
?>
<style>
.member-lounge{--ml-primary:#895566;--ml-pink:#f8e2eb;--ml-blue:#e8f0ff;--ml-ink:#30232d;--ml-muted:#887984;color:var(--ml-ink);font-family:'Plus Jakarta Sans','Source Sans 3',sans-serif;padding:28px 0 42px}
.member-lounge *{box-sizing:border-box}
.member-wrap{max-width:1180px;margin:0 auto;padding:0 22px}
.member-hero{position:relative;overflow:hidden;border-radius:28px;padding:30px;background:linear-gradient(120deg,#fff0f5 0%,#f7eaf6 48%,#eaf1ff 100%);border:1px solid #f1dce7;box-shadow:0 14px 38px rgba(91,57,76,.07)}
.member-hero:after{content:'';position:absolute;width:260px;height:260px;border-radius:50%;right:-90px;top:-120px;background:rgba(255,255,255,.55);pointer-events:none}
.member-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap}
.member-identity{display:flex;align-items:center;gap:18px;min-width:240px}
.member-avatar{width:76px;height:76px;border-radius:24px;display:grid;place-items:center;background:#fff;color:var(--ml-primary);font:600 27px 'Playfair Display',serif;box-shadow:0 5px 18px rgba(112,70,91,.1);border:1px solid #f3dce6}
.member-kicker{display:inline-block;font-size:10px;letter-spacing:.16em;font-weight:800;color:var(--ml-primary);text-transform:uppercase;margin-bottom:8px}
.member-hero h1{font:500 clamp(25px,3vw,36px)/1.15 'Playfair Display',serif;margin:0 0 7px;color:#382832}
.member-hero p{margin:0;color:#776875;font-size:13px}
.member-code{background:rgba(255,255,255,.78);border:1px solid #f0dce6;border-radius:18px;padding:15px 20px;min-width:190px}
.member-code small{display:block;color:var(--ml-muted);font-size:10px;letter-spacing:.1em;text-transform:uppercase;font-weight:700}
.member-code strong{display:block;margin-top:5px;font-size:17px;color:var(--ml-primary);letter-spacing:.04em}
.member-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin:20px 0}
.member-stat{background:#fff;border:1px solid #f0e2e9;border-radius:20px;padding:20px;box-shadow:0 8px 24px rgba(91,57,76,.045)}
.member-stat .stat-icon{width:36px;height:36px;border-radius:12px;display:grid;place-items:center;background:#fff0f5;color:var(--ml-primary);font-size:17px;margin-bottom:13px}
.member-stat:nth-child(2) .stat-icon{background:#edf3ff;color:#5b6f9c}
.member-stat:nth-child(3) .stat-icon{background:#f4effa;color:#77618c}
.member-stat small{display:block;color:var(--ml-muted);font-size:11px;font-weight:700;letter-spacing:.04em}
.member-stat strong{display:block;font-size:clamp(21px,2.4vw,27px);margin-top:5px;color:#382832}
.member-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:18px;align-items:start}
.member-card{background:#fff;border:1px solid #f0e2e9;border-radius:22px;padding:22px;box-shadow:0 8px 24px rgba(91,57,76,.045)}
.member-section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:18px;flex-wrap:wrap}
.member-section-head h2{font:500 25px 'Playfair Display',serif;margin:0;color:#382832}
.member-count{font-size:11px;color:#765e6d;background:#fff1f6;border-radius:99px;padding:7px 12px;font-weight:700}
.member-transaction{border:1px solid #f0e5eb;border-radius:17px;padding:17px;margin-top:12px;background:#fffdfd}
.member-transaction-top{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.member-transaction-code{font-weight:800;color:#634353;font-size:13px}
.member-transaction-date{font-size:11px;color:var(--ml-muted);margin-top:4px}
.member-transaction-total{text-align:right;font-weight:800;color:#805066;font-size:15px}
.member-status{display:inline-block;margin-top:6px;border-radius:99px;padding:5px 10px;font-size:10px;font-weight:800}
.member-status.is-done{background:#e9f6ed;color:#39754b}.member-status.is-process{background:#fff4dc;color:#946c22}.member-status.is-pending{background:#edf3ff;color:#526b9c}.member-status.is-cancelled{background:#fdeaea;color:#a14b4b}
.member-items{border-top:1px solid #f1e7ec;margin-top:13px;padding-top:10px}
.member-item{display:flex;justify-content:space-between;gap:12px;padding:5px 0;font-size:12px;color:#635560}
.member-item span:last-child{white-space:nowrap;font-weight:700;color:#594653}
.member-payment{font-size:11px;color:var(--ml-muted);margin-top:9px}
.member-side{display:grid;gap:15px}
.member-info-row{display:flex;gap:11px;padding:11px 0;border-bottom:1px solid #f2e8ed;align-items:flex-start}
.member-info-row:last-child{border-bottom:0;padding-bottom:0}
.member-info-icon{width:31px;height:31px;flex:0 0 31px;border-radius:10px;background:#fff1f6;color:#895566;display:grid;place-items:center}
.member-info-row small{display:block;font-size:10px;color:var(--ml-muted);margin-bottom:3px}
.member-info-row strong{font-size:12px;font-weight:700;overflow-wrap:anywhere}
.member-shortcut{display:flex;align-items:center;justify-content:space-between;gap:12px;text-decoration:none;color:#6e4b5d;padding:12px 14px;border-radius:13px;background:#fff3f7;font-size:12px;font-weight:700;margin-top:10px}
.member-shortcut:hover{background:#f9e4ed;color:#6e4b5d}
.member-empty{text-align:center;padding:42px 20px;border:1px dashed #e7cbd8;border-radius:18px;background:#fffafd;color:#756572}
.member-empty h2{font:500 23px 'Playfair Display',serif;color:#493542;margin:12px 0 7px}
.member-empty p{font-size:12px;margin:0}
@media(max-width:850px){.member-layout{grid-template-columns:1fr}.member-side{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.member-lounge{padding-top:18px}.member-wrap{padding:0 14px}.member-hero{padding:22px;border-radius:22px}.member-avatar{width:60px;height:60px;border-radius:19px}.member-stats{grid-template-columns:1fr;gap:10px}.member-stat{padding:15px;display:grid;grid-template-columns:38px 1fr;column-gap:12px;align-items:center}.member-stat .stat-icon{grid-row:span 2;margin:0}.member-stat strong{margin-top:2px}.member-card{padding:16px}.member-side{grid-template-columns:1fr}.member-transaction{padding:14px}.member-transaction-total{text-align:left}}
</style>

<section class="member-lounge">
 <div class="member-wrap">
  <header class="member-hero">
   <div class="member-hero-content">
    <div class="member-identity">
     <div class="member-avatar"><?= $memberEsc($inisial) ?></div>
     <div>
      <span class="member-kicker">Clairmont Member Lounge</span>
      <h1>Halo, <?= $memberEsc($member['nama_customer']) ?>!</h1>
      <p>Ringkasan akun dan perjalanan belanja kamu di Clairmont.</p>
     </div>
    </div>
    <div class="member-code"><small>Nomor member</small><strong><?= $memberEsc($member['kode_customer'] ?: 'Belum tersedia') ?></strong></div>
   </div>
  </header>

  <div class="member-stats">
   <article class="member-stat"><span class="stat-icon"><i class="bi bi-receipt"></i></span><small>Total transaksi POS</small><strong><?= (int)$ringkasan['jumlah_transaksi'] ?></strong></article>
   <article class="member-stat"><span class="stat-icon"><i class="bi bi-bag-check"></i></span><small>Transaksi selesai</small><strong><?= (int)$ringkasan['transaksi_selesai'] ?></strong></article>
   <article class="member-stat"><span class="stat-icon"><i class="bi bi-wallet2"></i></span><small>Total belanja selesai</small><strong><?= $formatRupiah($ringkasan['total_belanja']) ?></strong></article>
  </div>

  <div class="member-layout">
   <section class="member-card">
    <div class="member-section-head">
     <div><span class="member-kicker">Aktivitas akun</span><h2>Riwayat Pembelian</h2></div>
     <span class="member-count"><?= count($transaksi) ?> transaksi tercatat</span>
    </div>
    <?php if (!$transaksi): ?>
     <div class="member-empty"><i class="bi bi-bag-heart" style="font-size:30px;color:#b77b96"></i><h2>Belum ada pembelian</h2><p>Transaksi yang kasir catat menggunakan akun member ini akan muncul di sini.</p></div>
    <?php else: ?>
     <?php foreach ($transaksi as $trx): ?>
      <?php $statusKey = strtolower((string)$trx['status']); ?>
      <article class="member-transaction">
       <div class="member-transaction-top">
        <div><div class="member-transaction-code"><?= $memberEsc($trx['kode']) ?></div><div class="member-transaction-date"><i class="bi bi-calendar3 me-1"></i><?= $memberEsc(date('d M Y · H:i', strtotime($trx['tanggal']))) ?></div></div>
        <div class="member-transaction-total"><?= $formatRupiah($trx['total']) ?><br><span class="member-status <?= $memberEsc($statusClass[$statusKey] ?? 'is-pending') ?>"><?= $memberEsc($statusLabel[$statusKey] ?? ucfirst($statusKey)) ?></span></div>
       </div>
       <?php if ($trx['items']): ?>
        <div class="member-items">
         <?php foreach ($trx['items'] as $item): ?>
          <div class="member-item"><span><?= $memberEsc($item['nama']) ?> × <?= (int)$item['jumlah'] ?></span><span><?= $formatRupiah($item['subtotal']) ?></span></div>
         <?php endforeach; ?>
        </div>
       <?php endif; ?>
       <div class="member-payment"><i class="bi bi-credit-card me-1"></i>Metode pembayaran: <?= $memberEsc($trx['metode'] ?: '-') ?></div>
      </article>
     <?php endforeach; ?>
    <?php endif; ?>
   </section>

   <aside class="member-side">
    <section class="member-card">
     <span class="member-kicker">Informasi akun</span><h2 style="font:500 23px 'Playfair Display',serif;margin:0 0 12px">Profil Member</h2>
     <div class="member-info-row"><span class="member-info-icon"><i class="bi bi-person"></i></span><div><small>Nama lengkap</small><strong><?= $memberEsc($member['nama_customer']) ?></strong></div></div>
     <div class="member-info-row"><span class="member-info-icon"><i class="bi bi-envelope"></i></span><div><small>Email</small><strong><?= $memberEsc($member['email'] ?: 'Belum diisi') ?></strong></div></div>
     <div class="member-info-row"><span class="member-info-icon"><i class="bi bi-telephone"></i></span><div><small>Nomor HP</small><strong><?= $memberEsc($member['no_hp'] ?: 'Belum diisi') ?></strong></div></div>
     <div class="member-info-row"><span class="member-info-icon"><i class="bi bi-geo-alt"></i></span><div><small>Alamat</small><strong><?= $memberEsc($member['alamat'] ?: 'Belum diisi') ?></strong></div></div>
     <a class="member-shortcut" href="dashboard_customer.php?menu=profil">Lihat / ubah profil <i class="bi bi-arrow-up-right"></i></a>
    </section>
    <section class="member-card">
     <span class="member-kicker">Jelajahi Clairmont</span><h2 style="font:500 22px 'Playfair Display',serif;margin:0 0 6px">Ada yang manis untukmu</h2>
     <p style="font-size:12px;color:#887984;margin:0">Lihat koleksi dessert dan cake yang tersedia.</p>
     <a class="member-shortcut" href="dashboard_customer.php?menu=produk">Buka katalog produk <i class="bi bi-arrow-right"></i></a>
    </section>
   </aside>
  </div>
 </div>
</section>
