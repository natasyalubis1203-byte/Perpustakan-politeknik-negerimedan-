<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}

$mysqli->set_charset('utf8mb4');

// Get current month and last month
$currentMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));

// 1. Peminjaman Bulanan (bulan ini)
$stmt = $mysqli->prepare('SELECT COUNT(*) as total FROM loan_requests WHERE DATE_FORMAT(created_at, "%Y-%m") = ?');
$stmt->bind_param('s', $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
$currentMonthLoans = $result->fetch_assoc()['total'];
$stmt->close();

// Peminjaman bulan lalu
$stmt = $mysqli->prepare('SELECT COUNT(*) as total FROM loan_requests WHERE DATE_FORMAT(created_at, "%Y-%m") = ?');
$stmt->bind_param('s', $lastMonth);
$stmt->execute();
$result = $stmt->get_result();
$lastMonthLoans = $result->fetch_assoc()['total'];
$stmt->close();

// Hitung persentase perubahan
$percentageChange = 0;
if ($lastMonthLoans > 0) {
    $percentageChange = round((($currentMonthLoans - $lastMonthLoans) / $lastMonthLoans) * 100);
} elseif ($currentMonthLoans > 0) {
    $percentageChange = 100; // Naik dari 0
}

// 2. Buku Terpopuler (buku yang paling banyak dipinjam bulan ini)
$stmt = $mysqli->prepare('
    SELECT b.judul, COUNT(lr.id) as jumlah_peminjaman
    FROM loan_requests lr
    JOIN books b ON b.id = lr.book_id
    WHERE DATE_FORMAT(lr.created_at, "%Y-%m") = ?
    GROUP BY lr.book_id, b.judul
    ORDER BY jumlah_peminjaman DESC
    LIMIT 1
');
$stmt->bind_param('s', $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
$popularBook = $result->fetch_assoc();
$stmt->close();

// 3. Anggota Aktif (yang melakukan minimal 1 peminjaman bulan ini)
$stmt = $mysqli->prepare('
    SELECT COUNT(DISTINCT login_number) as total
    FROM loan_requests
    WHERE DATE_FORMAT(created_at, "%Y-%m") = ?
');
$stmt->bind_param('s', $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
$activeMembers = $result->fetch_assoc()['total'];
$stmt->close();

// 4. Anggota Baru (user yang dibuat bulan ini)
$stmt = $mysqli->prepare('
    SELECT COUNT(*) as total
    FROM users
    WHERE role = "user" AND DATE_FORMAT(created_at, "%Y-%m") = ?
');
$stmt->bind_param('s', $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
$newMembers = $result->fetch_assoc()['total'];
$stmt->close();

// Format bulan Indonesia
$bulanIndo = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$currentMonthName = $bulanIndo[date('F')] . ' ' . date('Y');

// 5. Detail untuk tabel
$details = [
    [
        'jenis' => 'Peminjaman',
        'deskripsi' => 'Bulan ' . $currentMonthName,
        'total' => $currentMonthLoans . ' transaksi'
    ],
    [
        'jenis' => 'Buku Terpopuler',
        'deskripsi' => $popularBook ? $popularBook['judul'] : 'Belum ada data',
        'total' => $popularBook ? $popularBook['jumlah_peminjaman'] . ' peminjaman' : '0 peminjaman'
    ],
    [
        'jenis' => 'Anggota Aktif',
        'deskripsi' => 'Melakukan min. 1 peminjaman',
        'total' => $activeMembers . ' anggota'
    ]
];

$mysqli->close();

echo json_encode([
    'success' => true,
    'stats' => [
        'peminjaman_bulanan' => [
            'total' => $currentMonthLoans,
            'percentage_change' => $percentageChange,
            'is_increase' => $percentageChange >= 0
        ],
        'buku_terpopuler' => [
            'judul' => $popularBook ? $popularBook['judul'] : 'Belum ada data',
            'jumlah' => $popularBook ? $popularBook['jumlah_peminjaman'] : 0
        ],
        'anggota_aktif' => [
            'total' => $activeMembers,
            'anggota_baru' => $newMembers
        ]
    ],
    'details' => $details,
    'current_month' => $currentMonthName
]);
?>

