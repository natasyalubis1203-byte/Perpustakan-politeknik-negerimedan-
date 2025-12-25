<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'books' => [], 'categories' => []]);
    exit;
}

$mysqli->set_charset('utf8mb4');

// Ambil buku terbaru (4 buku terakhir)
$books = [];
$result = $mysqli->query('SELECT id, judul, penulis, kategori, tahun, stok, cover FROM books ORDER BY created_at DESC LIMIT 4');

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $result->free();
}

// Ambil kategori populer berdasarkan jumlah peminjaman
$categories = [];
$sql = '
    SELECT b.kategori, COUNT(lr.id) as jumlah_peminjaman
    FROM loan_requests lr
    JOIN books b ON b.id = lr.book_id
    WHERE lr.status IN ("disetujui", "dikembalikan")
    GROUP BY b.kategori
    ORDER BY jumlah_peminjaman DESC
    LIMIT 4
';
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Hitung total buku di kategori ini
        $countStmt = $mysqli->prepare('SELECT COUNT(*) as total FROM books WHERE kategori = ?');
        $countStmt->bind_param('s', $row['kategori']);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countData = $countResult->fetch_assoc();
        $countStmt->close();
        
        $categories[] = [
            'kategori' => $row['kategori'],
            'jumlah_peminjaman' => (int)$row['jumlah_peminjaman'],
            'total_buku' => (int)$countData['total']
        ];
    }
    $result->free();
}

// Jika tidak ada kategori dari peminjaman, ambil kategori yang ada buku
if (empty($categories)) {
    $result = $mysqli->query('SELECT kategori, COUNT(*) as total FROM books GROUP BY kategori ORDER BY total DESC LIMIT 4');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'kategori' => $row['kategori'],
                'jumlah_peminjaman' => 0,
                'total_buku' => (int)$row['total']
            ];
        }
        $result->free();
    }
}

$placeholders = [
    'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=400&q=60',
    'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=400&q=60',
    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=400&q=60',
    'https://images.unsplash.com/photo-1455885666463-1c1cf57934c6?auto=format&fit=crop&w=400&q=60',
];

$mysqli->close();

echo json_encode([
    'success' => true,
    'books' => $books,
    'categories' => $categories,
    'placeholders' => $placeholders
]);
?>

