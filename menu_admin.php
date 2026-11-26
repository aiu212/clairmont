
<?php
$menu = $_GET['menu'] ?? 'home';

$laporanAktif = in_array(
    $menu,
    ['view_barang', 'view_customer', 'lap_transaksi'],
    true
);
?>

<ul class="nav sidebar-menu flex-column"
    data-lte-toggle="treeview"
    data-accordion="false">

    <li class="nav-item">
        <a href="dashboard_admin.php"
           class="nav-link <?= $menu == 'home' ? 'active' : '' ?>">
            <p>🏠 Home</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="dashboard_admin.php?menu=produk"
           class="nav-link <?= $menu == 'produk' ? 'active' : '' ?>">
            <p>🛍️ Produk</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="dashboard_admin.php?menu=customer"
           class="nav-link <?= $menu == 'customer' ? 'active' : '' ?>">
            <p>📦 Data Customer</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="dashboard_admin.php?menu=transaksi"
           class="nav-link <?= $menu == 'transaksi' ? 'active' : '' ?>">
            <p>🛒 Data Transaksi Harian</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="form/pos/pos.php"
           class="nav-link">
            <p>🧁 POS / Kasir</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="dashboard_admin.php?menu=kelola_transaksi"
           class="nav-link <?= $menu == 'kelola_transaksi' ? 'active' : '' ?>">
            <p>💸 Kelola Transaksi</p>
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
                <a href="dashboard_admin.php?menu=view_barang"
                   class="nav-link <?= $menu == 'view_barang' ? 'active' : '' ?>">
                    <p>Laporan Produk</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="dashboard_admin.php?menu=view_customer"
                   class="nav-link <?= $menu == 'view_customer' ? 'active' : '' ?>">
                    <p>Laporan Customer</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="dashboard_admin.php?menu=lap_transaksi"
                   class="nav-link <?= $menu == 'lap_transaksi' ? 'active' : '' ?>">
                    <p>Laporan Transaksi</p>
                </a>
            </li>

        </ul>

    </li>

    <li class="nav-item">
        <a href="dashboard_admin.php?menu=profil"
           class="nav-link <?= $menu == 'profil' ? 'active' : '' ?>">
            <p>👤 Profil</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="logout.php" class="nav-link">
            <p>🚪 Logout</p>
        </a>
    </li>

</ul>
