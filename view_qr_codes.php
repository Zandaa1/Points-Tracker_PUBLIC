<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

// Fetch all QR codes from the database
$sql = "SELECT q.*, u.username as creator_name FROM qr_codes q LEFT JOIN users u ON q.created_by = u.id ORDER BY q.created_at DESC";
$result = mysqli_query($link, $sql);

// Format datetime for display
function formatDateTime($timestamp) {
    return date('M d, Y h:i A', strtotime($timestamp));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes Overview - PWU Points Tracker</title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <!-- QR Library for rendering -->
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
<body class="min-h-screen p-4">

    <div class="max-w-7xl mx-auto glass-panel rounded-2xl shadow-2xl p-6">
        
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center">
                <a href="admin_dashboard.php" class="mr-4 text-gray-500 hover:text-pwu-maroon transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-pwu-maroon">QR Codes Overview</h1>
            </div>
            <a href="create_qr_code.php" class="bg-pwu-maroon hover:bg-red-900 text-white font-bold py-2 px-4 rounded shadow transition duration-300">
                + Create New
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden shadow-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">QR Preview</th>
                        <th class="py-3 px-6 text-left">Details</th>
                        <th class="py-3 px-6 text-center">Points</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Created By</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $is_expired = strtotime($row['expiration_time']) < time();
                        ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">
                                    <div class="flex items-center">
                                        <canvas class="qr-mini" data-value="<?php echo $row['unique_text']; ?>" width="50" height="50"></canvas>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800"><?php echo htmlspecialchars($row['booth_name']); ?></span>
                                        <span class="text-xs text-gray-500">Code: <?php echo htmlspecialchars($row['unique_text']); ?></span>
                                        <span class="text-xs text-gray-500 italic"><?php echo htmlspecialchars($row['description']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span class="bg-yellow-100 text-yellow-700 py-1 px-3 rounded-full text-xs font-bold">
                                        <?php echo $row['points']; ?> pts
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <?php if ($is_expired): ?>
                                        <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs">Expired</span>
                                        <div class="text-xs mt-1 text-gray-400"><?php echo formatDateTime($row['expiration_time']); ?></div>
                                    <?php else: ?>
                                        <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Active</span>
                                        <div class="text-xs mt-1 text-gray-400">Expires: <?php echo formatDateTime($row['expiration_time']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span class="font-medium"><?php echo htmlspecialchars($row['creator_name'] ?? 'Unknown'); ?></span>
                                    <div class="text-xs text-gray-400"><?php echo formatDateTime($row['created_at']); ?></div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-3 px-6 text-center">No QR codes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Render mini QR codes
        document.querySelectorAll('.qr-mini').forEach(canvas => {
            new QRious({
                element: canvas,
                value: canvas.dataset.value,
                size: 50
            });
        });
    </script>
</body>
</html>

    <script src="https://cdn.jsdelivr.net/npm/qr-creator/dist/qr-creator.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get all QR code data from the table
            const qrData = [
                <?php 
                // Reset the result pointer to the beginning
                mysqli_data_seek($result, 0);
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                {
                    id: 'qr-<?php echo $row['id']; ?>',
                    text: '<?php echo addslashes($row['unique_text']); ?>'
                },
                <?php endwhile; ?>
            ];
            
            // Generate all QR codes
            qrData.forEach(function(qr) {
                const container = document.getElementById(qr.id);
                if (container) {
                    QrCreator.render({
                        text: qr.text,
                        radius: 0,
                        ecLevel: "L",
                        background: "#fff",
                        size: 120
                    }, container);
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
