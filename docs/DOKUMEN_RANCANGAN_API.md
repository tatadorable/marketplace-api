# Dokumen Rancangan REST API — Marketplace

## A. Analisis dan Perancangan API

### a. Fungsi Web Service/API
Web Service/API adalah antarmuka yang membuat data dan proses bisnis marketplace dapat diakses aplikasi lain lewat HTTP dengan format standar (JSON), tanpa mengakses database atau halaman HTML secara langsung.

Fungsi pada marketplace:
- **Penyedia data**: katalog produk, kategori, dan status pesanan dapat dibaca aplikasi mobile atau situs mitra.
- **Penerima transaksi**: aplikasi lain dapat mengirim pesanan melalui `POST`.
- **Lapisan pengaman**: aplikasi klien tidak pernah menyentuh database; validasi, aturan stok, dan transaksi tetap dijalankan di server.

Mengapa API diperlukan untuk komunikasi antar-aplikasi:
1. **Interoperabilitas** — aplikasi Android/iOS, web lain, atau sistem pihak ketiga memakai bahasa/platform berbeda; HTTP + JSON dimengerti semuanya.
2. **Pemisahan tanggung jawab** — logika dan database dibuat sekali di server, banyak klien memakainya.
3. **Keamanan** — kredensial database tidak keluar dari server; akses dibatasi hanya pada endpoint yang disediakan.
4. **Konsistensi data** — semua klien membaca sumber data yang sama sehingga stok dan harga selalu seragam.
5. **Kemudahan pengembangan** — tampilan dapat diubah tanpa mengubah backend, dan sebaliknya.

### b. Rancangan Endpoint
Base URL (XAMPP): `http://localhost/marketplace-api/api`

| No | Method & Endpoint | Fungsi | Parameter / Body | Status |
|----|-------------------|--------|------------------|--------|
| 1 | `GET /api/produk.php` | Menampilkan seluruh produk aktif | opsional: `q`, `kategori`, `page`, `limit` | 200, 400 |
| 2 | `GET /api/produk.php?id=1` | Menampilkan detail produk | `id` (integer > 0) | 200, 400, 404 |
| 3 | `GET /api/kategori.php` | Menampilkan daftar kategori + jumlah produk | opsional: `id` | 200, 400, 404 |
| 4 | `POST /api/pesanan.php` | Membuat pesanan baru | Body JSON: `pembeli{nama,email,telepon,alamat}`, `items[{id_produk,jumlah}]` | 201, 400, 404, 409, 422 |
| 5 | `GET /api/pesanan.php?kode=INV...` | Menampilkan pesanan berdasarkan kode | `kode` | 200, 400, 404 |

Kode status:

| Kode | Arti | Contoh kasus |
|------|------|--------------|
| 200 | OK | GET berhasil |
| 201 | Created | Pesanan berhasil dibuat |
| 400 | Bad Request | `id=abc`, JSON rusak, body kosong |
| 404 | Not Found | Produk/kategori/pesanan tidak ada |
| 405 | Method Not Allowed | `DELETE /produk.php` |
| 409 | Conflict | Stok tidak mencukupi |
| 422 | Unprocessable Entity | Validasi data pesanan gagal |
| 500 | Server Error | Koneksi database gagal (pesan internal tidak dibocorkan) |

Aturan bisnis `POST /api/pesanan.php`: harga diambil dari database (bukan dari klien), stok dikunci (`FOR UPDATE`) dan dikurangi, seluruh proses dalam satu transaksi (gagal salah satu → rollback total), item duplikat digabung.

### c. Struktur JSON
Contoh memakai data asli dari database `marketplace` yang dipakai pengujian.

**Response daftar produk** — `GET /api/produk.php` (item pertama dari 11 produk)
```json
{
  "status": "success",
  "code": 200,
  "total": 11,
  "page": 1,
  "limit": 20,
  "total_pages": 1,
  "data": [
    {
      "id_produk": 1,
      "nama_produk": "leather jacket",
      "deskripsi": "",
      "harga": 80000,
      "stok": 50,
      "gambar": "96b1d317b9c136b8.jpg",
      "gambar_url": "http://localhost/marketplace/uploads/96b1d317b9c136b8.jpg",
      "kategori": { "id_kategori": 3, "nama_kategori": "Pakaian" },
      "penjual": { "id_penjual": 1, "nama_toko": "juju fashion" },
      "created_at": "2026-11-18 23:54:36"
    }
  ]
}
```

**Response detail produk** — `GET /api/produk.php?id=1`
```json
{
  "status": "success",
  "code": 200,
  "data": {
    "id_produk": 1,
    "nama_produk": "leather jacket",
    "deskripsi": "",
    "harga": 80000,
    "stok": 50,
    "gambar": "96b1d317b9c136b8.jpg",
    "gambar_url": "http://localhost/marketplace/uploads/96b1d317b9c136b8.jpg",
    "kategori": { "id_kategori": 3, "nama_kategori": "Pakaian" },
    "penjual": { "id_penjual": 1, "nama_toko": "juju fashion" },
    "created_at": "2026-11-18 23:54:36"
  }
}
```

**Request `POST /api/pesanan.php`**
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

**Response error** (format seragam)
```json
{ "status": "error", "code": 404, "message": "Produk dengan id 999 tidak ditemukan." }
```
