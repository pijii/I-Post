<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

$redirect_to = $_POST['redirect_to'] ?? $_SERVER['HTTP_REFERER'] ?? '../Pages/dashboard.php';
if (filter_var($redirect_to, FILTER_VALIDATE_URL) !== false) {
    $parsed = parse_url($redirect_to);
    $redirect_to = $parsed['path'] ?? '../Pages/dashboard.php';
}
$redirect_to = trim($redirect_to);
if ($redirect_to === '' || (!str_starts_with($redirect_to, '/') && !str_starts_with($redirect_to, '../') && !str_starts_with($redirect_to, './') && !preg_match('#^(Pages|Components|Context)/#', $redirect_to))) {
    $redirect_to = '../Pages/dashboard.php';
}

if (!isset($_SESSION['user_id'])) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    } else {
        header('Location: ' . $redirect_to);
    }
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

if ($comment_id <= 0) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid Comment ID']);
    } else {
        header('Location: ' . $redirect_to);
    }
    exit;
}

$check = mysqli_prepare($conn, "SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
mysqli_stmt_bind_param($check, "ii", $user_id, $comment_id);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {
    $delete = mysqli_prepare($conn, "DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    mysqli_stmt_bind_param($delete, "ii", $user_id, $comment_id);
    mysqli_stmt_execute($delete);
    $status = 'unliked';
} else {
    $insert = mysqli_prepare($conn, "INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($insert, "ii", $user_id, $comment_id);
    mysqli_stmt_execute($insert);
    $status = 'liked';
}

$count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM comment_likes WHERE comment_id = ?");
mysqli_stmt_bind_param($count_stmt, "i", $comment_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));

if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'likes_count' => (int)$count_result['total']
    ]);
    exit;
}

header('Location: ' . $redirect_to);
exit;
?>