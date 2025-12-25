<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}

$mysqli->set_charset('utf8mb4');

$requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
$status = $_POST['status'] ?? 'pending';
$loginNumber = trim($_POST['login_number'] ?? '');

if ($requestId > 0 && in_array($status, ['pending', 'diterima', 'ditolak'], true)) {
    $stmt = $mysqli->prepare('UPDATE login_requests SET status = ?, login_number = ?, updated_at = NOW() WHERE id = ?');
    $loginNumber = $loginNumber !== '' ? strtoupper($loginNumber) : null;
    $stmt->bind_param('ssi', $status, $loginNumber, $requestId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Permohonan berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui permohonan: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Data pembaruan tidak valid.']);
}

$mysqli->close();
?>

