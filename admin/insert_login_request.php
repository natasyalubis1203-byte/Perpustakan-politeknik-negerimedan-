<?php
/**
 * Menyimpan permohonan nomor login dari halaman user/request-login.html
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

function field(string $key): string
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

$nama   = field('nama');
$nim    = field('nim');
$email  = field('email');
$prodi  = field('prodi');
$alasan = field('alasan');

if (!$nama || !$nim || !$email || !$prodi || !$alasan) {
    http_response_code(422);
    exit('Semua kolom wajib diisi.');
}

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Koneksi database gagal: ' . $mysqli->connect_error);
}

$stmt = $mysqli->prepare('INSERT INTO login_requests (nama, nim, email, prodi, alasan) VALUES (?, ?, ?, ?, ?)');

if (!$stmt) {
    http_response_code(500);
    exit('Gagal menyiapkan query: ' . $mysqli->error);
}

$stmt->bind_param('sssss', $nama, $nim, $email, $prodi, $alasan);

if ($stmt->execute()) {
    header('Location: ../user/request-login.html?status=success');
    exit();
}

http_response_code(500);
echo 'Gagal mengirim permohonan: ' . $stmt->error;

