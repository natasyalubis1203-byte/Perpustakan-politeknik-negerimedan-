<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'books' => []]);
    exit;
}

$books = [];
$result = $mysqli->query('SELECT id, judul, penulis, kategori, tahun, stok, cover FROM books ORDER BY created_at DESC');

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $result->free();
}

$mysqli->close();

$placeholders = [
    'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=400&q=60',
    'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=400&q=60',
    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=400&q=60',
    'https://images.unsplash.com/photo-1455885666463-1c1cf57934c6?auto=format&fit=crop&w=400&q=60',
];

echo json_encode(['success' => true, 'books' => $books, 'placeholders' => $placeholders]);
?>

