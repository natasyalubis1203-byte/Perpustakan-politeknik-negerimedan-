<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'members' => []]);
    exit;
}

$mysqli->set_charset('utf8mb4');

$members = [];
$result = $mysqli->query('SELECT id, nama, username, role, created_at FROM users WHERE role = "user" ORDER BY created_at DESC');

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    $result->free();
}

$mysqli->close();

echo json_encode(['success' => true, 'members' => $members]);
?>

