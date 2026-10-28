<?php
include('session.php');
include('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: student_dashboard.php");
    exit();
}

$user_id = $_SESSION['id'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Validate inputs
if ($new_password !== $confirm_password) {
    header("Location: student_dashboard.php?error=passwords_dont_match");
    exit();
}

if (strlen($new_password) < 6) {
    header("Location: student_dashboard.php?error=password_too_short");
    exit();
}

// Fetch current password hash
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {
    header("Location: student_dashboard.php?error=db_error");
    exit();
}

mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Verify current password
if ($current_password !== $user['password']) {
    header("Location: student_dashboard.php?error=incorrect_current_password");
    exit();
}

// Update password
$update_sql = "UPDATE users SET password = ? WHERE id = ?";
$update_stmt = mysqli_prepare($link, $update_sql);

if (!$update_stmt) {
    header("Location: student_dashboard.php?error=db_error");
    exit();
}

mysqli_stmt_bind_param($update_stmt, 'si', $new_password, $user_id);

if (mysqli_stmt_execute($update_stmt)) {
    header("Location: student_dashboard.php?success=password_changed");
} else {
    header("Location: student_dashboard.php?error=update_failed");
}

exit();
