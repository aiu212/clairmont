-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 10:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_desert`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_customer`
--

CREATE TABLE `tb_customer` (
  `id_customer` int(11) NOT NULL,
  `kode_customer` varchar(20) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama_customer` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_customer`
--

INSERT INTO `tb_customer` (`id_customer`, `kode_customer`, `id_user`, `nama_customer`, `email`, `no_hp`, `alamat`, `status`, `created_at`) VALUES
(1, 'CST-001', 3, 'Ayu', 'ayu@gmail.com', '08123456789', 'Tangerang, Banten', 'aktif', '2025-12-26 14:14:17'),
(2, 'CST-002', 6, 'Lala', 'lala@gmail.com', '089767898765', 'Cipondoh', 'aktif', '2025-12-27 07:28:59'),
(3, 'CST-003', 4, 'Lulu', 'lukiyatulparoh@gmail.com', '083948748218', 'Perum Mas', 'aktif', '2025-12-27 07:28:59'),
(4, 'CST-004', 10, 'Melia', 'melia@gmail.com', '089787656734', 'Gelam Jaya', 'aktif', '2025-12-27 07:28:59'),
(5, 'CST-005', 8, 'Putra', 'putra@gmail.com', '089847381982', 'Jakarta Utara', 'aktif', '2025-12-27 07:28:59'),
(6, 'CST-006', 9, 'Roki', 'roki@gmail.com', '081873637484', 'Tangerang Selatan', 'aktif', '2025-12-27 07:28:59'),
(7, 'CST-007', 11, 'Salma', 'salma@gmail.com', '081283748294', 'Kab.Tangerang', 'aktif', '2025-12-27 07:32:31'),
(8, 'CST-008', 5, 'Fatih', 'fatih@gmail.com', '083847384375', 'Bugel, Tangerang', 'aktif', '2025-12-27 07:32:31'),
(11, 'CST-011', 15, 'Putri Syahratu', 'ratu@gmail.com', '', '', 'aktif', '2026-01-05 22:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail_transaksi`
--

CREATE TABLE `tb_detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_detail_transaksi`
--

INSERT INTO `tb_detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `harga`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 25000, 2, 50000),
(36, 33, 47, 170000, 1, 170000),
(37, 34, 77, 11000, 10, 110000),
(38, 35, 104, 20000, 1, 20000),
(39, 35, 81, 13000, 1, 13000),
(40, 36, 47, 170000, 1, 170000),
(41, 36, 65, 20000, 1, 20000),
(42, 37, 98, 60000, 1, 60000),
(43, 37, 105, 18000, 1, 18000),
(44, 37, 66, 18000, 1, 18000),
(45, 37, 81, 13000, 1, 13000),
(46, 38, 107, 30000, 1, 30000),
(47, 38, 1, 25000, 1, 25000),
(48, 39, 33, 17000, 5, 85000),
(49, 41, 47, 170000, 3, 510000),
(50, 42, 107, 30000, 1, 30000),
(51, 42, 33, 17000, 1, 17000),
(52, 43, 87, 52000, 2, 104000),
(53, 44, 20, 147000, 1, 147000),
(54, 44, 39, 14000, 1, 14000),
(55, 44, 74, 17000, 1, 17000),
(56, 45, 87, 52000, 1, 52000),
(57, 45, 88, 55000, 1, 55000),
(58, 45, 33, 17000, 1, 17000),
(59, 46, 1, 25000, 4, 100000),
(60, 46, 5, 33000, 2, 66000),
(66, 51, 9, 32000, 6, 192000),
(67, 52, 33, 17000, 2, 34000),
(68, 53, 32, 18500, 20, 370000),
(69, 54, 32, 18500, 20, 370000),
(70, 55, 46, 170000, 1, 170000),
(71, 56, 86, 55000, 10, 550000),
(72, 57, 71, 16000, 1, 16000),
(73, 57, 10, 31000, 1, 31000),
(74, 57, 80, 14000, 1, 14000),
(76, 59, 16, 148000, 1, 148000),
(77, 59, 74, 17000, 1, 17000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `id_kategori` int(11) NOT NULL,
  `kode_kategori` varchar(20) DEFAULT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`id_kategori`, `kode_kategori`, `nama_kategori`, `gambar`, `status`, `created_at`) VALUES
(1, 'KTG-001', 'Dessert Box', 'dessertbox.jpeg', 'aktif', '2025-12-26 14:14:17'),
(2, 'KTG-002', 'Cakes', 'cakes.jpeg', 'aktif', '2025-12-26 14:14:17'),
(3, 'KTG-003', 'Drinks', 'drinks.jpeg', 'aktif', '2025-12-26 14:14:17'),
(4, 'KTG-004', 'Pudding', 'pudding.jpeg', 'aktif', '2025-12-27 06:46:12'),
(5, 'KTG-005', 'Cupcakes', 'cupcake.jpeg', 'aktif', '2025-12-27 06:46:12'),
(6, 'KTG-006', 'Cookies', 'cookies.jpeg', 'aktif', '2025-12-27 06:46:12'),
(7, 'KTG-007', 'Donuts', 'donuts.jpeg', 'aktif', '2025-12-27 06:54:49'),
(8, 'KTG-008', 'Chiffon & Roll Cakes', 'chiffon.jpeg', 'aktif', '2025-12-27 06:56:26');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pesanan_custom`
--

CREATE TABLE `tb_pesanan_custom` (
  `id_pesanan` int(11) NOT NULL,
  `kode_pesanan` varchar(20) DEFAULT NULL,
  `id_customer` int(11) NOT NULL,
  `deskripsi` text NOT NULL,
  `status` enum('menunggu','diproses','selesai','ditolak') DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pesanan_custom`
--

INSERT INTO `tb_pesanan_custom` (`id_pesanan`, `kode_pesanan`, `id_customer`, `deskripsi`, `status`, `created_at`) VALUES
(1, 'PCS-001', 1, 'Dessert box custom tema ulang tahun', 'menunggu', '2025-12-26 14:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `tb_produk`
--

CREATE TABLE `tb_produk` (
  `id_produk` int(11) NOT NULL,
  `kode_produk` varchar(20) DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_produk`
--

INSERT INTO `tb_produk` (`id_produk`, `kode_produk`, `id_kategori`, `nama_produk`, `harga`, `deskripsi`, `stok`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PRD-001', 1, 'Dessert Box Coklat', 25000, 'Dessert box rasa coklat premium', 20, 'coklat.jpeg', 'aktif', '2025-12-26 14:14:18', '2025-12-31 07:55:53'),
(3, 'PRD-003', 2, 'Cake Taro', 145000, 'Cake taro lembut dengan rasa khas', 20, 'ctaro.jpeg', 'aktif', '2025-12-26 14:14:18', '2025-12-31 07:55:53'),
(4, 'PRD-004', 1, 'Dessert Box Nutella', 32000, 'Dessert box rasa nutella creamy dan premium', 15, 'dessert_nutela.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(5, 'PRD-005', 1, 'Dessert Box Lotus', 33000, 'Dessert box rasa lotus biskuit karamel', 15, 'dlotus.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(6, 'PRD-006', 1, 'Dessert Box Oreo', 30000, 'Dessert box rasa oreo lembut dan manis', 20, 'doreo.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(7, 'PRD-007', 1, 'Dessert Box Red Velvet', 35000, 'Dessert box red velvet dengan cream cheese', 12, 'dredvelvet.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(8, 'PRD-008', 1, 'Dessert Box Taro', 30000, 'Dessert box rasa taro lembut dan wangi', 18, 'dtaro.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(9, 'PRD-009', 1, 'Dessert Box Tiramisu', 32000, 'Dessert box tiramisu dengan rasa kopi lembut', 9, 'dtiramisu.jpeg', 'aktif', '2025-12-27 08:51:46', '2026-01-06 16:20:51'),
(10, 'PRD-010', 1, 'Dessert Box KitKat', 31000, 'Dessert box coklat kitkat crunchy', 15, 'kitkat.jpeg', 'aktif', '2025-12-27 08:51:46', '2026-05-19 19:59:13'),
(11, 'PRD-011', 1, 'Dessert Box Strawberry Coffee', 33000, 'Dessert box perpaduan strawberry dan coffee', 14, 'strcof.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(12, 'PRD-012', 1, 'Dessert Box Strawberry Cheese', 34000, 'Dessert box strawberry dengan cream cheese', 14, 'strwchese.jpeg', 'aktif', '2025-12-27 08:51:46', '2025-12-31 07:55:53'),
(13, 'PRD-013', 1, 'Dessert Box Matcha', 32000, 'Dessert box rasa matcha lembut dan wangi', 15, 'dmacha.jpeg', 'aktif', '2025-12-27 09:12:31', '2025-12-31 07:55:53'),
(14, 'PRD-014', 2, 'Cake Caramel', 145000, 'Cake rasa caramel lembut dan manis', 14, 'ccaramel.jpeg', 'aktif', '2025-12-27 09:29:11', '2025-12-31 12:51:01'),
(15, 'PRD-015', 2, 'Cake Coklat Coffee', 147000, 'Cake coklat dengan aroma coffee', 10, 'ccoklatcofe.jpeg', 'aktif', '2025-12-27 09:29:11', '2025-12-31 07:55:53'),
(16, 'PRD-016', 2, 'Cake Cheese', 148000, 'Cake keju lembut dan creamy', 9, 'cchesee.jpeg', 'aktif', '2025-12-27 09:29:11', '2026-05-20 08:08:14'),
(17, 'PRD-017', 2, 'Cake Coklat Oreo', 146000, 'Cake coklat dengan topping oreo', 40, 'ccoklatoreo.jpeg', 'aktif', '2025-12-27 09:29:11', '2026-01-06 17:12:38'),
(18, 'PRD-018', 2, 'Cake Marshmallow', 145000, 'Cake manis dengan marshmallow lembut', 10, 'cmarsmellow.jpeg', 'aktif', '2025-12-27 09:29:11', '2025-12-31 07:55:53'),
(20, 'PRD-020', 2, 'Cake Coffee', 147000, 'Cake rasa coffee yang rich dan wangi', 10, 'coffee.jpeg', 'aktif', '2025-12-27 09:29:11', '2025-12-31 07:55:53'),
(21, 'PRD-021', 2, 'Cake Strawberry Coklat', 146000, 'Cake strawberry dipadukan coklat', 10, 'cstrcok.jpeg', 'aktif', '2025-12-27 09:29:11', '2025-12-31 07:55:53'),
(22, 'PRD-022', 2, 'Cake Vanila', 144000, 'Cake vanila lembut dan ringan', 10, 'cvanila.jpeg', 'aktif', '2025-12-27 09:29:11', '2025-12-31 07:55:53'),
(23, 'PRD-023', 5, 'Cupcake Lotus', 18000, 'Cupcake lembut dengan topping lotus', 20, 'cplotus.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(24, 'PRD-024', 5, 'Cupcake Caramel', 18000, 'Cupcake rasa caramel manis', 20, 'cpcaramel.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(25, 'PRD-025', 5, 'Cupcake Dark Chocolate', 19000, 'Cupcake coklat hitam pekat', 20, 'cpdarkchoc.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(26, 'PRD-026', 5, 'Cupcake Blueberry', 18000, 'Cupcake blueberry dengan rasa segar', 20, 'cpblueberry.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(27, 'PRD-027', 5, 'Cupcake Strawberry Coklat', 18500, 'Cupcake strawberry dengan sentuhan coklat', 20, 'cpstrwcok.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(28, 'PRD-028', 5, 'Cupcake Red Velvet', 19000, 'Cupcake red velvet lembut dan moist', 20, 'cpredvelvet.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(29, 'PRD-029', 5, 'Cupcake Strawberry', 18000, 'Cupcake rasa strawberry manis', 20, 'cpstrw.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(30, 'PRD-030', 5, 'Cupcake Vanilla', 17000, 'Cupcake vanilla klasik dan lembut', 20, 'cpvanilla.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(31, 'PRD-031', 5, 'Cupcake Choco Chips', 18000, 'Cupcake coklat dengan choco chips', 20, 'cpchocochips.jpeg', 'aktif', '2025-12-27 09:41:21', '2025-12-31 07:55:53'),
(32, 'PRD-032', 5, 'Cupcake Oreo', 18500, 'Cupcake oreo creamy dan crunchy', 0, 'cporeo.jpeg', 'aktif', '2025-12-27 09:41:21', '2026-01-06 17:15:32'),
(33, 'PRD-033', 4, 'Pudding Banana', 17000, 'Pudding pisang lembut dan manis', 68, 'pbanana.jpeg', 'aktif', '2025-12-27 09:48:45', '2026-01-06 16:59:32'),
(34, 'PRD-034', 4, 'Pudding Buah', 13000, 'Pudding campuran buah segar', 27, 'pbuah.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 12:37:33'),
(35, 'PRD-035', 4, 'Pudding Coffee', 13000, 'Pudding rasa kopi lembut', 25, 'pcoffee.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(36, 'PRD-036', 4, 'Pudding Strawberry', 12000, 'Pudding strawberry manis dan segar', 25, 'pstrw.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(37, 'PRD-037', 4, 'Pudding Strawberry Vanilla', 13500, 'Pudding strawberry dengan vanilla lembut', 25, 'pstrwvanilla.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(38, 'PRD-038', 4, 'Pudding Mango', 13000, 'Pudding mangga segar dan manis', 25, 'pmango.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(39, 'PRD-039', 4, 'Pudding Dark Chocolate', 14000, 'Pudding coklat hitam premium', 25, 'pdarkchoc.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(40, 'PRD-040', 4, 'Pudding Caramel', 13500, 'Pudding caramel creamy', 25, 'pcaramel.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(41, 'PRD-041', 4, 'Pudding Chocolate', 13000, 'Pudding coklat klasik lembut', 25, 'pchoco.jpeg', 'aktif', '2025-12-27 09:48:45', '2025-12-31 07:55:53'),
(46, 'PRD-042', 2, 'Cake Vanilla Strawberry', 170000, 'Cake vanilla lembut dengan lapisan strawberry', 9, 'cvanilastrw.jpeg', 'aktif', '2025-12-27 09:54:21', '2026-01-06 17:24:59'),
(47, 'PRD-043', 2, 'Cake Black Forest', 170000, 'Cake coklat black forest dengan cherry', 20, 'cblackfores.jpeg', 'aktif', '2025-12-27 09:54:21', '2025-12-31 12:51:26'),
(58, 'PRD-048', 3, 'Milkshake Matcha', 18000, 'Minuman susu dengan matcha premium', 30, 'mlkmatcha.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(59, 'PRD-049', 3, 'Milkshake Coffee', 18000, 'Susu kopi creamy dan lembut', 30, 'mlkcoffee.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(60, 'PRD-050', 3, 'Milkshake Choco Bubble', 19000, 'Minuman coklat susu dengan bubble', 30, 'mlkchocobubble.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(61, 'PRD-051', 3, 'Milkshake Choco Oreo', 19000, 'Coklat susu dengan oreo crunchy', 30, 'mlkchocoreo.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(62, 'PRD-052', 3, 'Milkshake Strawberry', 18000, 'Susu strawberry manis dan segar', 30, 'mlkstrw.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(63, 'PRD-053', 3, 'Lemon Sprite', 15000, 'Minuman lemon segar dengan sprite', 30, 'lemonsprit.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(64, 'PRD-054', 3, 'Lemon Tea', 15000, 'Teh lemon segar', 30, 'lemontea.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(65, 'PRD-055', 3, 'Dalgona Coffee', 20000, 'Kopi dalgona creamy dan bold', 30, 'dalgonacoffee.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(66, 'PRD-056', 3, 'Milkshake Coconut', 18000, 'Minuman susu kelapa segar', 30, 'mlkcoconut.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 12:40:32'),
(67, 'PRD-057', 3, 'Milkshake Mango', 18000, 'Susu mangga manis dan creamy', 30, 'mlkmango.jpeg', 'aktif', '2025-12-27 10:08:01', '2025-12-31 07:55:53'),
(68, 'PRD-058', 6, 'Cookies Matcha', 15000, 'Cookies matcha dengan rasa khas teh hijau', 25, 'ckmatcha.jpeg', 'aktif', '2025-12-27 10:13:13', '2025-12-31 07:55:53'),
(69, 'PRD-059', 6, 'Cookies Choco Cheese', 16000, 'Cookies coklat dengan isian keju', 25, 'ckchocochesee.jpeg', 'aktif', '2025-12-27 10:13:13', '2025-12-31 07:55:53'),
(70, 'PRD-060', 6, 'Cookies Caramel', 15000, 'Cookies caramel manis dan renyah', 25, 'ckcaramel.jpeg', 'aktif', '2025-12-27 10:13:13', '2025-12-31 07:55:53'),
(71, 'PRD-061', 6, 'Cookies Marshmallow', 16000, 'Cookies dengan marshmallow lembut', 24, 'ckmarsmellow.jpeg', 'aktif', '2025-12-27 10:13:13', '2026-05-19 19:59:13'),
(72, 'PRD-062', 6, 'Cookies Cheese Red Velvet', 17000, 'Cookies red velvet dengan keju creamy', 25, 'ckcheseeredvelvet.jpeg', 'aktif', '2025-12-27 10:13:13', '2025-12-31 07:55:53'),
(73, 'PRD-063', 6, 'Cookies Chocolate', 15000, 'Cookies coklat klasik', 25, 'ckchoco.jpeg', 'aktif', '2025-12-27 10:13:13', '2025-12-31 07:55:53'),
(74, 'PRD-064', 6, 'Cookies Double Chocolate', 17000, 'Cookies coklat ganda lebih rich', 24, 'ckdoublechoco.jpeg', 'aktif', '2025-12-27 10:13:13', '2026-05-20 08:08:14'),
(75, 'PRD-065', 7, 'Donut Peanut', 12000, 'Donut topping kacang renyah', 30, 'dnpeanut.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(76, 'PRD-066', 7, 'Donut Chocolate', 12000, 'Donut coklat manis dan lembut', 30, 'dnchoco.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(77, 'PRD-067', 7, 'Donut Meses', 11000, 'Donut meses coklat klasik', 30, 'dnmeses.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(78, 'PRD-068', 7, 'Donut Blink', 13000, 'Donut topping blink warna-warni', 30, 'dnblink.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(79, 'PRD-069', 7, 'Donut Oreo', 13000, 'Donut topping oreo crunchy', 30, 'dnoreo.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(80, 'PRD-070', 7, 'Donut Pistachio', 14000, 'Donut topping pistachio premium', 29, 'dnpistacio.jpeg', 'aktif', '2025-12-27 10:22:02', '2026-05-19 19:59:13'),
(81, 'PRD-071', 7, 'Donut Caramel', 13000, 'Donut caramel manis creamy', 30, 'dncaramel.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(82, 'PRD-072', 7, 'Donut Strawberry', 12000, 'Donut topping strawberry manis', 30, 'dnstrw.jpeg', 'aktif', '2025-12-27 10:22:02', '2025-12-31 07:55:53'),
(83, 'PRD-073', 7, 'Paket Donuts', 130000, 'Paket isi 12 donuts berbagai varian', 15, 'paketdonuts.jpeg', 'aktif', '2025-12-27 10:24:22', '2025-12-31 07:55:53'),
(84, 'PRD-074', 8, 'Chiffon Blueberry', 55000, 'Chiffon cake rasa blueberry lembut', 10, 'cfblueberry.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(85, 'PRD-075', 8, 'Chiffon Vanilla', 50000, 'Chiffon vanilla klasik lembut', 10, 'cfvanilla.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(86, 'PRD-076', 8, 'Chiffon Matcha', 55000, 'Chiffon cake matcha harum', 0, 'cfmatcha.jpeg', 'aktif', '2025-12-27 10:31:30', '2026-01-06 17:31:17'),
(87, 'PRD-077', 8, 'Chiffon Lemon', 52000, 'Chiffon lemon segar', 10, 'cflemon.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(88, 'PRD-078', 8, 'Chiffon Chocolate', 55000, 'Chiffon coklat lembut', 10, 'cfchoco.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(89, 'PRD-079', 8, 'Chiffon Pandan', 52000, 'Chiffon pandan wangi dan lembut', 10, 'cfpandan.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(90, 'PRD-080', 8, 'Chiffon Strawberry', 55000, 'Chiffon strawberry manis', 10, 'cfstrw.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(91, 'PRD-081', 8, 'Roll Cake Mango', 60000, 'Roll cake mangga manis dan lembut', 8, 'rcmango.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(92, 'PRD-082', 8, 'Roll Cake Lemon', 58000, 'Roll cake lemon segar', 8, 'rclemon.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(93, 'PRD-083', 8, 'Roll Cake Red Velvet', 65000, 'Roll cake red velvet creamy', 8, 'rcredvelvet.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(94, 'PRD-084', 8, 'Roll Cake Peanut', 60000, 'Roll cake kacang lembut', 8, 'rcpeanut.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(95, 'PRD-085', 8, 'Roll Cake Black Forest', 68000, 'Roll cake black forest coklat cherry', 8, 'rcblackfores.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(96, 'PRD-086', 8, 'Roll Cake Strawberry Matcha', 65000, 'Roll cake strawberry dengan matcha', 8, 'rcstrwmatcha.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(97, 'PRD-087', 8, 'Roll Cake Oreo', 62000, 'Roll cake oreo creamy', 8, 'rcoreo.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(98, 'PRD-088', 8, 'Roll Cake Strawberry', 60000, 'Roll cake strawberry manis', 8, 'rcstrw.jpeg', 'aktif', '2025-12-27 10:31:30', '2025-12-31 07:55:53'),
(99, 'PRD-099', 2, 'Green Tea Cake', 145000, 'Cake green tea lembut dan premium', 20, 'cgreentea.jpeg', 'aktif', '2025-12-30 05:08:02', '2025-12-31 07:55:53'),
(101, 'PRD-101', 5, 'Cupcake Hazelnut', 19000, 'Cupcake lembut dengan rasa hazelnut', 20, 'cphazelnut.jpeg', 'aktif', '2025-12-30 05:17:49', '2025-12-31 07:55:53'),
(102, 'PRD-102', 5, 'Paket Birthday Cupcake', 45000, 'Paket cupcake isi 6 edisi ulang tahun', 20, 'cpbday.jpeg', 'aktif', '2025-12-30 05:17:49', '2025-12-31 07:55:53'),
(103, 'PRD-103', 3, 'Mango Iced', 17000, 'Minuman mangga segar dengan es', 30, 'drmangoiced.jpeg', 'aktif', '2025-12-30 05:27:44', '2025-12-31 07:55:53'),
(104, 'PRD-104', 3, 'Aneka Smoothies', 20000, 'Minuman smoothies segar dan creamy', 33, 'drsmoothies.jpeg', 'aktif', '2025-12-30 05:27:44', '2026-01-02 15:49:14'),
(105, 'PRD-105', 6, 'Strawberry Cheese Cookies', 18000, 'Cookies stroberi dengan keju lembut', 25, 'ckstrwchesee.jpeg', 'aktif', '2025-12-30 06:03:18', '2025-12-31 07:55:53'),
(107, 'PRD-106', 1, 'Dessert Box Buah', 30000, NULL, 25, 'buah.jpeg', 'aktif', '2025-12-31 04:51:09', '2025-12-31 11:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `tb_stok`
--

CREATE TABLE `tb_stok` (
  `id_stok` int(11) NOT NULL,
  `kode_stok` varchar(20) DEFAULT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tipe` enum('masuk','keluar') NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_stok`
--

INSERT INTO `tb_stok` (`id_stok`, `kode_stok`, `id_produk`, `jumlah`, `tipe`, `keterangan`, `tanggal`) VALUES
(1, 'STK-001', 1, 20, 'masuk', 'Stok awal', '2025-12-26 14:14:18'),
(3, 'STK-003', 3, 10, 'masuk', 'Stok awal', '2025-12-26 14:14:18'),
(4, NULL, 14, 4, 'masuk', NULL, '2025-12-31 05:51:01'),
(5, NULL, 47, 10, 'masuk', NULL, '2025-12-31 05:51:26'),
(6, NULL, 104, 3, 'masuk', NULL, '2026-01-02 08:49:14'),
(7, NULL, 17, 15, 'masuk', NULL, '2026-01-06 10:11:39'),
(8, NULL, 17, 15, 'masuk', NULL, '2026-01-06 10:12:38'),
(9, NULL, 32, 20, 'masuk', NULL, '2026-01-06 10:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `kode_transaksi` varchar(30) DEFAULT NULL,
  `id_customer` int(11) NOT NULL,
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_harga` int(11) NOT NULL,
  `metode_pembayaran` enum('COD','Transfer') NOT NULL,
  `status_transaksi` enum('pending','proses','selesai','batal') NOT NULL DEFAULT 'pending',
  `bukti_transfer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_transaksi`, `kode_transaksi`, `id_customer`, `tanggal_transaksi`, `total_harga`, `metode_pembayaran`, `status_transaksi`, `bukti_transfer`) VALUES
(1, 'TRX-001', 1, '2025-12-26 14:14:18', 50000, 'COD', 'selesai', NULL),
(33, 'TRX-003', 4, '2025-12-30 02:04:11', 170000, 'Transfer', 'selesai', 'bukti-6.png'),
(34, 'TRX-004', 4, '2025-12-30 02:05:08', 110000, 'COD', 'selesai', NULL),
(35, 'TRX-005', 4, '2025-12-30 02:09:42', 33000, 'COD', 'selesai', NULL),
(36, 'TRX-036', 4, '2025-12-30 22:57:16', 190000, 'Transfer', 'selesai', 'bukti-5.png'),
(37, 'TRX-037', 8, '2025-12-30 23:03:18', 109000, 'Transfer', 'selesai', 'bukti-4.png'),
(38, 'TRX-038', 8, '2026-01-01 20:16:13', 55000, 'Transfer', 'selesai', 'bukti-3.png'),
(39, 'TRX-039', 5, '2026-01-01 23:31:15', 85000, 'COD', 'selesai', NULL),
(41, 'TRX-040', 5, '2026-01-01 23:57:06', 510000, 'COD', 'selesai', NULL),
(42, 'TRX-042', 5, '2026-01-02 03:04:18', 47000, 'COD', 'proses', NULL),
(43, 'TRX-043', 5, '2026-01-05 00:48:46', 104000, 'Transfer', 'selesai', 'bukti-2.png'),
(44, 'TRX-044', 5, '2026-01-05 04:21:35', 178000, 'Transfer', 'selesai', 'bukti-1.png'),
(45, 'TRX-045', 5, '2026-01-05 04:25:15', 124000, 'COD', 'selesai', NULL),
(46, 'TRX-046', 5, '2026-01-05 07:25:22', 166000, 'COD', 'selesai', NULL),
(51, 'TRX-0047', 5, '2026-01-06 03:20:51', 192000, 'COD', 'batal', NULL),
(52, 'TRX-0052', 3, '2026-01-06 03:59:32', 34000, 'COD', 'pending', NULL),
(53, 'TRX-0053', 2, '2026-01-06 04:02:25', 370000, 'Transfer', 'batal', NULL),
(54, 'TRX-0054', 2, '2026-01-06 04:15:32', 370000, 'Transfer', 'selesai', 'bukti-8.png'),
(55, 'TRX-0055', 4, '2026-01-06 04:24:59', 170000, 'Transfer', 'proses', 'bukti_1767695099_538.png'),
(56, 'TRX-0056', 11, '2026-01-06 04:31:17', 550000, 'Transfer', 'selesai', 'bukti_1767695477_419.png'),
(57, 'TRX-0057', 11, '2026-05-19 07:59:13', 61000, 'COD', 'selesai', ''),
(59, 'TRX-0058', 11, '2026-05-19 20:08:14', 165000, 'COD', 'pending', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_customer`
--
ALTER TABLE `tb_customer`
  ADD PRIMARY KEY (`id_customer`),
  ADD UNIQUE KEY `kode_customer` (`kode_customer`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_detail_transaksi`
--
ALTER TABLE `tb_detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `kode_kategori` (`kode_kategori`);

--
-- Indexes for table `tb_pesanan_custom`
--
ALTER TABLE `tb_pesanan_custom`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `id_customer` (`id_customer`);

--
-- Indexes for table `tb_produk`
--
ALTER TABLE `tb_produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `tb_stok`
--
ALTER TABLE `tb_stok`
  ADD PRIMARY KEY (`id_stok`),
  ADD UNIQUE KEY `kode_stok` (`kode_stok`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `id_customer` (`id_customer`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_customer`
--
ALTER TABLE `tb_customer`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_detail_transaksi`
--
ALTER TABLE `tb_detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tb_pesanan_custom`
--
ALTER TABLE `tb_pesanan_custom`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_produk`
--
ALTER TABLE `tb_produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `tb_stok`
--
ALTER TABLE `tb_stok`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_customer`
--
ALTER TABLE `tb_customer`
  ADD CONSTRAINT `tb_customer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_users` (`id_user`);

--
-- Constraints for table `tb_detail_transaksi`
--
ALTER TABLE `tb_detail_transaksi`
  ADD CONSTRAINT `tb_detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `tb_transaksi` (`id_transaksi`),
  ADD CONSTRAINT `tb_detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `tb_produk` (`id_produk`);

--
-- Constraints for table `tb_pesanan_custom`
--
ALTER TABLE `tb_pesanan_custom`
  ADD CONSTRAINT `tb_pesanan_custom_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `tb_customer` (`id_customer`);

--
-- Constraints for table `tb_produk`
--
ALTER TABLE `tb_produk`
  ADD CONSTRAINT `tb_produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori` (`id_kategori`);

--
-- Constraints for table `tb_stok`
--
ALTER TABLE `tb_stok`
  ADD CONSTRAINT `tb_stok_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `tb_produk` (`id_produk`);

--
-- Constraints for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `tb_transaksi_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `tb_customer` (`id_customer`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
