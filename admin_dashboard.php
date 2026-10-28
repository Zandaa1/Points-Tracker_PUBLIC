<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$user_id = $_SESSION['id'];
$username = isset($_SESSION['login_user']) ? $_SESSION['login_user'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="css/animate.min.css">

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
        
        <div class="animate__animated animate__fadeIn bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded-lg mb-6 font-sans flex items-start">
            <span class="mr-3 mt-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7 12A1 1 0 004.14 18h15.72a1 1 0 00.85-1.5l-7-12a1 1 0 00-1.72 0z" />
            </svg>
            </span>
            <div>
                Last update: 1:58 PM PST <br>
            - We are working on resolving the date being saved which is delayed by 16 hours 'USA Los Angeles Timezone'. Keep in mind the timezone difference.
            </div>
        </div>
        
        <!-- Header -->
        <div class="glass-panel rounded-2xl shadow-lg p-6 mb-8 flex flex-col md:flex-row items-center justify-between animate__animated animate__fadeInDown">
            <div class="flex items-center mb-4 md:mb-0">
                <img src="img/pwu_logo.png" alt="PWU Logo" class="h-12 w-12 mr-4">
                <div>
                    <h1 class="text-2xl font-bold text-pwu-maroon">Admin Dashboard</h1>
                    <p class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></p>
                </div>
            </div>
            <a href="logout.php" class="bg-red-100 text-red-700 hover:bg-red-200 px-4 py-2 rounded-lg transition duration-300 font-semibold">
                Logout
            </a>
        </div>

        <!-- Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate__animated animate__fadeInUp">
            
            <!-- Scan Student QR (Claim) -->
            <a href="admin_scan.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group border-l-4 border-pwu-gold">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h-4v-4H8m13-4V7a1 1 0 00-1-1H4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-pwu-maroon transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Scan Student QR</h3>
                <p class="text-gray-600 text-sm">Scan student codes to deduct points for rewards.</p>
            </a>

            <!-- Transactions -->
            <a href="admin_transactions.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-pwu-maroon transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Transactions</h3>
                <p class="text-gray-600 text-sm">View history of points earned and items claimed.</p>
            </a>

            <!-- Create QR -->
            <a href="create_qr_code.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-pwu-maroon transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Create QR Code</h3>
                <p class="text-gray-600 text-sm">Generate new points QR codes for booths and events.</p>
            </a>

            <!-- View QR Codes -->
            <a href="view_qr_codes.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h-4v-4H8m13-4V7a1 1 0 00-1-1H4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-pwu-maroon transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Manage QR Codes</h3>
                <p class="text-gray-600 text-sm">View active codes, expiration status, and scan stats.</p>
            </a>

            <!-- View Users -->
            <a href="view_users.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-pwu-maroon transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Manage Users</h3>
                <p class="text-gray-600 text-sm">View registered students, points, and account details.</p>
            </a>

            <!-- View Student Dashboard -->
            <a href="student_dashboard.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-pwu-maroon transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">View Student Dashboard</h3>
                <p class="text-gray-600 text-sm">Access the student view to check points or test features.</p>
            </a>

            <?php if ($_SESSION['role'] == 'super_admin'): ?>
            <!-- Wipe All Data (Super Admin Only) -->
            <a href="wipe_data.php" class="glass-panel rounded-xl p-6 shadow-md hover:shadow-xl transition duration-300 transform hover:-translate-y-1 group border-l-4 border-red-600">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <span class="text-gray-400 group-hover:text-red-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-red-600 mb-2">Super Admin</h3>
                <p class="text-gray-600 text-sm">Change student password. Delete all student accounts, QR codes, and transaction history.</p>
            </a>
            <?php endif; ?>

        </div>

        <!-- Recent Activity (Placeholder for now, could be a real query) -->
        <div class="glass-panel rounded-2xl shadow-lg p-6 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <h2 class="text-xl font-bold text-pwu-maroon mb-4">Quick Stats</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-gray-500 text-xs uppercase">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?php 
                        $result = mysqli_query($link, "SELECT COUNT(*) as count FROM users WHERE role='student'");
                        $row = mysqli_fetch_assoc($result);
                        echo $row['count'];
                        ?>
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-gray-500 text-xs uppercase">Active QR Codes</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?php 
                        $result = mysqli_query($link, "SELECT COUNT(*) as count FROM qr_codes WHERE expiration_time > NOW()");
                        $row = mysqli_fetch_assoc($result);
                        echo $row['count'];
                        ?>
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-gray-500 text-xs uppercase">Total Scans</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?php 
                        $result = mysqli_query($link, "SELECT COUNT(*) as count FROM scan_history");
                        $row = mysqli_fetch_assoc($result);
                        echo $row['count'];
                        ?>
                    </p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-gray-500 text-xs uppercase">Points Distributed</p>
                    <p class="text-2xl font-bold text-pwu-maroon">
                        <?php 
                        $result = mysqli_query($link, "SELECT SUM(points_added) as total FROM scan_history WHERE points_added > 0");
                        $row = mysqli_fetch_assoc($result);
                        echo number_format($row['total'] ?? 0);
                        ?>
                    </p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
