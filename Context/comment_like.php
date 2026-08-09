<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

if ($comment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Comment ID']);
    exit;
}

// Check if user already liked the comment
$check = mysqli_prepare($conn, "SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
mysqli_stmt_bind_param($check, "ii", $user_id, $comment_id);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {
    // Unlike comment
    $delete = mysqli_prepare($conn, "DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    mysqli_stmt_bind_param($delete, "ii", $user_id, $comment_id);
    mysqli_stmt_execute($delete);
    $status = 'unliked';
} else {
    // Like comment
    $insert = mysqli_prepare($conn, "INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($insert, "ii", $user_id, $comment_id);
    mysqli_stmt_execute($insert);
    $status = 'liked';
}

// Get updated count
$count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM comment_likes WHERE comment_id = ?");
mysqli_stmt_bind_param($count_stmt, "i", $comment_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));

echo json_encode([
    'status' => $status,
    'likes_count' => $count_result['total']
]);
?>