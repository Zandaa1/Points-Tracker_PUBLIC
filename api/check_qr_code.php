<?php
header('Content-Type: application/json');
include('../session.php');
include('../config.php');
include('../check_role_expiry.php');

if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$code = isset($input['code']) ? trim($input['code']) : '';

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'No code provided']);
    exit();
}

$sql = "SELECT id FROM qr_codes WHERE unique_text = ? LIMIT 1";
$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query error']);
    exit();
}

mysqli_stmt_bind_param($stmt, 's', $code);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$exists = mysqli_stmt_num_rows($stmt) > 0;

echo json_encode(['success' => true, 'exists' => $exists]);
exit();
