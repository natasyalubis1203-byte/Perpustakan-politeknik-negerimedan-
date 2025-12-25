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

$requests = [];
$result = $mysqli->query('SELECT * FROM login_requests ORDER BY status = "pending" DESC, created_at DESC');

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $result->free();
}

$mysqli->close();

echo json_encode(['success' => true, 'requests' => $requests]);
?>

