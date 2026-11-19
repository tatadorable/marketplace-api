<?php
/**
 * Bootstrap bersama seluruh endpoint REST API Marketplace.
 * Header JSON + CORS, koneksi MySQLi, helper response dan pembaca body JSON.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
ini_set('display_errors', '0');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function ok(array $data, int $status = 200, array $extra = []): never
{
    respond($status, array_merge(['status' => 'success', 'code' => $status], $extra, ['data' => $data]));
}

function fail(int $status, string $message, array $errors = []): never
{
    $body = ['status' => 'error', 'code' => $status, 'message' => $message];
    if ($errors) $body['errors'] = $errors;
    respond($status, $body);
}

function allow_methods(array $methods): void
{
    $m = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($m === 'OPTIONS') { http_response_code(204); exit; }
    if (!in_array($m, $methods, true)) {
        header('Allow: ' . implode(', ', $methods));
        fail(405, "Method $m tidak diizinkan. Gunakan: " . implode(', ', $methods) . '.');
    }
}

function json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') fail(400, 'Body request kosong. Kirim data dalam format JSON.');
    $data = json_decode($raw, true);
    if (!is_array($data)) fail(400, 'Body request bukan JSON yang valid.');
    return $data;
}

function id_param(string $name): ?int
{
    if (!isset($_GET[$name])) return null;
    $v = $_GET[$name];
    if (!is_string($v) || !ctype_digit($v) || (int)$v < 1) {
        fail(400, "Parameter '$name' harus berupa bilangan bulat positif.");
    }
    return (int)$v;
}

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    fail(500, 'Server belum dikonfigurasi: salin config/config.example.php menjadi config/config.php.');
}
require $configFile;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('[API] Koneksi DB gagal: ' . $e->getMessage());
    fail(500, 'Koneksi database gagal.');
}

set_exception_handler(function (Throwable $e) {
    error_log('[API] ' . $e->getMessage());
    fail(500, 'Terjadi kesalahan pada server.');
});

function gambar_url(?string $g): ?string
{
    if (!$g || !defined('IMAGE_BASE_URL') || IMAGE_BASE_URL === '') return null;
    return IMAGE_BASE_URL . rawurlencode($g);
}
