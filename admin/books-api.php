<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'books' => [], 'message' => 'Koneksi database gagal']);
    exit;
}

$mysqli->set_charset('utf8mb4');

$books = [];
$result = $mysqli->query('SELECT id, judul, penulis, kategori, tahun, stok, cover, created_at FROM books ORDER BY created_at DESC');

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $result->free();
}

$mysqli->close();

echo json_encode(['success' => true, 'books' => $books]);
?>

