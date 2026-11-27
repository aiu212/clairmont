
<?php
$base = '/clairmont/';
$menu = $_GET['menu'] ?? 'home';

$laporanAktif = in_array(
    $menu,
    ['view_barang', 'view_customer', 'lap_transaksi'],
    true
);
?>

<style>
.sidebar-menu .nav-link {
    border-radius: 14px;
    margin: 4px 8px;
    transition: all .25s ease;
    font-weight: 600;
}

.sidebar-menu .nav-link p {
    margin: 0;
}

.sidebar-menu .nav-link:hover,
.sidebar-menu .nav-link.active {
    background: linear-gradient(135deg, #f3b6c2, #e6f0ff);
    box-shadow: 0 6px 15px rgba(0,0,0,.12);
}

.sidebar-menu .nav-treeview .nav-link {
    background: rgba(255,255,255,.55);
    margin-left: 18px;
    border-radius: 10px;
    font-size: 14px;
}

.nav-arrow {
    float: right;
    transition: .3s;
}

.menu-open > .nav-link .nav-arrow {
    transform: rotate(90deg);
}
</style>

<ul class="nav sidebar-menu flex-column"
    data-lte-toggle="treeview"
    data-accordion="false">

    <li class="nav-item">
        <a href="<?= $base ?>dashboard_owner.php"
           class="nav-link <?= $menu == 'home' ? 'active' : '' ?>">
            <p>🏠 Home</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= $base ?>dashboard_owner.php?menu=produk"
           class="nav-link <?= $menu == 'produk' ? 'active' : '' ?>">
            <p>🛍️ Produk</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= $base ?>dashboard_owner.php?menu=customer"
           class="nav-link <?= $menu == 'customer' ? 'active' : '' ?>">
            <p>📦 Data Customer</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= $base ?>dashboard_owner.php?menu=transaksi"
           class="nav-link <?= $menu == 'transaksi' ? 'active' : '' ?>">
            <p>🛒 Data Transaksi</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= $base ?>dashboard_owner.php?menu=target_sales"
           class="nav-link <?= $menu == 'target_sales' ? 'active' : '' ?>">
            <p>🎯 Target Penjualan</p>
        </a>
    </li>

    <li class="nav-item <?= $laporanAktif ? 'menu-open' : '' ?>">
        <a href="#" class="nav-link">
            <p>
                🧾 Laporan
                <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">

            <li class="nav-item">
                <a href="<?= $base ?>dashboard_owner.php?menu=view_barang"
                   class="nav-link <?= $menu == 'view_barang' ? 'active' : '' ?>">
                    <p>Laporan Produk</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base ?>dashboard_owner.php?menu=view_customer"
                   class="nav-link <?= $menu == 'view_customer' ? 'active' : '' ?>">
                    <p>Laporan Customer</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base ?>dashboard_owner.php?menu=lap_transaksi"
                   class="nav-link <?= $menu == 'lap_transaksi' ? 'active' : '' ?>">
                    <p>Laporan Transaksi</p>
                </a>
            </li>

        </ul>
    </li>

    <li class="nav-item">
        <a href="<?= $base ?>dashboard_owner.php?menu=profil"
           class="nav-link <?= $menu == 'profil' ? 'active' : '' ?>">
            <p>👤 Profil</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= $base ?>logout.php" class="nav-link">
            <p>🚪 Logout</p>
        </a>
    </li>

</ul>
