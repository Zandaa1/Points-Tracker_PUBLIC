<?php
include('session.php');
include('config.php');

// Fetch current points and password from the database
$user_id = $_SESSION['id'];
$sql = "SELECT points, password FROM users WHERE id = '$user_id'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$current_points = $row['points'] ? $row['points'] : 0;

// Check if password is the default 00000000
$needs_password_change = ($row['password'] === '00000000');

// Check if the user is an admin
$is_admin = isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super_admin');
$username = isset($_SESSION['login_user']) ? $_SESSION['login_user'] : 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - PWU Points Tracker</title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="css/animate.min.css">

    <!-- QR Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <style>
        body {
            background-image: url(img/pwu_bg.jpg);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md animate__animated animate__fadeIn">
        

    
        <?php if (isset($_GET['success']) && $_GET['success'] == 'password_changed'): ?>
            <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                <span class="block sm:inline">✅ Password changed successfully!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                <span class="block sm:inline">
                    <?php 
                    switch($_GET['error']) {
                        case 'passwords_dont_match': echo "⚠️ Passwords don't match."; break;
                        case 'password_too_short': echo "⚠️ Password must be at least 6 characters."; break;
                        case 'incorrect_current_password': echo "⚠️ Current password is incorrect."; break;
                        default: echo "⚠️ An error occurred. Please try again.";
                    }
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Logo & Welcome -->
        <div class="text-center mb-8">
            <img class="mx-auto h-20 w-auto mb-4 animate__animated animate__fadeInDown" src="img/pwu_logo.png" alt="PWU Logo">
            <h1 class="text-3xl font-bold text-white mb-1">Hello, <?php echo htmlspecialchars($username); ?>!</h1>
            <p class="text-gray-200 text-sm">Get Points, Get Rewards!</p>
        </div>

<div class="animate__animated animate__fadeIn bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded-lg mb-6 font-sans flex items-start">
    <span class="mr-3 mt-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7 12A1 1 0 004.14 18h15.72a1 1 0 00.85-1.5l-7-12a1 1 0 00-1.72 0z" />
        </svg>
    </span>
    <div class="w-full">
        <p class="font-semibold">The "PWU Sama All" is almost over! Claim your remaining points now!</p>
        <p class="text-sm mt-1">Redemption of points only until <strong>Jan 31, 2026 4:00 PM PHT</strong>.</p>
        <p id="event-countdown" class="text-sm mt-2 font-bold"></p>
        <p id="event-over" class="text-sm mt-2 font-bold hidden">The event is now over. Thank you for participating.</p>
    </div>
</div>

<script>
    (function () {
        const countdownEl = document.getElementById('event-countdown');
        const overEl = document.getElementById('event-over');
        const endTime = new Date('2026-01-31T16:00:00+08:00').getTime();

        function updateCountdown() {
            const now = Date.now();
            const distance = endTime - now;

            if (distance <= 0) {
                countdownEl.classList.add('hidden');
                overEl.classList.remove('hidden');
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownEl.textContent = `Time left: ${days}d ${hours}h ${minutes}m ${seconds}s`;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    })();
</script>

        <?php if ($needs_password_change): ?>
        <!-- Password Change Warning -->
        <div class="bg-pwu-maroon  p-4 mb-6 rounded-lg shadow-lg animate__animated animate__shakeX">
            <div class="flex">
                <div class="flex-shrink-0">
                </div>
                <div class="ml-3">
                    <p class="text-sm font-bold text-white">Security Alert: Change your password immediately!</p>
                    <p class="text-xs text-white mt-1">You have requested to reset your password. Your password has been reset.</p>
                    <a href="#" onclick="openChangePasswordModal(); return false;" class="inline-block mt-2 bg-white text-pwu-maroon text-xs font-semibold py-1 px-3 rounded transition">
                        Change Password Now
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Points Card -->
        <div class="glass-panel rounded-2xl p-8 mb-6 text-center shadow-2xl transform transition hover:scale-105 duration-300">
            <p class="text-gray-200 text-sm uppercase tracking-widest mb-2">Current Balance</p>
            <div class="flex items-center justify-center">
                <span class="text-5xl font-bold text-white drop-shadow-md"><?php echo number_format($current_points); ?></span>
            </div>
            <p class="text-pwu-gold font-semibold mt-2">POINTS</p>
        </div>
        
        <!-- Actions -->
        <div class="space-y-4">
            <button onclick="openMyQR()" class="block w-full bg-pwu-gold hover:bg-yellow-500 text-pwu-maroon font-bold py-4 px-6 rounded-xl shadow-lg text-center transition duration-300 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h-4v-4H8m13-4V7a1 1 0 00-1-1H4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                My QR Code
            </button>

            <a href="scan_qr_code.php" class="block w-full bg-white hover:bg-gray-100 text-pwu-maroon font-bold py-4 px-6 rounded-xl shadow-lg text-center transition duration-300 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Scan Booth QR
            </a>

            <a href="student_history.php" class="block w-full bg-white hover:bg-gray-100 text-pwu-maroon font-bold py-4 px-6 rounded-xl shadow-lg text-center transition duration-300 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                My History
            </a>

            <?php if ($is_admin): ?>
                <a href="admin_dashboard.php" class="block w-full bg-pwu-maroon hover:bg-red-900 text-white font-bold py-4 px-6 rounded-xl shadow-lg text-center transition duration-300 flex items-center justify-center border border-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Admin Dashboard
                </a>
            <?php endif; ?>

            <a href="logout.php" class="block w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg text-center transition duration-300 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </div>

    <!-- My QR Modal -->
    <div id="myQRModal" class="fixed inset-0 bg-black bg-opacity-80 hidden flex items-center justify-center z-50 animate__animated animate__fadeIn">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 text-center relative">
            <button onclick="closeMyQR()" class="absolute top-4 right-4 text-pwu-maroon hover:text-red-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-2xl font-bold text-pwu-maroon mb-2 font-sans">My QR Code</h2>
            <p class="text-gray-600 text-sm mb-6 font-sans">Show this to the claiming booth to claim points.</p>
            
            <div class="flex justify-center mb-6">
                <canvas id="my-qr-canvas"></canvas>
            </div>
            
            <p class="text-xs font-bold text-pwu-maroon">Cant Scan? Manual Input: ID: <?php echo $user_id; ?></p>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="fixed inset-0 bg-black bg-opacity-80 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 relative">
            <button onclick="closeChangePasswordModal()" class="absolute top-4 right-4 text-pwu-maroon hover:text-red-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-2xl font-bold text-pwu-maroon mb-4 font-sans">Change Password</h2>
            
            <form method="POST" action="change_password.php" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 font-sans">Current Password</label>
                    <input type="password" name="current_password" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-sans">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 font-sans">New Password</label>
                    <input type="password" name="new_password" required minlength="6"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-sans">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 font-sans">Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-sans">
                </div>
                <button type="submit" class="w-full bg-pwu-maroon hover:bg-red-900 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition font-sans">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <script>
        function openMyQR() {
            document.getElementById('myQRModal').classList.remove('hidden');
            
            // Generate QR
            const qr = new QRious({
                element: document.getElementById('my-qr-canvas'),
                value: 'STUDENT:<?php echo $user_id; ?>', // Prefix to identify student QR
                size: 200,
                level: 'H'
            });
        }

        function closeMyQR() {
            document.getElementById('myQRModal').classList.add('hidden');
        }

        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.remove('hidden');
        }

        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.add('hidden');
        }
    </script>
</body>
</html>