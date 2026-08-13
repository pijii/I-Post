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
$post_id = intval($_POST['post_id'] ?? 0);

$redirect_to = $_POST['redirect_to'] ?? $_SERVER['HTTP_REFERER'] ?? '../Pages/dashboard.php';
if (filter_var($redirect_to, FILTER_VALIDATE_URL) !== false) {
    $parsed = parse_url($redirect_to);
    $redirect_to = $parsed['path'] ?? '../Pages/dashboard.php';
}
$redirect_to = trim($redirect_to);
if ($redirect_to === '' || (!str_starts_with($redirect_to, '/') && !str_starts_with($redirect_to, '../') && !str_starts_with($redirect_to, './') && !preg_match('#^(Pages|Components|Context)/#', $redirect_to))) {
    $redirect_to = '../Pages/dashboard.php';
}

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

        $post_owner_stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
        $post_owner_stmt->bind_param("i", $post_id);
        $post_owner_stmt->execute();
        $post_owner_result = $post_owner_stmt->get_result();

        if ($post_owner_result && $post_owner_result->num_rows > 0) {
            $post_owner = $post_owner_result->fetch_assoc();
            $owner_id = (int)($post_owner['user_id'] ?? 0);
            if ($owner_id > 0 && $owner_id !== $user_id) {
                createNotification($conn, $owner_id, $user_id, 'like', $post_id, null);
            }
        }
        $post_owner_stmt->close();
    }
    $check_stmt->close();
}

header("Location: " . $redirect_to);
exit();