CREATE DATABASE IF NOT EXISTS marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE marketplace;

-- Database: marketplace
CREATE TABLE IF NOT EXISTS penjual (
  id_penjual INT AUTO_INCREMENT PRIMARY KEY,
  nama_toko VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kategori (
  id_kategori INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produk (
  id_produk INT AUTO_INCREMENT PRIMARY KEY,
  id_penjual INT NOT NULL,
  id_kategori INT NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  harga DECIMAL(12,0) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) DEFAULT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_penjual) REFERENCES penjual(id_penjual) ON UPDATE CASCADE,
  FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pembeli (
  id_pembeli INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  telepon VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pesanan (
  id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan VARCHAR(20) NOT NULL UNIQUE,
  id_pembeli INT NOT NULL,
  tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(14,0) NOT NULL,
  status ENUM('baru','diproses','dikirim','selesai','batal') NOT NULL DEFAULT 'baru',
  FOREIGN KEY (id_pembeli) REFERENCES pembeli(id_pembeli) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detail_pesanan (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_pesanan INT NOT NULL,
  id_produk INT NOT NULL,
  jumlah INT NOT NULL,
  harga_satuan DECIMAL(12,0) NOT NULL,
  subtotal DECIMAL(14,0) NOT NULL,
  FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Data awal. Login penjual: admin@toko.test / password  (SEGERA GANTI setelah deploy)
INSERT INTO penjual (nama_toko, email, password) VALUES
('Toko Bengkulu', 'admin@toko.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO kategori (nama_kategori) VALUES ('Makanan'),('Pakaian'),('Skincare'),('Figure');
INSERT INTO produk (id_penjual,id_kategori,nama_produk,deskripsi,harga,stok) VALUES
(1,1,'Kopi Kepahiang 250g','Kopi robusta khas Bengkulu, sangrai medium.',45000,50),
(1,1,'Pendap Instan','Olahan ikan khas Bengkulu dalam kemasan siap saji.',30000,30),
(1,2,'Kaos Daily Pastel','Kaos nyaman untuk gaya sehari-hari.',85000,40),
(1,3,'Gentle Facial Wash','Pembersih wajah untuk rutinitas harian.',59000,25),
(1,4,'Mini Character Figure','Figure karakter lucu untuk koleksi dan pajangan.',125000,12);
