<?php
/* Halaman produk khusus Sales. Dipanggil dari dashboard_admin.php */
if (!isset($koneksi)) {
    exit('Buka halaman ini lewat dashboard.');
}

$cari       = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$idKategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;

/* Daftar kategori */
$daftarKategori = [];
$qKategori = mysqli_query($koneksi, "
    SELECT id_kategori, nama_kategori
    FROM tb_kategori
    ORDER BY id_kategori
");
if ($qKategori) {
    while ($k = mysqli_fetch_assoc($qKategori)) {
        $daftarKategori[] = $k;
    }
}

/* Daftar produk */
$where = "(p.status IS NULL OR p.status = 'aktif')";

if ($idKategori > 0) {
    $where .= " AND p.id_kategori = $idKategori";
}

if ($cari !== '') {
    $cariAman = mysqli_real_escape_string($koneksi, $cari);
    $where .= " AND p.nama_produk LIKE '%$cariAman%'";
}

$qProduk = mysqli_query($koneksi, "
    SELECT p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar,
           p.deskripsi, k.nama_kategori
    FROM tb_produk p
    LEFT JOIN tb_kategori k ON k.id_kategori = p.id_kategori
    WHERE $where
    ORDER BY p.id_produk
");

/* Cari lokasi file gambar di dalam folder project */
if (!function_exists('prLokasiGambar')) {
    function prLokasiGambar($gambar)
    {
        static $peta = null;

        $gambar = trim((string) $gambar);
        if ($gambar === '') {
            return '';
        }

        $akar = dirname(__DIR__, 2);

        if (!is_file($akar . '/' . $gambar)) {
            if ($peta === null) {
                $peta = [];
                $berkas = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($akar, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY,
                    RecursiveIteratorIterator::CATCH_GET_CHILD
                );
                foreach ($berkas as $f) {
                    if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $f->getFilename())) {
                        $path = str_replace('\\', '/', substr($f->getPathname(), strlen($akar) + 1));
                        $peta += [strtolower($f->getFilename()) => $path];
                    }
                }
            }
            $kunci = strtolower(basename($gambar));
            if (!isset($peta[$kunci])) {
                return '';
            }
            $gambar = $peta[$kunci];
        }

        return implode('/', array_map('rawurlencode', explode('/', $gambar)));
    }
}

if (!function_exists('prAman')) {
    function prAman($teks)
    {
        return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
.pr { padding: 24px; background: #fff8fa; color: #493747; }

.pr-hero {
    display: flex; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 16px;
    background: linear-gradient(120deg, #f7d4dc, #dceaff);
    border-radius: 20px; padding: 26px 32px; margin-bottom: 20px;
}
.pr-hero h2 { font-weight: 800; margin: 0 0 4px; }
.pr-hero p { margin: 0; }
.pr-aksi-atas { display: flex; flex-wrap: wrap; gap: 10px; }

.pr-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    min-height: 42px; padding: 8px 20px; border: 0; border-radius: 12px;
    font-weight: 700; text-decoration: none;
}
.pr-btn:hover { filter: brightness(.94); }
.pr-utama  { background: #d9779a; color: #fff; }
.pr-kedua  { background: #fff; color: #a64d70; }
.pr-edit   { background: #ffe29a; color: #6b4e00; }
.pr-hapus  { background: #f8c4c4; color: #8f1f1f; }
.pr-utama:hover { color: #fff; }
.pr-kedua:hover { color: #a64d70; }
.pr-edit:hover  { color: #6b4e00; }
.pr-hapus:hover { color: #8f1f1f; }

.pr-filter {
    display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px;
    background: #fff; border-radius: 18px; padding: 18px 20px; margin-bottom: 16px;
    box-shadow: 0 4px 18px rgba(73,55,71,.08);
}
.pr-kolom { flex: 1 1 220px; }
.pr-kolom label { display: block; font-weight: 700; margin-bottom: 6px; }
.pr-kolom input, .pr-kolom select {
    width: 100%; height: 44px; padding: 0 14px;
    border: 1px solid #e5c3cd; border-radius: 12px; background: #fff;
}
.pr-reset { align-self: center; margin-top: 24px; color: #a64d70; font-weight: 600; }
.pr-info { text-align: right; font-size: 14px; color: #7a6a77; margin-bottom: 12px; }

.pr-grid {
    display: grid; gap: 20px;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
}
.pr-card {
    display: flex; flex-direction: column; overflow: hidden;
    background: #fff; border: 1px solid #f4dde4; border-radius: 18px;
    box-shadow: 0 4px 16px rgba(73,55,71,.08); transition: .2s;
}
.pr-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(73,55,71,.15); }
.pr-foto {
    display: flex; align-items: center; justify-content: center;
    width: 100%; height: 190px; background: #fbeaef; color: #a64d70;
}
img.pr-foto { display: block; object-fit: cover; }
.pr-isi { display: flex; flex-direction: column; flex: 1; padding: 14px 16px 16px; }
.pr-isi h5 { font-weight: 700; margin: 2px 0 6px; }
.pr-kategori { font-size: 14px; color: #7a6a77; }
.pr-deskripsi {
    font-size: 14px; margin-bottom: 8px; overflow: hidden;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.pr-harga { margin-top: auto; font-size: 18px; font-weight: 800; }
.pr-stok { font-size: 14px; margin-bottom: 12px; }
.pr-aksi { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.pr-kosong { grid-column: 1 / -1; text-align: center; padding: 48px; background: #fff; border-radius: 18px; }
</style>

<div class="pr">

    <div class="pr-hero">
        <div>
            <h2>Data Produk</h2>
            <p>Kelola produk dan stok Clairmont Dessert.</p>
        </div>
        <div class="pr-aksi-atas">
            <a class="pr-btn pr-kedua" href="dashboard_admin.php?menu=tambah_stok">
                <i class="bi bi-box-seam"></i> Tambah Stok
            </a>
            <a class="pr-btn pr-utama" href="dashboard_admin.php?menu=barang_form">
                <i class="bi bi-plus-lg"></i> Tambah Produk
            </a>
        </div>
    </div>

    <form class="pr-filter" action="dashboard_admin.php" method="get">
        <input type="hidden" name="menu" value="produk">

        <div class="pr-kolom">
            <label for="pr-q">Cari produk</label>
            <input type="search" id="pr-q" name="q"
                   value="<?= prAman($cari) ?>" placeholder="Ketik nama produk...">
        </div>

        <div class="pr-kolom">
            <label for="pr-kategori">Kategori</label>
            <select id="pr-kategori" name="kategori" onchange="this.form.submit()">
                <option value="0">Semua kategori</option>
                <?php foreach ($daftarKategori as $k): ?>
                    <option value="<?= (int) $k['id_kategori'] ?>"
                        <?= $idKategori === (int) $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= prAman($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="pr-btn pr-utama">
            <i class="bi bi-search"></i> Cari Produk
        </button>
        <a class="pr-reset" href="dashboard_admin.php?menu=produk">Reset</a>
    </form>

    <?php if (!$qProduk): ?>
        <div class="alert alert-danger">
            Gagal mengambil data: <?= prAman(mysqli_error($koneksi)) ?>
        </div>
    <?php else: ?>

        <div class="pr-info"><?= mysqli_num_rows($qProduk) ?> produk ditemukan</div>

        <div class="pr-grid">
            <?php while ($p = mysqli_fetch_assoc($qProduk)): ?>
                <?php
                $id   = (int) $p['id_produk'];
                $foto = prLokasiGambar($p['gambar']);
                ?>
                <div class="pr-card">
                    <?php if ($foto !== ''): ?>
                        <img class="pr-foto" src="<?= prAman($foto) ?>"
                             alt="<?= prAman($p['nama_produk']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="pr-foto">Tidak ada gambar</div>
                    <?php endif; ?>

                    <div class="pr-isi">
                        <span class="pr-kategori"><?= prAman($p['nama_kategori']) ?></span>
                        <h5><?= prAman($p['nama_produk']) ?></h5>
                        <div class="pr-deskripsi"><?= prAman($p['deskripsi']) ?></div>
                        <div class="pr-harga">Rp <?= number_format((int) $p['harga'], 0, ',', '.') ?></div>
                        <div class="pr-stok">Stok: <strong><?= (int) $p['stok'] ?></strong></div>

                        <div class="pr-aksi">
                            <a class="pr-btn pr-edit"
                               href="dashboard_admin.php?menu=produk_edit&amp;id=<?= $id ?>&amp;id_produk=<?= $id ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a class="pr-btn pr-hapus"
                               href="dashboard_admin.php?menu=hapus_barang&amp;id=<?= $id ?>&amp;id_produk=<?= $id ?>"
                               onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($qProduk) === 0): ?>
                <div class="pr-kosong">Produk tidak ditemukan.</div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>