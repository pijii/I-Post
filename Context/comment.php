<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Pages/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'add';

$redirect_to = $_POST['redirect_to'] ?? $_SERVER['HTTP_REFERER'] ?? '../Pages/dashboard.php';
if (filter_var($redirect_to, FILTER_VALIDATE_URL) !== false) {
    $parsed = parse_url($redirect_to);
    $redirect_to = $parsed['path'] ?? '../Pages/dashboard.php';
}
$redirect_to = trim($redirect_to);
if ($redirect_to === '' || (!str_starts_with($redirect_to, '/') && !str_starts_with($redirect_to, '../') && !str_starts_with($redirect_to, './') && !preg_match('#^(Pages|Components|Context)/#', $redirect_to))) {
    $redirect_to = '../Pages/dashboard.php';
}

if ($action === 'add') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment_txt = trim($_POST['comment_txt'] ?? '');

    if ($post_id > 0 && !empty($comment_txt)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_txt, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_txt);
        $stmt->execute();
        $stmt->close();

        $post_owner_stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
        $post_owner_stmt->bind_param("i", $post_id);
        $post_owner_stmt->execute();
        $post_owner_result = $post_owner_stmt->get_result();

        if ($post_owner_result && $post_owner_result->num_rows > 0) {
            $post_owner = $post_owner_result->fetch_assoc();
            $owner_id = (int)($post_owner['user_id'] ?? 0);
            if ($owner_id > 0 && $owner_id !== $user_id) {
                createNotification($conn, $owner_id, $user_id, 'comment', $post_id, null);
            }
        }
        $post_owner_stmt->close();
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

header("Location: " . $redirect_to);
exit();