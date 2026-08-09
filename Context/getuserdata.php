<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Include database configuration
    require_once '../Config.php'; 

    // Redirect if user is not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $current_user_id = $_SESSION['user_id'];

    // 1. Fetch Current User Details
    $stmtUser = mysqli_prepare($conn, "SELECT fullname, username, profile_picture FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtUser, "i", $current_user_id);
    mysqli_stmt_execute($stmtUser);
    $resultUser = mysqli_stmt_get_result($stmtUser);
    $user_data = mysqli_fetch_assoc($resultUser) ?: [
        'fullname' => $_SESSION['fullname'] ?? 'User',
        'username' => $_SESSION['username'] ?? 'user',
        'profile_picture' => ''
    ];

    // Helper function to verify image existence and fallback correctly
    if (!function_exists('getAvatar')) {
        function getAvatar($img) {
            if (!empty($img) && file_exists("../img/uploads/" . $img)) {
                return "../img/uploads/" . $img;
            }
            return "../img/default_profile.png";
        }
    }

    // 2. Fetch Unread Chats/Messages
    $stmtChats = mysqli_prepare($conn, "
        SELECT c.*, u.fullname, u.username, u.profile_picture 
        FROM chats c 
        JOIN users u ON c.sender_id = u.id 
        WHERE c.receiver_id = ? 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    mysqli_stmt_bind_param($stmtChats, "i", $current_user_id);
    mysqli_stmt_execute($stmtChats);
    $recent_chats = mysqli_stmt_get_result($stmtChats);

    // Count unread messages for badge
    $stmtUnreadChats = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM chats WHERE receiver_id = ? AND is_read = 0");
    mysqli_stmt_bind_param($stmtUnreadChats, "i", $current_user_id);
    mysqli_stmt_execute($stmtUnreadChats);
    $resUnreadChats = mysqli_stmt_get_result($stmtUnreadChats);
    $unread_chat_count = mysqli_fetch_assoc($resUnreadChats)['total'] ?? 0;

    // 3. Fetch Unread Notifications
    $stmtNotifs = mysqli_prepare($conn, "
        SELECT n.*, u.fullname, u.profile_picture 
        FROM notifications n 
        JOIN users u ON n.actor_id = u.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC 
        LIMIT 5
    ");
    mysqli_stmt_bind_param($stmtNotifs, "i", $current_user_id);
    mysqli_stmt_execute($stmtNotifs);
    $notifications = mysqli_stmt_get_result($stmtNotifs);

    // Count unread notifications for badge
    $stmtUnreadNotifs = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    mysqli_stmt_bind_param($stmtUnreadNotifs, "i", $current_user_id);
    mysqli_stmt_execute($stmtUnreadNotifs);
    $resUnreadNotifs = mysqli_stmt_get_result($stmtUnreadNotifs);
    $unread_notif_count = mysqli_fetch_assoc($resUnreadNotifs)['total'] ?? 0;
?>