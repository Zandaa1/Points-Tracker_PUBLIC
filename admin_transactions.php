<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($link, $_GET['search']) : '';

// Build Query
$where_earn = "";
$where_claim = "";

if ($search) {
    $where_earn = "WHERE u.username LIKE '%$search%' OR q.booth_name LIKE '%$search%'";
    $where_claim = "WHERE u.username LIKE '%$search%' OR c.item_name LIKE '%$search%'";
}

$sql = "
SELECT 'Earned' as type, u.username, q.booth_name COLLATE utf8mb4_general_ci as details, s.points_added as points, s.scan_time as date
FROM scan_history s
JOIN users u ON s.user_id = u.id
JOIN qr_codes q ON s.qr_code_id = q.id
$where_earn
UNION ALL
SELECT 'Claimed' as type, u.username, c.item_name COLLATE utf8mb4_general_ci as details, c.points, c.claim_date as date
FROM claim_history c
JOIN users u ON c.student_id = u.id
$where_claim
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
    <title>Transactions - PWU Points Tracker</title>
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <link rel="stylesheet" href="css/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <div class="flex items-center mb-4 md:mb-0">
                <a href="admin_dashboard.php" class="text-pwu-maroon hover:text-red-700 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">Transaction History</h1>
            </div>
            
            <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
                <a href="export_transactions.php<?php echo $search ? ('?search=' . urlencode($search)) : ''; ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-pwu-maroon text-white font-semibold hover:bg-red-900 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                    </svg>
                    Export Transactions
                </a>

                <!-- Search -->
                <form method="GET" class="w-full md:w-72">
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                            class="w-full pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:border-pwu-maroon" 
                            placeholder="Search username, booth, or item...">
                        <div class="absolute top-0 left-0 pt-2 pl-3 text-gray-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Student
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Details
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Points
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap"><?php echo formatDateTime($row['date']); ?></p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <div class="flex items-center">
                                            <div class="ml-3">
                                                <p class="text-gray-900 whitespace-no-wrap font-semibold">
                                                    <?php echo htmlspecialchars($row['username']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                        <?php if($row['type'] == 'Earned'): ?>
                                            <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                                <span aria-hidden="true" class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                                <span class="relative">Earned</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                                <span aria-hidden="true" class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                                <span class="relative">Claimed</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap"><?php echo htmlspecialchars($row['details']); ?></p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                                        <span class="font-bold <?php echo $row['type'] == 'Earned' ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $row['type'] == 'Earned' ? '+' : '-'; ?><?php echo $row['points']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    No transactions found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>