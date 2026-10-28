<?php
header('Content-Type: application/json');
include('../session.php');
include('../config.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get raw POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $unique_text = isset($input['unique_text']) ? mysqli_real_escape_string($link, $input['unique_text']) : '';

    if (empty($unique_text)) {
        echo json_encode(['success' => false, 'message' => 'No QR code provided']);
        exit();
    }

    // Fetch QR code details from the database
    $sql = "SELECT * FROM qr_codes WHERE unique_text = '$unique_text'";
    $result = mysqli_query($link, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $qr_code = mysqli_fetch_assoc($result);

        // Check if the QR code has expired
        if (strtotime($qr_code['expiration_time']) < time()) {
            echo json_encode(['success' => false, 'message' => 'This QR code has expired.']);
        } else {
            // Check if the user has already scanned this QR code
            $check_sql = "SELECT * FROM scan_history WHERE user_id = '$user_id' AND qr_code_id = '" . $qr_code['id'] . "'";
            $check_result = mysqli_query($link, $check_sql);

            if (mysqli_num_rows($check_result) > 0) {
                echo json_encode(['success' => false, 'message' => 'You have already claimed points from this QR code.']);
            } else {
                $points_to_add = $qr_code['points'];
                $qr_code_id = $qr_code['id'];
                $description = $qr_code['description'];

                // Add points to the user's scan history
                $insert_sql = "INSERT INTO scan_history (user_id, qr_code_id, points_added, description) 
                               VALUES ('$user_id', '$qr_code_id', '$points_to_add', '$description')";
                
                if (mysqli_query($link, $insert_sql)) {
                    // Update the user's points in the users table
                    $update_sql = "UPDATE users SET points = points + $points_to_add WHERE id = '$user_id'";
                    if (mysqli_query($link, $update_sql)) {
                        
                        // Get updated total points
                        $sql_points = "SELECT points FROM users WHERE id = '$user_id'";
                        $result_points = mysqli_query($link, $sql_points);
                        $row_points = mysqli_fetch_assoc($result_points);
                        $new_total = $row_points['points'];

                        echo json_encode([
                            'success' => true, 
                            'message' => "Successfully added " . $points_to_add . " points!",
                            'points_added' => $points_to_add,
                            'new_total' => $new_total
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error updating user points: ' . mysqli_error($link)]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error adding points: ' . mysqli_error($link)]);
                }
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid QR code. You are not scanning a PWU Event Code.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
