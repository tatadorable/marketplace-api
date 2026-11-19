# Laporan Hasil Pengujian REST API — Marketplace

**Lingkungan:** XAMPP (Apache + MySQL) di komputer lokal, `http://localhost/marketplace-api/api`
**Database:** `marketplace` (`database/marketplace.sql`)
**Alat uji:** browser (request GET) dan Postman (request POST)
**Catatan:** seluruh bukti di bawah adalah screenshot dari aplikasi yang benar-benar berjalan, bukan JSON buatan.

## Ringkasan Hasil

| No | Skenario | Request | Hasil | Bukti |
|----|----------|---------|-------|-------|
| 1 | Menampilkan seluruh produk | `GET /produk.php` | 200, total 11 produk | Gambar 1 |
| 2 | Detail produk berdasarkan ID | `GET /produk.php?id=1` | 200, "leather jacket", stok 50 | Gambar 2 |
| 3 | Daftar kategori | `GET /kategori.php` | 200 | Gambar 3 |
| 4 | Membuat pesanan | `POST /pesanan.php` | 201 Created, kode `INV2611180EC65` | Gambar 4 |
| 5 | Stok berkurang setelah pesanan | `GET /produk.php?id=1` | stok 50 → 46 | Gambar 5 |
| 6 | Data tersimpan di MySQL | phpMyAdmin | baris baru di `pesanan`, `pembeli`, `detail_pesanan` | Gambar 6–8 |

## 1. GET semua produk
URL: `http://localhost/marketplace-api/api/produk.php`

Response: `status: success`, `code: 200`, `total: 11`, `page: 1`, `limit: 20`, `total_pages: 1`. Setiap item memuat objek `kategori` dan `penjual`.

![Gambar 1 — GET semua produk](../screenshots/01_browser_produk.png)

## 2. GET detail produk (id=1)
URL: `http://localhost/marketplace-api/api/produk.php?id=1`

Response: `code: 200`; produk "leather jacket", harga 80000, stok 50, kategori Pakaian, penjual "juju fashion".

![Gambar 2 — GET detail produk](../screenshots/02_browser_detail_produk.png)

## 3. GET daftar kategori
URL: `http://localhost/marketplace-api/api/kategori.php`

![Gambar 3 — GET kategori](../screenshots/03_browser_kategori.png)

## 4. POST buat pesanan (Postman)
Request: `POST http://localhost/marketplace-api/api/pesanan.php`, Body → raw → JSON:

```json
{
  "pembeli": {
    "nama": "nana chimi",
    "email": "nachimi@gmail.com",
    "telepon": "081234567890",
    "alamat": "Jl. Merpati No. 10, Bengkulu"
  },
  "items": [ { "id_produk": 1, "jumlah": 2 } ]
}
```

Response: **201 Created** (62 ms, 951 B), `message: "Pesanan berhasil dibuat."`, `kode_pesanan: INV2611180EC65`, `status: baru`, `nama_pembeli: nana chimi`, item id_produk 1 "leather jacket" jumlah 2.

![Gambar 4 — POST pesanan di Postman](../screenshots/04_postman_post_pesanan.png)

Pengujian POST dilakukan dua kali. Pengiriman pertama berisi 2 unit leather jacket (id 1) dan 1 unit gaun (id 3); pengiriman kedua berisi 2 unit leather jacket (screenshot di atas).

## 5. Stok berkurang setelah pesanan
Request: `GET http://localhost/marketplace-api/api/produk.php?id=1`. Sebelum POST stok leather jacket 50 (Gambar 2); setelah dua kali POST (2 + 2 unit) stok menjadi 46.

![Gambar 5 — Detail produk setelah POST](../screenshots/05_browser_detail_setelah_post.png)

## 6. Data tersimpan di database MySQL (phpMyAdmin)

**Tabel `pesanan`** — dua pesanan baru dari pengujian (id 3 dan 4):

| id_pesanan | kode_pesanan | id_pembeli | tanggal | total | status |
|---|---|---|---|---|---|
| 3 | INV26111898270 | 3 | 2026-11-19 05:40:27 | 310000 | baru |
| 4 | INV2611180EC65 | 4 | 2026-11-19 05:42:09 | 160000 | baru |

![Gambar 6 — Tabel pesanan](../screenshots/06_phpmyadmin_pesanan.png)

**Tabel `pembeli`** — data pembeli "nana chimi" (id 3 dan 4) tersimpan.

![Gambar 7 — Tabel pembeli](../screenshots/07_phpmyadmin_pembeli.png)

**Tabel `detail_pesanan`** — rincian item pesanan hasil POST:

| id_detail | id_pesanan | id_produk | jumlah | harga_satuan | subtotal |
|---|---|---|---|---|---|
| 3 | 3 | 1 | 2 | 80000 | 160000 |
| 4 | 3 | 3 | 1 | 150000 | 150000 |
| 5 | 4 | 1 | 2 | 80000 | 160000 |

![Gambar 8 — Tabel detail_pesanan](../screenshots/08_phpmyadmin_detail_pesanan.png)

Total pesanan id 3 = 160000 + 150000 = 310000 dan pesanan id 4 = 160000, sesuai kolom `total` pada tabel `pesanan`. Harga satuan diambil server dari tabel `produk`, bukan dari request.

## Kesimpulan
Seluruh endpoint yang diuji (GET produk, GET detail produk, GET kategori, POST pesanan) menghasilkan response JSON dengan status HTTP yang sesuai. Pesanan dari POST tersimpan di tabel `pesanan`, `pembeli`, dan `detail_pesanan`, dan stok produk berkurang sesuai jumlah yang dipesan.
