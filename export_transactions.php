<?php
ob_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_export_errors.log');

include('session.php');
include('config.php');
include('check_role_expiry.php');

if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filename = 'pwu_transactions_' . date('Y-m-d') . '.csv';

if (headers_sent()) {
    error_log('Export failed: headers already sent.');
    http_response_code(500);
    exit();
}

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

if (ob_get_length()) {
    ob_clean();
}

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

fputcsv($output, ['Date', 'Type', 'Username', 'Name', 'Email', 'Details', 'Points']);

$hasClaimHistory = false;
$checkSql = "SHOW TABLES LIKE 'claim_history'";
$checkResult = mysqli_query($link, $checkSql);
if ($checkResult) {
    $hasClaimHistory = mysqli_num_rows($checkResult) > 0;
}

if ($search !== '') {
    $sql = "
        SELECT 'Earned' COLLATE utf8mb4_general_ci as type,
               CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci as username,
               CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci as name,
               CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci as email,
               CONVERT(q.booth_name USING utf8mb4) COLLATE utf8mb4_general_ci as details,
               s.points_added as points,
               s.scan_time as date
        FROM scan_history s
        JOIN users u ON s.user_id = u.id
        JOIN qr_codes q ON s.qr_code_id = q.id
        WHERE CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
           OR CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
           OR CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
           OR CONVERT(q.booth_name USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
        ORDER BY date DESC
    ";

    if ($hasClaimHistory) {
        $sql = "
            SELECT 'Earned' COLLATE utf8mb4_general_ci as type,
                   CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci as username,
                   CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci as name,
                   CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci as email,
                   CONVERT(q.booth_name USING utf8mb4) COLLATE utf8mb4_general_ci as details,
                   s.points_added as points,
                   s.scan_time as date
            FROM scan_history s
            JOIN users u ON s.user_id = u.id
            JOIN qr_codes q ON s.qr_code_id = q.id
            WHERE CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
               OR CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
               OR CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
               OR CONVERT(q.booth_name USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
            UNION ALL
            SELECT 'Claimed' COLLATE utf8mb4_general_ci as type,
                   CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci as username,
                   CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci as name,
                   CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci as email,
                   CONVERT(c.item_name USING utf8mb4) COLLATE utf8mb4_general_ci as details,
                   c.points as points,
                   c.claim_date as date
            FROM claim_history c
            JOIN users u ON c.student_id = u.id
            WHERE CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
               OR CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
               OR CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
               OR CONVERT(c.item_name USING utf8mb4) COLLATE utf8mb4_general_ci LIKE ?
            ORDER BY date DESC
        ";
    }

    $stmt = mysqli_prepare($link, $sql);

    if ($stmt) {
        $term = '%' . $search . '%';
        if ($hasClaimHistory) {
            mysqli_stmt_bind_param($stmt, 'ssssssss', $term, $term, $term, $term, $term, $term, $term, $term);
        } else {
            mysqli_stmt_bind_param($stmt, 'ssss', $term, $term, $term, $term);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        error_log('Export failed (search): ' . mysqli_error($link));
        http_response_code(500);
        exit();
    }
} else {
    $sql = "
        SELECT 'Earned' COLLATE utf8mb4_general_ci as type,
               CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci as username,
               CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci as name,
               CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci as email,
               CONVERT(q.booth_name USING utf8mb4) COLLATE utf8mb4_general_ci as details,
               s.points_added as points,
               s.scan_time as date
        FROM scan_history s
        JOIN users u ON s.user_id = u.id
        JOIN qr_codes q ON s.qr_code_id = q.id
        ORDER BY date DESC
    ";

    if ($hasClaimHistory) {
        $sql = "
            SELECT 'Earned' COLLATE utf8mb4_general_ci as type,
                   CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci as username,
                   CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci as name,
                   CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci as email,
                   CONVERT(q.booth_name USING utf8mb4) COLLATE utf8mb4_general_ci as details,
                   s.points_added as points,
                   s.scan_time as date
            FROM scan_history s
            JOIN users u ON s.user_id = u.id
            JOIN qr_codes q ON s.qr_code_id = q.id
            UNION ALL
            SELECT 'Claimed' COLLATE utf8mb4_general_ci as type,
                   CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci as username,
                   CONVERT(u.name USING utf8mb4) COLLATE utf8mb4_general_ci as name,
                   CONVERT(u.email USING utf8mb4) COLLATE utf8mb4_general_ci as email,
                   CONVERT(c.item_name USING utf8mb4) COLLATE utf8mb4_general_ci as details,
                   c.points as points,
                   c.claim_date as date
            FROM claim_history c
            JOIN users u ON c.student_id = u.id
            ORDER BY date DESC
        ";
    }

    $stmt = mysqli_prepare($link, $sql);

    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        error_log('Export failed: ' . mysqli_error($link));
        http_response_code(500);
        exit();
    }
}

if (isset($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['date'],
            $row['type'],
            $row['username'],
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['details'],
            $row['points']
        ]);
    }
}

fclose($output);
exit();
