<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Pages/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);

if ($post_id > 0) {
    // Check if the user already liked the post
    $check_stmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $res = $check_stmt->get_result();

    if ($res && $res->num_rows > 0) {
        // Unlike post
        $delete_stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $post_id, $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    } else {
        // Like post
        $insert_stmt = $conn->prepare("INSERT INTO post_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
        $insert_stmt->bind_param("ii", $post_id, $user_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();
}

header("Location: ../Pages/dashboard.php");
exit();