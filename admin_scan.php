<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$scanned_student_id = '';
$student_name = '';
$student_points = 0;
$error = '';
$success = '';

// Handle manual ID entry or pre-filled from scan
if (isset($_GET['student_id'])) {
    $scanned_student_id = mysqli_real_escape_string($link, $_GET['student_id']);

    $sql = "SELECT username, points, role FROM users WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $scanned_student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $student = mysqli_fetch_assoc($result);
            if ($student['role'] !== 'student') {
                $error = "This QR belongs to an admin account. Please scan a Student My QR Code for claiming.";
            } else {
                $student_name = $student['username'];
                $student_points = $student['points'];
            }
        } else {
            $error = "Student not found.";
        }
    } else {
        $error = "Unable to fetch student details.";
    }
}

// Handle Claim Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['claim_item'])) {
    $student_id = mysqli_real_escape_string($link, $_POST['student_id']);
    $item_name = mysqli_real_escape_string($link, $_POST['item_name']);
    $points_cost = intval($_POST['points_cost']);
    $admin_id = $_SESSION['id'];

    // Verify student and points again
    $sql = "SELECT points FROM users WHERE id = '$student_id'";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['points'] >= $points_cost) {
        // Deduct points
        $new_points = $row['points'] - $points_cost;
        $update_sql = "UPDATE users SET points = '$new_points' WHERE id = '$student_id'";
        mysqli_query($link, $update_sql);
        
        // Log transaction
        $log_sql = "INSERT INTO claim_history (student_id, item_name, points, processed_by) VALUES ('$student_id', '$item_name', '$points_cost', '$admin_id')";
        mysqli_query($link, $log_sql);
        
        $success = "Claim successful! Deducted $points_cost points.";
        // Reset form
        $scanned_student_id = '';
        $student_name = '';
    } else {
        $error = "Insufficient points. Student has " . $row['points'] . " points.";
        $scanned_student_id = $student_id; // Keep ID to retry
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Scanner - PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="js/html5-qrcode.min.js"></script>
    <style>
        /* Scanner Customization */
        #reader {
            background-color: white;
        }
        #reader button {
            background-color: #800000 !important; /* PWU Maroon */
            color: white !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 4px !important;
            margin-top: 10px !important;
            cursor: pointer !important;
        }
        #reader select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        #reader__dashboard_section_swaplink {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="max-w-md mx-auto p-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <a href="admin_dashboard.php" class="text-pwu-maroon font-bold flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
            <h1 class="text-xl font-bold text-gray-800">Scan Student QR</h1>
            <div class="w-10"></div>
        </div>

        <?php if ($success): ?>
            <div class="bg-pwu-maroon border-l-4 border-white text-white p-4 mb-4 font-sans" role="alert">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-pwu-maroon border-l-4 border-white text-white p-4 mb-4 font-sans" role="alert">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <div id="scanError" class="bg-pwu-maroon border-l-4 border-white text-white p-4 mb-4 hidden font-sans" role="alert">
            <p id="scanErrorText"></p>
        </div>

        <!-- Scanner Section -->
        <?php if (empty($scanned_student_id)): ?>
            <div class="bg-white rounded-xl shadow-md mb-6 min-h-[300px]">
                <div id="reader" class="w-full"></div>
                <div class="p-4 text-center text-gray-500 text-sm">
                    Scan a student's QR code to process a claim.
                </div>
            </div>

            <!-- Manual ID Entry -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 text-center">Or Enter Student ID Manually</h3>
                <form method="GET" action="admin_scan.php" class="flex gap-2">
                    <input type="number" name="student_id" required min="1"
                        class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-pwu-maroon"
                        placeholder="Enter Student ID">
                    <button type="submit"
                        class="bg-pwu-maroon hover:bg-red-900 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition">
                        Lookup
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Claim Form Section -->
        <?php if (!empty($scanned_student_id) && !empty($student_name)): ?>
            <div class="bg-white rounded-xl shadow-md p-6 animate__animated animate__fadeInUp">
                <h2 class="text-lg font-bold text-pwu-maroon mb-4 border-b pb-2">Process Claim</h2>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500">Student</p>
                    <p class="text-xl font-bold"><?php echo htmlspecialchars($student_name); ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-semibold">ID:</span> <?php echo htmlspecialchars($scanned_student_id); ?> | 
                        <span class="font-semibold">Balance:</span> <span class="text-pwu-maroon font-bold"><?php echo $student_points; ?></span> pts
                    </p>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="student_id" value="<?php echo $scanned_student_id; ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="item_name">Item Name</label>
                        <input type="text" id="item_name" name="item_name" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="e.g., T-Shirt, Sticker">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="points_cost">Points Cost</label>
                        <input type="number" id="points_cost" name="points_cost" required min="1" max="<?php echo $student_points; ?>"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Points to deduct">
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="admin_scan.php" class="text-gray-500 hover:text-gray-700 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </a>
                        <button type="submit" name="claim_item"
                            class="bg-pwu-maroon hover:bg-red-900 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline shadow-lg transition">
                            Confirm Claim
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>

    <script>
        <?php if (empty($scanned_student_id)): ?>
        let scanLocked = false;

        function showScanError(message) {
            const box = document.getElementById('scanError');
            const text = document.getElementById('scanErrorText');
            text.textContent = message;
            box.classList.remove('hidden');
            setTimeout(() => box.classList.add('hidden'), 4000);
        }

        async function checkAdminQr(decodedText) {
            const response = await fetch('api/check_qr_code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: decodedText })
            });

            if (!response.ok) {
                return { success: false };
            }

            return response.json();
        }

        async function onScanSuccess(decodedText, decodedResult) {
            if (scanLocked) return;
            scanLocked = true;

            // Expected format: "STUDENT:123"
            if (decodedText.startsWith('STUDENT:')) {
                const id = decodedText.split(':')[1];
                window.location.href = `admin_scan.php?student_id=${id}`;
                return;
            }

            // Fallback: numeric student ID
            if (!isNaN(decodedText)) {
                window.location.href = `admin_scan.php?student_id=${decodedText}`;
                return;
            }

            try {
                const result = await checkAdminQr(decodedText);
                if (result.success && result.exists) {
                    showScanError('This QR belongs to an admin account. Please scan a Student My QR Code for claiming.');
                } else {
                    showScanError('Invalid QR code. Please scan a Student QR.');
                }
            } catch (err) {
                showScanError('Unable to validate QR. Please try again.');
            } finally {
                setTimeout(() => { scanLocked = false; }, 2000);
            }
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
        <?php endif; ?>
    </script>
</body>
</html>