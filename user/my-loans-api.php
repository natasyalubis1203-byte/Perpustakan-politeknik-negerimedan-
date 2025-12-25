<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$loginNumber = isset($_GET['login']) ? strtoupper(trim($_GET['login'])) : '';

$response = ['success' => false, 'loans' => []];

if (empty($loginNumber)) {
    echo json_encode($response);
    exit;
}

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode($response);
    exit;
}

$mysqli->set_charset('utf8mb4');

$loans = [];
$stmt = $mysqli->prepare('SELECT lr.*, b.judul, b.penulis FROM loan_requests lr JOIN books b ON b.id = lr.book_id WHERE lr.login_number = ? ORDER BY lr.created_at DESC');
$stmt->bind_param('s', $loginNumber);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    $result->free();
}

$stmt->close();
$mysqli->close();

$response = ['success' => true, 'loans' => $loans];
echo json_encode($response);
?>

