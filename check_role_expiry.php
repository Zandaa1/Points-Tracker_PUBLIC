<?php
// Assumes config.php is already included and $link is available
// Assumes session is started and $_SESSION['id'] is set

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $check_id = $_SESSION['id'];
    // Use prepared statement for security
    $stmt = mysqli_prepare($link, "SELECT role_expiry FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $check_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['role_expiry'] && new DateTime() > new DateTime($row['role_expiry'])) {
            // Expired
            // Demote in DB
            $update_sql = "UPDATE users SET role = 'student', role_expiry = NULL WHERE id = '$check_id'";
            mysqli_query($link, $update_sql);
            
            // Update session
            $_SESSION['role'] = 'student';
            
            // Redirect
            header("Location: student_dashboard.php?message=Admin access expired");
            exit();
        }
    }
    mysqli_stmt_close($stmt);
}
?>