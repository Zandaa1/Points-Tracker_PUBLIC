<?php
include('session.php');
include('config.php');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['id'];

// Fetch history (Earned and Claimed)
$sql = "
SELECT 'Earned' as type, q.booth_name COLLATE utf8mb4_general_ci as details, s.points_added as points, s.scan_time as date
FROM scan_history s
JOIN qr_codes q ON s.qr_code_id = q.id
WHERE s.user_id = '$user_id'
UNION ALL
SELECT 'Claimed' as type, c.item_name COLLATE utf8mb4_general_ci as details, c.points, c.claim_date as date
FROM claim_history c
WHERE c.student_id = '$user_id'
ORDER BY date DESC
";

$result = mysqli_query($link, $sql);

function formatDateTime($timestamp) {
    return date('M d, Y h:i A', strtotime($timestamp));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="max-w-md mx-auto p-4">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <a href="student_dashboard.php" class="text-pwu-maroon font-bold flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
            <h1 class="text-xl font-bold text-gray-800">My History</h1>
            <div class="w-10"></div>
        </div>

        <!-- List -->
        <div class="space-y-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="bg-white rounded-xl shadow p-4 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-800"><?php echo htmlspecialchars($row['details']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo formatDateTime($row['date']); ?></p>
                            <span class="text-xs px-2 py-0.5 rounded-full <?php echo $row['type'] == 'Earned' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $row['type']; ?>
                            </span>
                        </div>
                        <span class="font-bold text-lg <?php echo $row['type'] == 'Earned' ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $row['type'] == 'Earned' ? '+' : '-'; ?><?php echo $row['points']; ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-gray-500 py-8">
                    No history found.
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>