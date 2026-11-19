# Laporan Hasil Pengujian REST API — Tokoharu Marketplace

**Lingkungan:** PHP 8 + MySQL/MariaDB, `http://localhost/marketplace-api/api` · **Database:** `marketplace` (`database/marketplace.sql`)
**Metode:** request dikirim ke API yang berjalan (bukan JSON buatan); status, header, dan isi response di bawah adalah output asli.

## Ringkasan Hasil
| No | Skenario | Request | Hasil diharapkan | Hasil |
|----|----------|---------|------------------|-------|
| 1 | Semua produk | `GET /produk.php` | 200 + daftar | Lulus |
| 2 | Detail produk | `GET /produk.php?id=1` | 200 + detail | Lulus |
| 3 | Daftar kategori | `GET /kategori.php` | 200 + daftar | Lulus |
| 4 | Buat pesanan | `POST /pesanan.php` | 201, data tersimpan, stok berkurang | Lulus |
| 5 | Baca pesanan | `GET /pesanan.php?kode=...` | 200 | Lulus |
| 6 | Produk tidak ada | `GET /produk.php?id=999` | 404 | Lulus |
| 7 | ID tidak valid | `GET /produk.php?id=abc` | 400 | Lulus |
| 8 | Method salah | `DELETE /produk.php` | 405 + header `Allow` | Lulus |
| 9 | JSON rusak | `POST /pesanan.php` | 400 | Lulus |
| 10 | Validasi gagal | `POST /pesanan.php` | 422 + rincian error | Lulus |
| 11 | Stok kurang | `POST /pesanan.php` | 409 + rollback | Lulus |

## Daftar Screenshot yang Harus Dilampirkan (simpan di folder `screenshots/`)
| File | Isi |
|------|-----|
| `01_browser_produk.png` | Browser: `http://localhost/marketplace-api/api/produk.php` (URL + JSON terlihat) |
| `02_browser_detail.png` | Browser: `.../produk.php?id=1` |
| `03_browser_kategori.png` | Browser: `.../kategori.php` |
| `04_postman_get_produk.png` | Postman: request GET + response 200 |
| `05_postman_get_detail.png` | Postman: GET detail + response |
| `06_postman_post_pesanan.png` | Postman: tab Body (JSON) + response 201 |
| `07_postman_error_404.png` | Postman: response 404 / 409 / 422 |
| `08_phpmyadmin_pesanan.png` | phpMyAdmin: tabel `pesanan`, `pembeli`, `detail_pesanan` berisi data hasil POST |
| `09_phpmyadmin_stok.png` | phpMyAdmin: stok produk berkurang |
| `10_xampp_running.png` | XAMPP Control Panel: Apache & MySQL *Running* |

## Output Pengujian
### Uji 1 — GET semua produk
```
$ curl -i http://localhost/marketplace-api/api/produk.php?limit=2

HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "success",
    "code": 200,
    "total": 5,
    "page": 1,
    "limit": 2,
    "total_pages": 3,
    "data": [
        {
            "id_produk": 1,
            "nama_produk": "Kopi Kepahiang 250g",
            "deskripsi": "Kopi robusta khas Bengkulu, sangrai medium.",
            "harga": 45000,
            "stok": 50,
            "gambar": null,
            "gambar_url": null,
            "kategori": {
                "id_kategori": 1,
                "nama_kategori": "Makanan"
            },
            "penjual": {
                "id_penjual": 1,
                "nama_toko": "Toko Bengkulu"
            },
            "created_at": "2026-11-18 21:46:48"
        },
        {
            "id_produk": 2,
            "nama_produk": "Pendap Instan",
            "deskripsi": "Olahan ikan khas Bengkulu dalam kemasan siap saji.",
            "harga": 30000,
            "stok": 30,
            "gambar": null,
            "gambar_url": null,
            "kategori": {
                "id_kategori": 1,
                "nama_kategori": "Makanan"
            },
            "penjual": {
                "id_penjual": 1,
                "nama_toko": "Toko Bengkulu"
            },
            "created_at": "2026-11-18 21:46:48"
        }
    ]
}

```

### Uji 2 — GET detail produk (id=1)
```
$ curl -i http://localhost/marketplace-api/api/produk.php?id=1

HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "success",
    "code": 200,
    "data": {
        "id_produk": 1,
        "nama_produk": "Kopi Kepahiang 250g",
        "deskripsi": "Kopi robusta khas Bengkulu, sangrai medium.",
        "harga": 45000,
        "stok": 50,
        "gambar": null,
        "gambar_url": null,
        "kategori": {
            "id_kategori": 1,
            "nama_kategori": "Makanan"
        },
        "penjual": {
            "id_penjual": 1,
            "nama_toko": "Toko Bengkulu"
        },
        "created_at": "2026-11-18 21:46:48"
    }
}

```

### Uji 3 — GET kategori
```
$ curl -i http://localhost/marketplace-api/api/kategori.php

HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "success",
    "code": 200,
    "total": 4,
    "data": [
        {
            "id_kategori": 1,
            "nama_kategori": "Makanan",
            "jumlah_produk": 2
        },
        {
            "id_kategori": 2,
            "nama_kategori": "Pakaian",
            "jumlah_produk": 1
        },
        {
            "id_kategori": 3,
            "nama_kategori": "Skincare",
            "jumlah_produk": 1
        },
        {
            "id_kategori": 4,
            "nama_kategori": "Figure",
            "jumlah_produk": 1
        }
    ]
}

```

### Stok sebelum pesanan
```sql
SELECT id_produk, nama_produk, stok FROM produk WHERE id_produk IN (1,3);
```
```
+-----------+---------------------+------+
| id_produk | nama_produk         | stok |
+-----------+---------------------+------+
|         1 | Kopi Kepahiang 250g |   50 |
|         3 | Kaos Daily Pastel   |   40 |
+-----------+---------------------+------+
```

### Uji 4 — POST pesanan (berhasil, 201)
```
$ curl -i -X POST http://localhost/marketplace-api/api/pesanan.php -H Content-Type: application/json -d {"pembeli":{"nama":"Budi Santoso","email":"budi@example.com","telepon":"081234567890","alamat":"Jl. Merdeka No. 10, Bengkulu"},"items":[{"id_produk":1,"jumlah":2},{"id_produk":3,"jumlah":1}]}

HTTP/1.1 201 Created
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff
Location: pesanan.php?kode=INV261118E5FBA

{
    "status": "success",
    "code": 201,
    "message": "Pesanan berhasil dibuat.",
    "data": {
        "kode_pesanan": "INV261118E5FBA",
        "status": "baru",
        "nama_pembeli": "Budi Santoso",
        "items": [
            {
                "id_produk": 1,
                "nama_produk": "Kopi Kepahiang 250g",
                "jumlah": 2,
                "harga_satuan": 45000,
                "subtotal": 90000
            },
            {
                "id_produk": 3,
                "nama_produk": "Kaos Daily Pastel",
                "jumlah": 1,
                "harga_satuan": 85000,
                "subtotal": 85000
            }
        ],
        "total": 175000
    }
}

```

### Uji 5 — GET pesanan berdasarkan kode
```
$ curl -i http://localhost/marketplace-api/api/pesanan.php?kode=INV261118E5FBA

HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "success",
    "code": 200,
    "data": {
        "kode_pesanan": "INV261118E5FBA",
        "tanggal": "2026-11-18 21:46:48",
        "status": "baru",
        "nama_pembeli": "Budi Santoso",
        "items": [
            {
                "id_produk": 1,
                "nama_produk": "Kopi Kepahiang 250g",
                "jumlah": 2,
                "harga_satuan": 45000,
                "subtotal": 90000
            },
            {
                "id_produk": 3,
                "nama_produk": "Kaos Daily Pastel",
                "jumlah": 1,
                "harga_satuan": 85000,
                "subtotal": 85000
            }
        ],
        "total": 175000
    }
}

```

### Data tersimpan di MySQL
```sql
SELECT p.kode_pesanan, b.nama, p.total, p.status FROM pesanan p JOIN pembeli b USING(id_pembeli); SELECT id_pesanan, id_produk, jumlah, harga_satuan, subtotal FROM detail_pesanan; SELECT id_produk, nama_produk, stok FROM produk WHERE id_produk IN (1,3);
```
```
+----------------+--------------+--------+--------+
| kode_pesanan   | nama         | total  | status |
+----------------+--------------+--------+--------+
| INV261118E5FBA | Budi Santoso | 175000 | baru   |
+----------------+--------------+--------+--------+
+------------+-----------+--------+--------------+----------+
| id_pesanan | id_produk | jumlah | harga_satuan | subtotal |
+------------+-----------+--------+--------------+----------+
|          1 |         1 |      2 |        45000 |    90000 |
|          1 |         3 |      1 |        85000 |    85000 |
+------------+-----------+--------+--------------+----------+
+-----------+---------------------+------+
| id_produk | nama_produk         | stok |
+-----------+---------------------+------+
|         1 | Kopi Kepahiang 250g |   48 |
|         3 | Kaos Daily Pastel   |   39 |
+-----------+---------------------+------+
```

### Uji 6 — Produk tidak ditemukan (404)
```
$ curl -i http://localhost/marketplace-api/api/produk.php?id=999

HTTP/1.1 404 Not Found
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "error",
    "code": 404,
    "message": "Produk dengan id 999 tidak ditemukan."
}

```

### Uji 7 — Parameter id tidak valid (400)
```
$ curl -i http://localhost/marketplace-api/api/produk.php?id=abc

HTTP/1.1 400 Bad Request
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "error",
    "code": 400,
    "message": "Parameter 'id' harus berupa bilangan bulat positif."
}

```

### Uji 8 — Method tidak diizinkan (405)
```
$ curl -i -X DELETE http://localhost/marketplace-api/api/produk.php

HTTP/1.1 405 Method Not Allowed
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff
Allow: GET

{
    "status": "error",
    "code": 405,
    "message": "Method DELETE tidak diizinkan. Gunakan: GET."
}

```

### Uji 9 — JSON tidak valid (400)
```
$ curl -i -X POST http://localhost/marketplace-api/api/pesanan.php -H Content-Type: application/json -d {bad

HTTP/1.1 400 Bad Request
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "error",
    "code": 400,
    "message": "Body request bukan JSON yang valid."
}

```

### Uji 10 — Validasi gagal (422)
```
$ curl -i -X POST http://localhost/marketplace-api/api/pesanan.php -H Content-Type: application/json -d {"pembeli":{"nama":"","email":"x"},"items":[{"id_produk":1,"jumlah":0}]}

HTTP/1.1 422 Unknown Status Code
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "error",
    "code": 422,
    "message": "Data pesanan tidak valid.",
    "errors": {
        "pembeli.nama": "Nama wajib diisi (maks. 100 karakter).",
        "pembeli.email": "Email tidak valid.",
        "pembeli.telepon": "Telepon wajib diisi (maks. 20 karakter).",
        "pembeli.alamat": "Alamat wajib diisi.",
        "items[0].jumlah": "jumlah harus bilangan bulat 1 sampai 1000."
    }
}

```

### Uji 11 — Stok tidak cukup (409), transaksi di-rollback
```
$ curl -i -X POST http://localhost/marketplace-api/api/pesanan.php -H Content-Type: application/json -d {"pembeli":{"nama":"Rina","email":"rina@example.com","telepon":"08123","alamat":"Bengkulu"},"items":[{"id_produk":2,"jumlah":1},{"id_produk":5,"jumlah":999}]}

HTTP/1.1 409 Conflict
Content-Type: application/json; charset=utf-8
X-Content-Type-Options: nosniff

{
    "status": "error",
    "code": 409,
    "message": "Stok tidak mencukupi untuk Mini Character Figure (tersedia 12, diminta 999)."
}

```

### Bukti rollback (pesanan tetap 1, stok produk 2 tetap 30)
```sql
SELECT (SELECT COUNT(*) FROM pesanan) AS jumlah_pesanan, (SELECT COUNT(*) FROM pembeli) AS jumlah_pembeli, (SELECT stok FROM produk WHERE id_produk=2) AS stok_produk_2;
```
```
+----------------+----------------+---------------+
| jumlah_pesanan | jumlah_pembeli | stok_produk_2 |
+----------------+----------------+---------------+
|              1 |              1 |            30 |
+----------------+----------------+---------------+
```

