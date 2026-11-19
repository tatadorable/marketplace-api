<?php
// Salin file ini menjadi config/config.php lalu sesuaikan.
// JANGAN meng-commit config/config.php ke GitHub (sudah ada di .gitignore).
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // XAMPP default
define('DB_PASS', '');              // XAMPP default: kosong
define('DB_NAME', 'marketplace');   // database dari Soal 11

// Awalan URL gambar produk (opsional). Contoh: 'http://localhost/marketplace/uploads/'
define('IMAGE_BASE_URL', 'http://localhost/marketplace/uploads/');
