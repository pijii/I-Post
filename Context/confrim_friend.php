<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection exists
require_once "../config.php";

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Pages/login.php");
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_user_id = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;

    $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../Pages/dashboard.php';

    switch ($action) {
        case 'accept_request':
            if ($request_id > 0) {
                // Update using request primary ID
                $sql = "UPDATE friends SET status = 'accepted' WHERE id = ? AND friend_user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $request_id, $current_user_id);
                $stmt->execute();
                $stmt->close();
            } elseif ($target_user_id > 0) {
                // Fallback using target sender ID
                $sql = "UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $target_user_id, $current_user_id);
                $stmt->execute();
                $stmt->close();
            }
            break;

        case 'decline_request':
        case 'cancel_request':
        case 'remove_friend':
        case 'unfriend':
            if ($request_id > 0) {
                // Delete using request primary ID
                $sql = "DELETE FROM friends WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $request_id);
                $stmt->execute();
                $stmt->close();
            } elseif ($target_user_id > 0) {
                // Fallback using target user relationship
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
    header("Location: ../Pages/dashboard.php");
    exit;
}
?>