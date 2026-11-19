SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `marketplace`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `marketplace`;

-- Hapus tabel lama (urutan anak -> induk) agar file ini bisa diimpor berulang kali
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `detail_pesanan`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `pembeli`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `penjual`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tabel penjual
-- ------------------------------------------------------------
CREATE TABLE `penjual` (
  `id_penjual` int(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_penjual`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `penjual` (`id_penjual`, `nama_toko`, `email`, `password`, `created_at`) VALUES
(1, 'juju fashion', 'juju@123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-11-18 16:54:36');

-- ------------------------------------------------------------
-- Tabel kategori
-- ------------------------------------------------------------
CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(80) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Makanan'),
(2, 'Skincare'),
(3, 'Pakaian'),
(4, 'Figure');

-- ------------------------------------------------------------
-- Tabel pembeli
-- ------------------------------------------------------------
CREATE TABLE `pembeli` (
  `id_pembeli` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pembeli`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pembeli` (`id_pembeli`, `nama`, `email`, `telepon`, `alamat`, `created_at`) VALUES
(1, 'tata', 'tata@gmail.com', '082254679009', 'jalan kampung bali', '2026-11-18 17:25:39'),
(2, 'yua', 'yua@gmail.com', '089966553443', 'bajak', '2026-11-18 21:26:45'),
(3, 'sean', 'sean@gmail.com', '089966553443', 'jalan merpati no 20', '2026-11-19 08:41:30');

-- ------------------------------------------------------------
-- Tabel produk
-- ------------------------------------------------------------
CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjual` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(12,0) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_produk`),
  KEY `id_penjual` (`id_penjual`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_penjual`) REFERENCES `penjual` (`id_penjual`) ON UPDATE CASCADE,
  CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `produk` (`id_produk`, `id_penjual`, `id_kategori`, `nama_produk`, `deskripsi`, `harga`, `stok`, `gambar`, `status`, `created_at`) VALUES
(1, 1, 3, 'leather jacket', '', 80000, 50, '96b1d317b9c136b8.jpg', 'aktif', '2026-11-18 16:54:36'),
(2, 1, 3, 'brown and blue skirt', 'A retro, Y2K-inspired midi skirt in brown and blue plaid cotton, featuring a flattering asymmetrical handkerchief hem and a fitted waistband.', 70000, 30, '3375054bdabe6349.jpg', 'aktif', '2026-11-18 16:54:36'),
(3, 1, 3, 'gaun', 'gaun imup cocok anda gunakan for porm night', 150000, 15, '0d1fd3f0ee322444.jpg', 'aktif', '2026-11-18 16:54:36'),
(4, 1, 3, 'y2k shirt', '', 85000, 39, '1262a6b265779986.jpg', 'aktif', '2026-11-18 16:54:36'),
(5, 1, 1, 'turkish delight', 'cemilan manis from turkey for your loml', 45000, 20, '1c2b4e9e54207e19.jpg', 'aktif', '2026-11-18 18:36:12'),
(6, 1, 1, 'dorayaki', 'dorayaki approved by doraemon', 35000, 10, '9b8b26bbddd26cd5.jpg', 'aktif', '2026-11-18 18:38:57'),
(7, 1, 2, 'Beauty of Joseon Relief Sun Rice', 'BEAUTY OF JOSEON Relief Sun Rice & Probiotics SPF50+ PA++++', 110000, 23, '6e71ce902fb7dcf8.jpg', 'aktif', '2026-11-18 18:42:04'),
(8, 1, 2, 'Cherry Blossom Glow Jam Cleanser', 'This gentle daily cleanser features a bouncy jelly texture and is infused with Cherry Blossom Extract and betaine to leave skin feeling fresh, hydrated, and visibly luminous. This K-beauty gel cleanser is vegan & cruelty-free, sulfate free, and suitable for all skin types and helps promote a radiant, healthy-looking glow.', 100000, 15, 'b95f59620deb4bde.jpg', 'aktif', '2026-11-18 18:45:18'),
(9, 1, 4, 'Hirono Shelter Series Figures The Warrior Icon', 'The Warrior Hirono Figurine Cute Icon Phone Widget Star Aesthetic', 300000, 5, '08571e3f96574009.jpg', 'aktif', '2026-11-18 21:11:40'),
(10, 1, 4, 'Cute Shinchan Figures', 'Cute Shinchan Crawling Toy', 290000, 3, 'd7b8f1500c3d7899.jpg', 'aktif', '2026-11-18 21:14:47'),
(11, 1, 4, 'Chibi Maruko-chan Figures', 'Chibi Maruko-chan\'s Quirky Adventures Series Figures', 309984, 1, '54d4554f18592505.jpg', 'aktif', '2026-11-18 21:18:16');

-- ------------------------------------------------------------
-- Tabel pesanan
-- ------------------------------------------------------------
CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pesanan` varchar(20) NOT NULL,
  `id_pembeli` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total` decimal(14,0) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'baru',
  PRIMARY KEY (`id_pesanan`),
  UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  KEY `id_pembeli` (`id_pembeli`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_pembeli`, `tanggal`, `total`, `status`) VALUES
(1, 'INV26111873F3A', 1, '2026-11-18 09:25:39', 85000, 'diproses'),
(2, 'INV2611185E6DC', 2, '2026-11-18 13:26:45', 110000, 'baru'),
(3, 'INV26111913AF9', 3, '2026-11-19 00:41:30', 419984, 'baru');

-- ------------------------------------------------------------
-- Tabel detail_pesanan
-- ------------------------------------------------------------
CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,0) NOT NULL,
  `subtotal` decimal(14,0) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 4, 1, 85000, 85000),
(2, 2, 7, 1, 110000, 110000),
(3, 3, 7, 1, 110000, 110000),
(4, 3, 11, 1, 309984, 309984);
