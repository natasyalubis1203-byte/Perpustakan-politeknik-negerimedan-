<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$bookId = isset($_POST['book_id']) ? (int) $_POST['book_id'] : 0;
$loginNumber = isset($_POST['login_number']) ? strtoupper(trim($_POST['login_number'])) : '';

if ($bookId <= 0 || !$loginNumber) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Data peminjaman tidak lengkap.']);
    exit;
}

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal.']);
    exit;
}

$stmt = $mysqli->prepare('INSERT INTO loan_requests (book_id, login_number) VALUES (?, ?)');

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query.']);
    exit;
}

$stmt->bind_param('is', $bookId, $loginNumber);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Permintaan peminjaman telah diajukan.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan permintaan peminjaman.']);
}

$stmt->close();
$mysqli->close();

