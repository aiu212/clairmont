<?php
$menu = $_GET['menu'] ?? 'home';
?>

  <style>
/* === MENU ADMIN CLAIRMONT STYLE === */

.sidebar-menu .nav-link {
  border-radius: 14px;
  margin: 4px 8px;
  transition: all .25s ease;
  font-weight: 600;
}

.sidebar-menu .nav-link p {
  margin: 0;
}

.sidebar-menu .nav-link:hover {
  background: linear-gradient(135deg, #f3b6c2, #e6f0ff);
  box-shadow: 0 6px 15px rgba(0,0,0,.12);
  transform: translateX(6px);
}

.sidebar-menu .nav-link.active {
  background: linear-gradient(135deg, #f3b6c2, #e6f0ff);
  box-shadow: 0 8px 18px rgba(0,0,0,.15);
}

.sidebar-menu .nav-treeview .nav-link {
  background: rgba(255,255,255,.55);
  margin-left: 18px;
  border-radius: 10px;
  font-size: 14px;
}

.sidebar-menu .nav-treeview .nav-link:hover {
  background: #ffffff;
}

.nav-arrow {
  float: right;
  transition: .3s;
}

.menu-open > .nav-link .nav-arrow {
  transform: rotate(90deg);
}
</style>

<ul
  class="nav sidebar-menu flex-column"
  data-lte-toggle="treeview"
  role="navigation"
  aria-label="Main navigation"
  data-accordion="false"
  id="navigation"
>

  
  <!-- HOME -->
  <li class="nav-item">
      <a href="dashboard_owner.php"
        class="nav-link <?= ($menu == 'home') ? 'active' : '' ?>">
          <p>🏠 Home</p>
      </a>
  </li>

  <!-- PRODUK -->
  <li class="nav-item">
    <a href="dashboard_owner.php?menu=produk"
       class="nav-link <?= ($menu == 'produk') ? 'active' : '' ?>">
      <p>🛍️ Produk</p>
    </a>
  </li>

  <!-- CUSTOMER -->
  <li class="nav-item">
    <a href="dashboard_owner.php?menu=customer"
       class="nav-link <?= ($menu == 'customer') ? 'active' : '' ?>">
      <p>📦 Data Customer</p>
    </a>
  </li>

  <!-- TRANSAKSI -->
  <li class="nav-item">
    <a href="dashboard_owner.php?menu=transaksi"
       class="nav-link <?= ($menu == 'transaksi') ? 'active' : '' ?>">
      <p>🛒 Data Transaksi</p>
    </a>
  </li>

  <!-- TARGET PENJUALAN -->
  <li class="nav-item">
      <a href="dashboard_owner.php?menu=target_sales"
        class="nav-link <?= $menu == 'target_sales' ? 'active' : '' ?>">
          <p>🎯 Target Penjualan</p>
      </a>
  </li>

  <!-- LAPORAN -->
  <li class="nav-item <?= in_array($menu, ['view_barang','view_customer','lap_transaksi']) ? 'menu-open' : '' ?>">
    <a href="#" class="nav-link">
      <p>
        🧾 Laporan
        <i class="nav-arrow bi bi-chevron-right"></i>
      </p>
    </a>

    <ul class="nav nav-treeview">
      <li class="nav-item">
        <a href="?menu=view_barang"
           class="nav-link <?= ($menu == 'view_barang') ? 'active' : '' ?>">
          <p>Laporan Produk</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="?menu=view_customer"
           class="nav-link <?= ($menu == 'view_customer') ? 'active' : '' ?>">
          <p>Laporan Customer</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="?menu=lap_transaksi"
           class="nav-link <?= ($menu == 'lap_transaksi') ? 'active' : '' ?>">
          <p>Laporan Transaksi</p>
        </a>
      </li>
    </ul>
  </li>

  <!-- PROFIL -->
  <li class="nav-item">
    <a href="dashboard_owner.php?menu=profil"
       class="nav-link <?= ($menu == 'profil') ? 'active' : '' ?>">
      <p>👤 Profil</p>
    </a>
  </li>

  <!-- LOGOUT -->
  <li class="nav-item">
    <a href="./login.php" class="nav-link">
      <p>🚪 Logout</p>
    </a>
  </li>

</ul>