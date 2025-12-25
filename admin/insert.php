<?php
/**
 * Proses simpan buku baru dari form admin/add-book.html
 */

// Konfigurasi dasar
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';
$UPLOAD_DIR = dirname(__DIR__) . '/uploads';
$UPLOAD_URL_PREFIX = 'uploads/'; // disimpan relatif agar bisa dipakai di HTML

// Fungsi helper sederhana
function field(string $name): string
{
    return isset($_POST[$name]) ? trim($_POST[$name]) : '';
}

// Validasi input dasar
$judul    = field('judul');
$penulis  = field('penulis');
$kategori = field('kategori');
$tahun    = (int) field('tahun');
$stok     = (int) field('stok');

if (!$judul || !$penulis || !$kategori || !$tahun || !$stok) {
    http_response_code(422);
    exit('Input tidak lengkap. Pastikan semua kolom terisi.');
}

// Pastikan direktori upload tersedia
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

$coverPath = null;

if (
    isset($_FILES['cover']) &&
    $_FILES['cover']['error'] === UPLOAD_ERR_OK &&
    is_uploaded_file($_FILES['cover']['tmp_name'])
) {
    $extension = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
    $safeName  = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($_FILES['cover']['name'], PATHINFO_FILENAME));
    $fileName  = time() . '_' . substr($safeName, 0, 40) . ($extension ? '.' . strtolower($extension) : '');
    $target    = $UPLOAD_DIR . '/' . $fileName;

    if (move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
        $coverPath = $UPLOAD_URL_PREFIX . $fileName;
    }
}

// Koneksi ke database
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Koneksi database gagal: ' . $mysqli->connect_error);
}

$stmt = $mysqli->prepare('INSERT INTO books (judul, penulis, kategori, tahun, stok, cover) VALUES (?, ?, ?, ?, ?, ?)');

if (!$stmt) {
    http_response_code(500);
    exit('Gagal menyiapkan query: ' . $mysqli->error);
}

$stmt->bind_param('sssiis', $judul, $penulis, $kategori, $tahun, $stok, $coverPath);

if ($stmt->execute()) {
    header('Location: ./books.html?status=success');
    exit();
}

http_response_code(500);
echo 'Gagal menambah buku: ' . $stmt->error;
