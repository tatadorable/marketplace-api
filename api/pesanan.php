<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';
allow_methods(['GET', 'POST']);

// ---------- GET /api/pesanan.php?kode=INV... -> baca pesanan ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $kode = isset($_GET['kode']) && is_string($_GET['kode']) ? trim($_GET['kode']) : '';
    if ($kode === '') fail(400, "Parameter 'kode' wajib diisi.");

    $stmt = $db->prepare(
        "SELECT ps.id_pesanan, ps.kode_pesanan, ps.tanggal, ps.total, ps.status, pb.nama
         FROM pesanan ps JOIN pembeli pb ON pb.id_pembeli = ps.id_pembeli
         WHERE ps.kode_pesanan = ?"
    );
    $stmt->bind_param('s', $kode);
    $stmt->execute();
    $ps = $stmt->get_result()->fetch_assoc();
    if (!$ps) fail(404, "Pesanan dengan kode $kode tidak ditemukan.");

    $stmt = $db->prepare(
        "SELECT d.id_produk, p.nama_produk, d.jumlah, d.harga_satuan, d.subtotal
         FROM detail_pesanan d JOIN produk p ON p.id_produk = d.id_produk
         WHERE d.id_pesanan = ? ORDER BY d.id_detail"
    );
    $idPesanan = (int)$ps['id_pesanan'];
    $stmt->bind_param('i', $idPesanan);
    $stmt->execute();
    $items = [];
    foreach ($stmt->get_result() as $r) {
        $items[] = [
            'id_produk' => (int)$r['id_produk'], 'nama_produk' => $r['nama_produk'],
            'jumlah' => (int)$r['jumlah'], 'harga_satuan' => (int)$r['harga_satuan'], 'subtotal' => (int)$r['subtotal'],
        ];
    }
    ok([
        'kode_pesanan' => $ps['kode_pesanan'], 'tanggal' => $ps['tanggal'], 'status' => $ps['status'],
        'nama_pembeli' => $ps['nama'], 'items' => $items, 'total' => (int)$ps['total'],
    ]);
}

// ---------- POST /api/pesanan.php -> buat pesanan ----------
/** Hitung karakter UTF-8 tanpa bergantung pada ekstensi mbstring. */
function panjang(string $s): int { return (int)preg_match_all('/./us', $s); }

$in = json_body();
$errors = [];

$pb = $in['pembeli'] ?? null;
if (!is_array($pb)) {
    $errors['pembeli'] = 'Objek pembeli wajib diisi.';
    $pb = [];
}
$nama   = trim((string)($pb['nama'] ?? ''));
$email  = trim((string)($pb['email'] ?? ''));
$telp   = trim((string)($pb['telepon'] ?? ''));
$alamat = trim((string)($pb['alamat'] ?? ''));
if ($nama === '' || panjang($nama) > 100)    $errors['pembeli.nama'] = 'Nama wajib diisi (maks. 100 karakter).';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || panjang($email) > 100) $errors['pembeli.email'] = 'Email tidak valid.';
if ($telp === '' || panjang($telp) > 20)     $errors['pembeli.telepon'] = 'Telepon wajib diisi (maks. 20 karakter).';
if ($alamat === '')                            $errors['pembeli.alamat'] = 'Alamat wajib diisi.';

$cart = []; // id_produk => jumlah (duplikat digabung)
$items = $in['items'] ?? null;
if (!is_array($items) || count($items) === 0) {
    $errors['items'] = 'Minimal 1 item pesanan diperlukan.';
} elseif (count($items) > 50) {
    $errors['items'] = 'Maksimal 50 item per pesanan.';
} else {
    foreach ($items as $i => $it) {
        $idp = is_array($it) ? ($it['id_produk'] ?? null) : null;
        $qty = is_array($it) ? ($it['jumlah'] ?? null) : null;
        if (!is_int($idp) || $idp < 1)               { $errors["items[$i].id_produk"] = 'id_produk harus bilangan bulat positif.'; continue; }
        if (!is_int($qty) || $qty < 1 || $qty > 1000) { $errors["items[$i].jumlah"] = 'jumlah harus bilangan bulat 1 sampai 1000.'; continue; }
        $cart[$idp] = ($cart[$idp] ?? 0) + $qty;
    }
}
if ($errors) fail(422, 'Data pesanan tidak valid.', $errors);

ksort($cart); // urutan tetap -> mencegah deadlock saat lock baris produk

try {
    $db->begin_transaction();

    $st = $db->prepare("INSERT INTO pembeli (nama,email,telepon,alamat) VALUES (?,?,?,?)");
    $st->bind_param('ssss', $nama, $email, $telp, $alamat);
    $st->execute();
    $idPembeli = (int)$db->insert_id;

    $kode = 'INV' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    $nol = 0;
    $st = $db->prepare("INSERT INTO pesanan (kode_pesanan,id_pembeli,total) VALUES (?,?,?)");
    $st->bind_param('sii', $kode, $idPembeli, $nol);
    $st->execute();
    $idPesanan = (int)$db->insert_id;

    $sel = $db->prepare("SELECT nama_produk, harga, stok FROM produk WHERE id_produk=? AND status='aktif' FOR UPDATE");
    $det = $db->prepare("INSERT INTO detail_pesanan (id_pesanan,id_produk,jumlah,harga_satuan,subtotal) VALUES (?,?,?,?,?)");
    $upd = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk=? AND stok >= ?");

    $total = 0; $rincian = [];
    foreach ($cart as $idp => $qty) {
        $sel->bind_param('i', $idp);
        $sel->execute();
        $p = $sel->get_result()->fetch_assoc();
        if (!$p) { $db->rollback(); fail(404, "Produk dengan id $idp tidak ditemukan atau tidak aktif."); }
        if ((int)$p['stok'] < $qty) {
            $db->rollback();
            fail(409, "Stok tidak mencukupi untuk {$p['nama_produk']} (tersedia {$p['stok']}, diminta $qty).");
        }
        $harga = (int)$p['harga']; $sub = $harga * $qty; $total += $sub;
        $det->bind_param('iiiii', $idPesanan, $idp, $qty, $harga, $sub);
        $det->execute();
        $upd->bind_param('iii', $qty, $idp, $qty);
        $upd->execute();
        $rincian[] = ['id_produk' => $idp, 'nama_produk' => $p['nama_produk'], 'jumlah' => $qty, 'harga_satuan' => $harga, 'subtotal' => $sub];
    }

    $fin = $db->prepare("UPDATE pesanan SET total=? WHERE id_pesanan=?");
    $fin->bind_param('ii', $total, $idPesanan);
    $fin->execute();
    $db->commit();
} catch (Throwable $e) {
    try { $db->rollback(); } catch (Throwable $ignored) {}
    throw $e; // ditangani exception handler -> 500 JSON
}

header('Location: pesanan.php?kode=' . urlencode($kode));
ok([
    'kode_pesanan' => $kode, 'status' => 'baru', 'nama_pembeli' => $nama,
    'items' => $rincian, 'total' => $total,
], 201, ['message' => 'Pesanan berhasil dibuat.']);
