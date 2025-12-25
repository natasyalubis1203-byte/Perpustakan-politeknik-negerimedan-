<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./members.html');
    exit;
}

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    die('Koneksi database gagal: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = 'user'; // Default role untuk anggota baru

if (empty($nama) || empty($username) || empty($password)) {
    echo "<script>
            alert('Semua field wajib diisi!');
            window.history.back();
          </script>";
    exit;
}

// Cek apakah username sudah ada
$checkStmt = $mysqli->prepare('SELECT id FROM users WHERE username = ?');
$checkStmt->bind_param('s', $username);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>
            alert('Username sudah digunakan. Silakan pilih username lain.');
            window.history.back();
          </script>";
    $checkStmt->close();
    $mysqli->close();
    exit;
}
$checkStmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert ke database
$stmt = $mysqli->prepare('INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $nama, $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo "<script>
            alert('Anggota berhasil ditambahkan!');
            window.location.href='members.html?status=success';
          </script>";
} else {
    echo "<script>
            alert('Gagal menambahkan anggota: " . addslashes($stmt->error) . "');
            window.history.back();
          </script>";
}

$stmt->close();
$mysqli->close();
?>
