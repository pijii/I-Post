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
$action = $_POST['action'] ?? 'add';

if ($action === 'add') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment_txt = trim($_POST['comment_txt'] ?? '');

    if ($post_id > 0 && !empty($comment_txt)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_txt, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_txt);
        $stmt->execute();
        $stmt->close();
    }
} elseif ($action === 'update') {
    $comment_id = intval($_POST['comment_id'] ?? 0);
    $comment_txt = trim($_POST['comment_txt'] ?? '');

    if ($comment_id > 0 && !empty($comment_txt)) {
        $stmt = $conn->prepare("UPDATE comments SET comment_txt = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $comment_txt, $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
} elseif ($action === 'delete') {
    $comment_id = intval($_POST['comment_id'] ?? 0);

    if ($comment_id > 0) {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: ../Pages/dashboard.php");
exit();