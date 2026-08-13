<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Pages/login.php');
    exit;
}

require_once __DIR__ . '/../config.php';

$notification_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = $_GET['redirect'] ?? 'notifications.php';

if ($notification_id > 0) {
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $notification_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

header('Location: ../Pages/' . ltrim($redirect, '/'));
exit;
