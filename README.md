# Tokoharu Marketplace — REST API (PHP Native + MySQLi)

REST API untuk database marketplace Soal 11. Menghasilkan JSON, memakai *prepared statement* MySQLi, dan transaksi database untuk pembuatan pesanan.

## Menjalankan di XAMPP
1. Salin folder ini ke `C:\xampp\htdocs\marketplace-api`.
2. Jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` → **Import** → pilih `database/marketplace.sql` (otomatis membuat database `marketplace`).
   Jika sudah memakai database Soal 11, langkah ini boleh dilewati.
4. Salin `config/config.example.php` → `config/config.php` (default XAMPP: user `root`, password kosong).
5. Uji di browser: `http://localhost/marketplace-api/api/produk.php`

## Endpoint
| Method | URL | Fungsi | Status |
|---|---|---|---|
| GET | `/api/produk.php` | Daftar produk (`q`, `kategori`, `page`, `limit`) | 200, 400 |
| GET | `/api/produk.php?id=1` | Detail produk | 200, 400, 404 |
| GET | `/api/kategori.php` | Daftar kategori (`?id=` opsional) | 200, 400, 404 |
| POST | `/api/pesanan.php` | Buat pesanan (body JSON) | 201, 400, 404, 409, 422 |
| GET | `/api/pesanan.php?kode=INV...` | Lihat pesanan | 200, 400, 404 |

Contoh body `POST /api/pesanan.php` (`Content-Type: application/json`):
```json
{
  "pembeli": {"nama": "nana chimi", "email": "nachimi@gmail.com", "telepon": "081234567890", "alamat": "Jl. Merpati No. 10, Bengkulu"},
  "items": [{"id_produk": 1, "jumlah": 2}]
}
```

## Postman
Import `postman/Marketplace_API.postman_collection.json`. Variabel `base_url` default `http://localhost/marketplace-api/api`.


## Struktur
```
api/        _bootstrap.php, produk.php, kategori.php, pesanan.php, index.php
config/     config.example.php
database/   marketplace.sql
docs/       DOKUMEN_RANCANGAN_API.md, LAPORAN_PENGUJIAN.md
postman/    Marketplace_API.postman_collection.json
```
