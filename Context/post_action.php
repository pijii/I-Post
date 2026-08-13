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
$action = $_POST['action'] ?? '';

$redirect_to = $_POST['redirect_to'] ?? $_SERVER['HTTP_REFERER'] ?? '../Pages/dashboard.php';
if (filter_var($redirect_to, FILTER_VALIDATE_URL) !== false) {
    $parsed = parse_url($redirect_to);
    $redirect_to = $parsed['path'] ?? '../Pages/dashboard.php';
}
$redirect_to = trim($redirect_to);
if ($redirect_to === '' || !str_starts_with($redirect_to, '/') && !str_starts_with($redirect_to, '../') && !str_starts_with($redirect_to, './') && !preg_match('#^(Pages|Components|Context)/#', $redirect_to)) {
    $redirect_to = '../Pages/dashboard.php';
}

if ($post_id > 0) {
    if ($action === 'delete') {
        // Retrieve post image path to clean up files
        $select_stmt = $conn->prepare("SELECT post_img FROM posts WHERE id = ? AND user_id = ?");
        $select_stmt->bind_param("ii", $post_id, $user_id);
        $select_stmt->execute();
        $res = $select_stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $post = $res->fetch_assoc();
            if (!empty($post['post_img']) && file_exists('../' . $post['post_img'])) {
                unlink('../' . $post['post_img']);
            }

            // Delete post record (cascade deletes likes and comments via schema constraints)
            $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $post_id, $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
        $select_stmt->close();

    } elseif ($action === 'update') {
        $post_txt = trim($_POST['post_txt'] ?? '');

        if (!empty($post_txt)) {
            $update_stmt = $conn->prepare("UPDATE posts SET post_txt = ? WHERE id = ? AND user_id = ?");
            $update_stmt->bind_param("sii", $post_txt, $post_id, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
}

header("Location: " . $redirect_to);
exit();