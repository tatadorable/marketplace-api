<?php
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'name'    => 'Tokoharu Marketplace REST API',
    'version' => '1.0',
    'endpoints' => [
        ['GET',  '/api/produk.php',              'Daftar produk (q, kategori, page, limit)'],
        ['GET',  '/api/produk.php?id={id}',      'Detail produk'],
        ['GET',  '/api/kategori.php',            'Daftar kategori'],
        ['POST', '/api/pesanan.php',             'Buat pesanan baru (body JSON)'],
        ['GET',  '/api/pesanan.php?kode={kode}', 'Lihat pesanan berdasarkan kode'],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
