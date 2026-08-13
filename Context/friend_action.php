<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection exists
require_once __DIR__ . '/../config.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Pages/signup.php");
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_user_id = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;

    // Fallback URL to redirect back to the previous page
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../Pages/explore.php';

    switch ($action) {
        case 'add_friend':
            if ($target_user_id > 0 && $target_user_id !== $current_user_id) {
                // Check if a relationship or request already exists
                $check_sql = "SELECT id FROM friends 
                              WHERE (user_id = ? AND friend_user_id = ?) 
                                 OR (user_id = ? AND friend_user_id = ?)";
                $stmt = $conn->prepare($check_sql);
                $stmt->bind_param("iiii", $current_user_id, $target_user_id, $target_user_id, $current_user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    // Send friend request
                    $insert_sql = "INSERT INTO friends (user_id, friend_user_id, status) VALUES (?, ?, 'pending')";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ii", $current_user_id, $target_user_id);
                    $insert_stmt->execute();
                    $insert_stmt->close();

                    createNotification($conn, $target_user_id, $current_user_id, 'friend_request', null, null);
                }
                $stmt->close();
            }
            break;

        case 'accept_request':
            if ($request_id > 0) {
                // Accept request using request_id
                $sql = "SELECT user_id FROM friends WHERE id = ? AND friend_user_id = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $request_id, $current_user_id);
                $stmt->execute();
                $request_row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($request_row) {
                    $sender_id = (int)($request_row['user_id'] ?? 0);
                    $update_sql = "UPDATE friends SET status = 'accepted' WHERE id = ? AND friend_user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $request_id, $current_user_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    if ($sender_id > 0 && $sender_id !== $current_user_id) {
                        createNotification($conn, $sender_id, $current_user_id, 'friend_accept', null, null);
                    }
                }
            } elseif ($target_user_id > 0) {
                // Accept request using target_user_id
                $sql = "SELECT user_id FROM friends WHERE user_id = ? AND friend_user_id = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $target_user_id, $current_user_id);
                $stmt->execute();
                $request_row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($request_row) {
                    $sender_id = (int)($request_row['user_id'] ?? 0);
                    $update_sql = "UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $target_user_id, $current_user_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    if ($sender_id > 0 && $sender_id !== $current_user_id) {
                        createNotification($conn, $sender_id, $current_user_id, 'friend_accept', null, null);
                    }
                }
            }
            break;

        case 'decline_request':
        case 'cancel_request':
        case 'remove_friend':
        case 'unfriend':
            if ($request_id > 0) {
                // Delete request/friendship by request_id
                $sql = "DELETE FROM friends WHERE id = ? AND (user_id = ? OR friend_user_id = ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $request_id, $current_user_id, $current_user_id);
                $stmt->execute();
                $stmt->close();
            } elseif ($target_user_id > 0) {
                // Delete relation by user IDs
                $sql = "DELETE FROM friends 
                        WHERE (user_id = ? AND friend_user_id = ?) 
                           OR (user_id = ? AND friend_user_id = ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiii", $current_user_id, $target_user_id, $target_user_id, $current_user_id);
                $stmt->execute();
                $stmt->close();
            }
            break;

        default:
            break;
    }

    header("Location: " . $redirect_url);
    exit;
} else {
    header("Location: ../Pages/explore.php");
    exit;
}