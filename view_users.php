<?php
include('session.php');
include('config.php');
include('check_role_expiry.php');

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: student_dashboard.php?message=Unauthorized access");
    exit();
}

$isSuperAdmin = $_SESSION['role'] == 'super_admin';

// Handle Actions (Super Admin Only)
if ($isSuperAdmin && isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($link, $_GET['id']);
    $action = $_GET['action'];
    
    if ($target_id != $_SESSION['id']) { // Prevent self-modification
        if ($action == 'promote') {
            $sql = "UPDATE users SET role = 'admin' WHERE id = '$target_id'";
            if (mysqli_query($link, $sql)) {
                $message = "User promoted to Admin successfully.";
            } else {
                $error = "Error promoting user.";
            }
        } elseif ($action == 'demote') {
            $sql = "UPDATE users SET role = 'student', role_expiry = NULL WHERE id = '$target_id'";
            if (mysqli_query($link, $sql)) {
                $message = "User demoted to Student successfully.";
            } else {
                $error = "Error demoting user.";
            }
        }
    }
}

// Handle Expiry Update (Super Admin Only)
if ($isSuperAdmin && isset($_POST['update_expiry'])) {
    $target_id = mysqli_real_escape_string($link, $_POST['user_id']);
    $expiry_date = mysqli_real_escape_string($link, $_POST['expiry_date']);
    
    if ($target_id != $_SESSION['id']) {
        $sql = "UPDATE users SET role_expiry = '$expiry_date' WHERE id = '$target_id'";
        if (mysqli_query($link, $sql)) {
            $message = "Admin access expiry updated.";
        } else {
            $error = "Error updating expiry.";
        }
    }
}

// Handle user deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($link, $_GET['delete']);
    
    // Don't allow deleting self
    if ($delete_id != $_SESSION['id']) {
        // First delete any scan history entries
        $delete_history = "DELETE FROM scan_history WHERE user_id = '$delete_id'";
        mysqli_query($link, $delete_history);
        
        // Then delete the user
        $delete_user = "DELETE FROM users WHERE id = '$delete_id'";
        if (mysqli_query($link, $delete_user)) {
            $message = "User deleted successfully";
        } else {
            $error = "Failed to delete user";
        }
    } else {
        $error = "You cannot delete your own account";
    }
}

// Fetch all users from the database
$sql = "SELECT id, username, role, points, reg_date, role_expiry, name, email FROM users ORDER BY id ASC";
$result = mysqli_query($link, $sql);

// Format datetime for display
function formatDateTime($timestamp) {
    return $timestamp ? date('M d, Y h:i A', strtotime($timestamp)) : 'N/A';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="img/pwu_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Overview - PWU Points Tracker</title>
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="css/output.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
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
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div class="flex items-center">
                <a href="admin_dashboard.php" class="mr-4 text-gray-500 hover:text-pwu-maroon transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-pwu-maroon">Users Overview</h1>
            </div>
            <div>
                <a href="export_users.php" class="inline-flex items-center px-4 py-2 rounded-lg bg-pwu-maroon text-white font-semibold hover:bg-red-900 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                    </svg>
                    Export Users
                </a>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-pwu-maroon border border-pwu-maroon text-white px-4 py-3 rounded relative mb-4 font-sans" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden shadow-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Username</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-center">Role</th>
                        <th class="py-3 px-6 text-center">Points</th>
                        <th class="py-3 px-6 text-center">Registered</th>
                        <?php if ($isSuperAdmin): ?>
                            <th class="py-3 px-6 text-center">Admin Expiry</th>
                        <?php endif; ?>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $isAdmin = $row['role'] == 'admin';
                            $isSuperAdminUser = $row['role'] == 'super_admin';
                            $rowClass = $isSuperAdminUser ? 'bg-purple-50' : ($isAdmin ? 'bg-red-50' : '');
                            $isSelf = $row['id'] == $_SESSION['id'];
                        ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100 <?php echo $rowClass; ?>">
                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                    <span class="font-medium"><?php echo $row['id']; ?></span>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <div class="flex items-center">
                                        <span class="font-medium"><?php echo htmlspecialchars($row['username']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <?php echo htmlspecialchars($row['name'] ?? 'Not set'); ?>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <?php echo htmlspecialchars($row['email'] ?? 'Not set'); ?>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <?php if($row['role'] == 'super_admin'): ?>
                                        <span class="bg-purple-200 text-purple-600 py-1 px-3 rounded-full text-xs font-bold">Super Admin</span>
                                    <?php elseif($row['role'] == 'admin'): ?>
                                        <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs font-bold">Admin</span>
                                    <?php elseif($row['role'] == 'faculty'): ?>
                                        <span class="bg-info text-info-800 py-1 px-3 rounded-full text-xs">Faculty</span>
                                    <?php else: ?>
                                        <span class="bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs">Student</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span class="bg-yellow-100 text-yellow-700 py-1 px-3 rounded-full text-xs font-bold">
                                        <?php echo $row['points']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span><?php echo formatDateTime($row['reg_date']); ?></span>
                                </td>
                                
                                <?php if ($isSuperAdmin): ?>
                                    <td class="py-3 px-6 text-center">
                                        <?php if ($isAdmin): ?>
                                            <div class="text-xs">
                                                <?php echo $row['role_expiry'] ? formatDateTime($row['role_expiry']) : 'Permanent'; ?>
                                                <?php if (!$isSelf): ?>
                                                    <button onclick="openExpiryModal(<?php echo $row['id']; ?>, '<?php echo $row['role_expiry']; ?>')" class="text-blue-500 hover:underline ml-1">Edit</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        <?php if(!$isSelf && !$isSuperAdminUser): ?>
                                            
                                            <?php if ($isSuperAdmin): ?>
                                                <?php if ($isAdmin): ?>
                                                    <a href="view_users.php?action=demote&id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-800" title="Demote to Student">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                        </svg>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="view_users.php?action=promote&id=<?php echo $row['id']; ?>" class="text-green-600 hover:text-green-800" title="Promote to Admin">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <a href="view_users.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this user? This will also delete their scan history.');" class="text-red-500 hover:text-red-700" title="Delete User">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </a>
                                        <?php elseif ($isSelf): ?>
                                            <span class="badge bg-secondary text-xs">You</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $isSuperAdmin ? '9' : '8'; ?>" class="py-3 px-6 text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expiry Modal -->
    <div id="expiryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96 shadow-xl">
            <h3 class="text-lg font-bold mb-4 text-pwu-maroon font-sans">Set Admin Expiry</h3>
            <form method="POST" action="view_users.php">
                <input type="hidden" name="user_id" id="modalUserId">
                <input type="hidden" name="update_expiry" value="1">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2 font-sans">Expiry Date & Time</label>
                    <input type="datetime-local" name="expiry_date" id="modalExpiryDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-sans">
                    <p class="text-xs text-gray-500 mt-1 font-sans">Leave empty to make permanent.</p>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeExpiryModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded font-sans">Cancel</button>
                    <button type="submit" class="bg-pwu-maroon hover:bg-red-900 text-white font-bold py-2 px-4 rounded font-sans">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openExpiryModal(userId, currentExpiry) {
            document.getElementById('modalUserId').value = userId;
            if (currentExpiry) {
                // Format for datetime-local input (YYYY-MM-DDTHH:MM)
                const date = new Date(currentExpiry);
                const formatted = date.toISOString().slice(0, 16);
                document.getElementById('modalExpiryDate').value = formatted;
            } else {
                document.getElementById('modalExpiryDate').value = '';
            }
            document.getElementById('expiryModal').classList.remove('hidden');
        }

        function closeExpiryModal() {
            document.getElementById('expiryModal').classList.add('hidden');
        }
    </script>
</body>
</html>