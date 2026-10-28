<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

// SUPER ADMIN ONLY
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: admin_dashboard.php?message=Unauthorized");
    exit();
}

$message = '';
$error = '';

// Fetch all students
$students_sql = "SELECT id, username, name, email, points FROM users WHERE role = 'student' ORDER BY username ASC";
$students_result = mysqli_query($link, $students_sql);

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = '00000000';
    
    $sql = "UPDATE users SET password = ? WHERE id = ? AND role = 'student'";
    $stmt = mysqli_prepare($link, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'si', $new_password, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Password reset to 00000000 for user ID: $user_id";
        } else {
            $error = "Failed to reset password.";
        }
    }
}

// Handle Data Wipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_wipe'])) {
    $confirmation = trim($_POST['confirmation']);
    
    if ($confirmation === 'WIPE ALL DATA') {
        mysqli_begin_transaction($link);
        
        try {
            mysqli_query($link, "DELETE FROM scan_history");
            
            $tableCheck = mysqli_query($link, "SHOW TABLES LIKE 'claim_history'");
            if (mysqli_num_rows($tableCheck) > 0) {
                mysqli_query($link, "DELETE FROM claim_history");
            }
            
            mysqli_query($link, "DELETE FROM qr_codes");
            mysqli_query($link, "DELETE FROM users WHERE role = 'student'");
            mysqli_query($link, "UPDATE users SET points = 0 WHERE role IN ('admin', 'super_admin')");
            
            mysqli_commit($link);
            $message = "All data wiped successfully. Student accounts, QR codes, and transaction history have been deleted.";
        } catch (Exception $e) {
            mysqli_rollback($link);
            $error = "Error wiping data: " . $e->getMessage();
        }
    } else {
        $error = "Confirmation text does not match. Data was NOT wiped.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Control Panel - PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-image: url(img/pwu_bg.jpg);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen p-4">

    <div class="max-w-6xl mx-auto">
        
        <div class="flex items-center mb-6">
            <a href="admin_dashboard.php" class="text-gray-500 hover:text-pwu-maroon mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-white">Developer Only • Control Panel </h1>
        </div>

        <?php if ($message): ?>
            <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Reset Student Password Section -->
        <div class="glass-panel rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-yellow-100 p-3 rounded-full mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Reset Student Password</h2>
            </div>
            <p class="text-sm text-gray-600 mb-4">Select a student and reset their password to <span class="font-mono font-bold">00000000</span> (8 zeros).</p>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Select Student</label>
                    <select name="user_id" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">-- Choose a student --</option>
                        <?php while($student = mysqli_fetch_assoc($students_result)): ?>
                            <option value="<?php echo $student['id']; ?>">
                                ID: <?php echo $student['id']; ?> | 
                                <?php echo htmlspecialchars($student['username']); ?>
                                <?php if($student['name']): ?>
                                    (<?php echo htmlspecialchars($student['name']); ?>)
                                <?php endif; ?>
                                - <?php echo $student['points']; ?> pts
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="reset_password" onclick="return confirm('Reset this student\'s password to 00000000?');"
                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                    Reset Password to 00000000
                </button>
            </form>
        </div>

        <!-- Wipe All Data Section -->
        <div class="glass-panel rounded-xl shadow-lg p-6"

        <!-- Wipe All Data Section -->
        <div class="glass-panel rounded-xl shadow-lg p-6">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 p-3 rounded-full mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-red-600">Wipe All Data</h2>
            </div>

        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-red-800">DANGER ZONE</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p class="font-semibold mb-2">This action will permanently delete:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>All student accounts</li>
                            <li>All QR codes (booths)</li>
                            <li>All scan history</li>
                            <li>All claim history</li>
                            <li>Reset admin points to 0</li>
                        </ul>
                        <p class="mt-3 font-bold">THIS CANNOT BE UNDONE!</p>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="" onsubmit="return confirm('Are you absolutely sure? This will delete ALL data!');">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Type <span class="text-red-600 font-mono">WIPE ALL DATA</span> to confirm:
                </label>
                <input type="text" name="confirmation" required autocomplete="off"
                    class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-red-500"
                    placeholder="Type confirmation phrase here">
            </div>

            <div class="flex justify-between items-center">
                <a href="admin_dashboard.php" 
                    class="text-gray-600 hover:text-gray-800 font-semibold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" name="confirm_wipe"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded focus:outline-none focus:shadow-outline transition">
                    Wipe All Data
                </button>
            </div>
        </form>

        <div class="mt-4 text-center text-xs text-gray-500">
            Super Admin Only • All Actions Logged
        </div>
        </div>
    </div>

</body>
</html>
