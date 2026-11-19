<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
allow_methods(['GET']);

function format_produk(array $r): array
{
    return [
        'id_produk'   => (int)$r['id_produk'],
        'nama_produk' => $r['nama_produk'],
        'deskripsi'   => $r['deskripsi'],
        'harga'       => (int)$r['harga'],
        'stok'        => (int)$r['stok'],
        'gambar'      => $r['gambar'],
        'gambar_url'  => gambar_url($r['gambar']),
        'kategori'    => ['id_kategori' => (int)$r['id_kategori'], 'nama_kategori' => $r['nama_kategori']],
        'penjual'     => ['id_penjual' => (int)$r['id_penjual'], 'nama_toko' => $r['nama_toko']],
        'created_at'  => $r['created_at'],
    ];
}

$baseSelect =
    "SELECT p.id_produk, p.nama_produk, p.deskripsi, p.harga, p.stok, p.gambar, p.created_at,
            k.id_kategori, k.nama_kategori, j.id_penjual, j.nama_toko
     FROM produk p
     JOIN kategori k ON k.id_kategori = p.id_kategori
     JOIN penjual  j ON j.id_penjual  = p.id_penjual";

// GET /api/produk.php?id=1 -> detail
$id = id_param('id');
if ($id !== null) {
    $stmt = $db->prepare("$baseSelect WHERE p.id_produk = ? AND p.status = 'aktif'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) fail(404, "Produk dengan id $id tidak ditemukan.");
    ok(format_produk($row));
}

// GET /api/produk.php -> daftar (filter + paginasi)
$where = ["p.status = 'aktif'"]; $types = ''; $params = [];
if (isset($_GET['kategori'])) {
    $where[] = 'p.id_kategori = ?'; $types .= 'i'; $params[] = id_param('kategori');
}
if (isset($_GET['q']) && is_string($_GET['q']) && trim($_GET['q']) !== '') {
    $like = '%' . trim($_GET['q']) . '%';
    $where[] = '(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)';
    $types .= 'ss'; $params[] = $like; $params[] = $like;
}
$limit = 20; $page = 1;
if (isset($_GET['limit'])) {
    $v = (string)$_GET['limit'];
    if (!ctype_digit($v) || (int)$v < 1 || (int)$v > 100) fail(400, "Parameter 'limit' harus 1 sampai 100.");
    $limit = (int)$v;
}
if (isset($_GET['page'])) {
    $v = (string)$_GET['page'];
    if (!ctype_digit($v) || (int)$v < 1) fail(400, "Parameter 'page' harus bilangan bulat positif.");
    $page = (int)$v;
}
$offset   = ($page - 1) * $limit;
$whereSql = 'WHERE ' . implode(' AND ', $where);

$stmt = $db->prepare("SELECT COUNT(*) AS n FROM produk p $whereSql");
if ($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['n'];

$stmt = $db->prepare("$baseSelect $whereSql ORDER BY p.id_produk ASC LIMIT ? OFFSET ?");
$stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
$stmt->execute();
$res = $stmt->get_result(); $data = [];
while ($r = $res->fetch_assoc()) $data[] = format_produk($r);

ok($data, 200, ['total' => $total, 'page' => $page, 'limit' => $limit, 'total_pages' => (int)ceil($total / $limit)]);
