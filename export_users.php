<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$filename = 'pwu_users_' . date('Y-m-d') . '.csv';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Username', 'Name', 'Email', 'Role', 'Points', 'Registered', 'Admin Expiry']);

$sql = "SELECT id, username, name, email, role, points, reg_date, role_expiry FROM users ORDER BY id ASC";
$stmt = mysqli_prepare($link, $sql);

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            $row['username'],
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['role'],
            $row['points'],
            $row['reg_date'],
            $row['role_expiry']
        ]);
    }
}

fclose($output);
exit();
