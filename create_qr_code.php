<?php
include('session.php');
include("config.php");
include('check_role_expiry.php');

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$error = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $unique_text = mysqli_real_escape_string($link, $_POST['unique_text']);
    $booth_name = mysqli_real_escape_string($link, $_POST['booth_name']);
    $description = mysqli_real_escape_string($link, $_POST['description']);
    $points = mysqli_real_escape_string($link, $_POST['points']);
    $expiration_time = mysqli_real_escape_string($link, $_POST['expiration_time']);
    $created_by = $_SESSION['id']; // Assuming session has user ID

    // Check for duplicate unique_text
    $check_sql = "SELECT * FROM qr_codes WHERE unique_text = '$unique_text'";
    $result = mysqli_query($link, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $error = "Error: A QR code with this unique text already exists.";
    } else {
        // Insert the new QR code into the database
        $sql = "INSERT INTO qr_codes (unique_text, booth_name, description, points, expiration_time, created_by) VALUES ('$unique_text', '$booth_name', '$description', '$points', '$expiration_time', '$created_by')";
        if (mysqli_query($link, $sql)) {
            $success_message = "QR Code created successfully!";
        } else {
            $error = "Error: " . $sql . "<br>" . mysqli_error($link);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create QR Code - PWU Points Tracker</title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-4xl glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <!-- Form Section -->
        <div class="w-full md:w-1/2 p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex items-center mb-6">
                <a href="student_dashboard.php" class="text-pwu-maroon hover:text-red-700 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-pwu-maroon">Create QR Code</h1>
            </div>

            <?php if ($error): ?>
                <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="qrForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="unique_text">Unique Code</label>
                    <div class="flex">
                        <input type="text" id="unique_text" name="unique_text" required
                            class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Enter or generate code">
                        <button type="button" onclick="generateRandomCode()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-r focus:outline-none focus:shadow-outline">
                            Auto
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="booth_name">Booth Name</label>
                    <input type="text" id="booth_name" name="booth_name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="e.g., IT Booth">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                    <textarea id="description" name="description" rows="2"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Short description"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="points">Points</label>
                        <input type="number" id="points" name="points" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="e.g., 50">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="expiration_time">Expiration</label>
                        <input type="datetime-local" id="expiration_time" name="expiration_time" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> Download the QR code image first before saving to database!
                            </p>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-pwu-maroon hover:bg-red-900 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                    Save to Database
                </button>
            </form>
        </div>

        <!-- Preview Section -->
        <div class="w-full md:w-1/2 p-8 bg-gray-50 flex flex-col items-center justify-center">
            <h2 class="text-xl font-bold text-gray-700 mb-6">Preview</h2>
            
            <div class="bg-white p-4 rounded-xl shadow-lg mb-6">
                <canvas id="qr-canvas" class="mx-auto"></canvas>
            </div>

            <button id="downloadBtn" onclick="downloadQR()" disabled
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg flex items-center transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Image
            </button>
            <p class="text-xs text-gray-500 mt-2">Includes Booth Name</p>
        </div>
    </div>

    <script>
        const qrCanvas = document.getElementById('qr-canvas');
        const uniqueTextInput = document.getElementById('unique_text');
        const boothNameInput = document.getElementById('booth_name');
        const downloadBtn = document.getElementById('downloadBtn');
        
        let qr;

        function generateRandomCode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            uniqueTextInput.value = result;
            updatePreview();
        }

        function updatePreview() {
            const text = uniqueTextInput.value;
            const boothName = boothNameInput.value || 'PWU Points';

            if (!text) {
                downloadBtn.disabled = true;
                // Clear canvas
                const ctx = qrCanvas.getContext('2d');
                ctx.clearRect(0, 0, qrCanvas.width, qrCanvas.height);
                return;
            }

            downloadBtn.disabled = false;

            // 1. Generate QR on a temporary canvas
            const tempCanvas = document.createElement('canvas');
            const qrSize = 250;
            const padding = 20;
            const textHeight = 40;
            const totalWidth = qrSize + (padding * 2);
            const totalHeight = qrSize + (padding * 2) + textHeight;

            const qr = new QRious({
                element: tempCanvas,
                value: text,
                size: qrSize,
                level: 'H'
            });

            // 2. Draw Composite Image on Main Canvas
            qrCanvas.width = totalWidth;
            qrCanvas.height = totalHeight;
            const ctx = qrCanvas.getContext('2d');

            // Background
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, totalWidth, totalHeight);

            // Draw QR
            ctx.drawImage(tempCanvas, padding, padding);

            // Draw Text
            ctx.font = 'bold 20px Montserrat, sans-serif';
            ctx.fillStyle = '#800000'; // PWU Maroon
            ctx.textAlign = 'center';
            ctx.fillText(boothName, totalWidth / 2, totalHeight - 15);
        }

        function downloadQR() {
            const link = document.createElement('a');
            link.download = `PWU-QR-${uniqueTextInput.value}.png`;
            link.href = qrCanvas.toDataURL();
            link.click();
        }

        // Event Listeners
        uniqueTextInput.addEventListener('input', updatePreview);
        boothNameInput.addEventListener('input', updatePreview);

        // Initialize
        // generateRandomCode(); // Optional: Auto-generate on load
    </script>
</body>
</html>
