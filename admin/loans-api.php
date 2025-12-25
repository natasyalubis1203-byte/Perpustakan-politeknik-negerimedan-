<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'requests' => []]);
    exit;
}

$mysqli->set_charset('utf8mb4');

$sql = 'SELECT lr.id, lr.login_number, lr.status, lr.created_at, b.judul, b.penulis
        FROM loan_requests lr
        JOIN books b ON b.id = lr.book_id
        ORDER BY lr.status = "pending" DESC, lr.created_at DESC';
$requests = [];
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $result->free();
}

$mysqli->close();

echo json_encode(['success' => true, 'requests' => $requests]);
?>

