<?php
header('Content-Type: application/json');
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_pnm';

$loginNumber = isset($_GET['login']) ? strtoupper(trim($_GET['login'])) : '';

$response = [
    'success' => false,
    'stats' => [
        'buku_dipinjam' => 0,
        'jatuh_tempo_terdekat' => null,
        'riwayat_selesai' => 0,
        'hari_menuju_tempo' => null
    ],
    'activeLoans' => [],
    'historyLoans' => []
];

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

// Ambil data peminjaman aktif (disetujui)
$stmt = $mysqli->prepare('
    SELECT lr.id, lr.status, lr.created_at, lr.updated_at, b.judul, b.penulis
    FROM loan_requests lr
    JOIN books b ON b.id = lr.book_id
    WHERE lr.login_number = ? AND lr.status = "disetujui"
    ORDER BY lr.created_at DESC
');
$stmt->bind_param('s', $loginNumber);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['activeLoans'][] = $row;
}
$stmt->close();

// Ambil data riwayat (dikembalikan)
$stmt = $mysqli->prepare('
    SELECT lr.id, lr.status, lr.created_at, lr.updated_at, b.judul, b.penulis
    FROM loan_requests lr
    JOIN books b ON b.id = lr.book_id
    WHERE lr.login_number = ? AND lr.status = "dikembalikan"
    ORDER BY lr.updated_at DESC
    LIMIT 5
');
$stmt->bind_param('s', $loginNumber);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['historyLoans'][] = $row;
}
$stmt->close();

// Hitung statistik
$response['stats']['buku_dipinjam'] = count($response['activeLoans']);
$response['stats']['riwayat_selesai'] = count($response['historyLoans']);

// Hitung jatuh tempo terdekat (asumsi 14 hari dari tanggal pinjam)
if (!empty($response['activeLoans'])) {
    $nearestDue = null;
    $nearestDays = null;
    foreach ($response['activeLoans'] as $loan) {
        $pinjamDate = new DateTime($loan['created_at']);
        $dueDate = clone $pinjamDate;
        $dueDate->modify('+14 days');
        $now = new DateTime();
        $diff = $now->diff($dueDate);
        $daysLeft = $diff->invert ? -$diff->days : $diff->days;

        if ($nearestDue === null || $daysLeft < $nearestDays) {
            $nearestDue = $dueDate;
            $nearestDays = $daysLeft;
        }
    }
    $response['stats']['jatuh_tempo_terdekat'] = $nearestDue ? $nearestDue->format('Y-m-d') : null;
    $response['stats']['hari_menuju_tempo'] = $nearestDays;
}

$response['success'] = true;
$mysqli->close();

echo json_encode($response);
?>

