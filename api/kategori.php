<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
allow_methods(['GET']);

$sql = "SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) AS jumlah_produk
        FROM kategori k
        LEFT JOIN produk p ON p.id_kategori = k.id_kategori AND p.status = 'aktif'";
$group = " GROUP BY k.id_kategori, k.nama_kategori";

function fmt_kategori(array $r): array
{
    return [
        'id_kategori'   => (int)$r['id_kategori'],
        'nama_kategori' => $r['nama_kategori'],
        'jumlah_produk' => (int)$r['jumlah_produk'],
    ];
}

$id = id_param('id');
if ($id !== null) {
    $stmt = $db->prepare("$sql WHERE k.id_kategori = ?$group");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) fail(404, "Kategori dengan id $id tidak ditemukan.");
    ok(fmt_kategori($row));
}

$res  = $db->query("$sql$group ORDER BY k.id_kategori");
$data = [];
while ($r = $res->fetch_assoc()) $data[] = fmt_kategori($r);
ok($data, 200, ['total' => count($data)]);
