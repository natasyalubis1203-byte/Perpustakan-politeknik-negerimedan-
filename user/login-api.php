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
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $mysqli->connect_error]);
    exit;
}

$mysqli->set_charset('utf8mb4');

$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi']);
    exit;
}

// Cek apakah email ada di login_requests yang sudah diterima
$stmt = $mysqli->prepare('SELECT login_number, nama, email FROM login_requests WHERE LOWER(TRIM(email)) = ? AND status = "diterima" LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$loginRequest = $result->fetch_assoc();
$stmt->close();

if (!$loginRequest) {
    echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar atau belum diterima oleh admin. Pastikan email sudah diajukan dan diterima oleh admin.']);
    $mysqli->close();
    exit;
}

if (empty($loginRequest['login_number'])) {
    echo json_encode(['success' => false, 'message' => 'Email sudah diterima tapi nomor login belum diberikan. Silakan hubungi admin.']);
    $mysqli->close();
    exit;
}

$loginNumber = trim($loginRequest['login_number']);

// Cek apakah ada user dengan username = login_number
$stmt = $mysqli->prepare('SELECT id, nama, username, password, role FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $loginNumber);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Jika user belum ada, buat user baru dengan password yang diberikan
if (!$user) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, "user")');
    $stmt->bind_param('sss', $loginRequest['nama'], $loginNumber, $hashedPassword);
    
    if ($stmt->execute()) {
        $userId = $mysqli->insert_id;
        // Ambil user yang baru dibuat
        $stmt2 = $mysqli->prepare('SELECT id, nama, username, password, role FROM users WHERE id = ? LIMIT 1');
        $stmt2->bind_param('i', $userId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $user = $result2->fetch_assoc();
        $stmt2->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat akun: ' . $mysqli->error]);
        $stmt->close();
        $mysqli->close();
        exit;
    }
    $stmt->close();
}

// Verifikasi password
if (!$user || !isset($user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Data user tidak valid. Silakan hubungi admin.']);
    $mysqli->close();
    exit;
}

$passwordMatch = false;

// Cek apakah password adalah hash (dimulai dengan $2y$ atau $2a$ atau $2b$)
if (preg_match('/^\$2[ayb]\$/', $user['password'])) {
    // Password sudah di-hash, verifikasi dengan password_verify
    $passwordMatch = password_verify($password, $user['password']);
    
    // Jika tidak cocok, mungkin password di database perlu di-update
    if (!$passwordMatch) {
        // Coba update password dengan yang baru (untuk reset password)
        // Tapi kita tidak akan auto-update, biarkan user tahu password salah
    }
} else {
    // Password masih plain text (untuk migrasi dari sistem lama)
    $passwordMatch = ($user['password'] === $password);
    
    // Jika cocok, update ke hashed password
    if ($passwordMatch) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hashedPassword, $user['id']);
        $stmt->execute();
        $stmt->close();
    }
}

if ($passwordMatch) {
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => [
            'id' => $user['id'],
            'nama' => $user['nama'],
            'username' => $user['username'],
            'email' => $email,
            'role' => $user['role']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Password salah. Jika ini pertama kali login setelah akun diterima, password akan di-set dengan password yang Anda masukkan. Pastikan password yang Anda masukkan benar.']);
}

$mysqli->close();
?>
