<?php
header('Content-Type: application/json');

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $mysqli->connect_error]);
    exit;
}

// Set charset untuk menghindari masalah encoding
$mysqli->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
$loginNumber = isset($_POST['login_number']) ? strtoupper(trim($_POST['login_number'])) : '';

if ($requestId <= 0 || empty($loginNumber)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

// Verifikasi bahwa request ini milik login_number yang bersangkutan
$stmt = $mysqli->prepare('SELECT id, status FROM loan_requests WHERE id = ? AND login_number = ?');
$stmt->bind_param('is', $requestId, $loginNumber);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memverifikasi data.']);
    $stmt->close();
    $mysqli->close();
    exit;
}

$result = $stmt->get_result();
$loan = $result->fetch_assoc();
$stmt->close();

if (!$loan) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Peminjaman tidak ditemukan atau tidak memiliki akses.']);
    $mysqli->close();
    exit;
}

if ($loan['status'] !== 'disetujui') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Hanya peminjaman yang sudah disetujui yang dapat dikembalikan.']);
    $mysqli->close();
    exit;
}

// Update status menjadi dikembalikan
$stmt = $mysqli->prepare('UPDATE loan_requests SET status = "dikembalikan", updated_at = NOW() WHERE id = ?');
$stmt->bind_param('i', $requestId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Buku berhasil dikembalikan.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>

