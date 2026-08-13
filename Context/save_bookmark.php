<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($post_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Post ID']);
    exit;
}

// Check if bookmark exists
$check = mysqli_prepare($conn, "SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ?");
mysqli_stmt_bind_param($check, "ii", $user_id, $post_id);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {
    // Remove bookmark
    $delete = mysqli_prepare($conn, "DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
    mysqli_stmt_bind_param($delete, "ii", $user_id, $post_id);
    if (mysqli_stmt_execute($delete)) {
        echo json_encode(['status' => 'removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove bookmark']);
    }
    header("Location: ../Pages/dashboard.php");
} else {
    // Add bookmark
    $insert = mysqli_prepare($conn, "INSERT INTO bookmarks (user_id, post_id, created_at) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($insert, "ii", $user_id, $post_id);
    if (mysqli_stmt_execute($insert)) {
        echo json_encode(['status' => 'added']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add bookmark']);
    }
    header("Location: ../Pages/dashboard.php");
}
?>