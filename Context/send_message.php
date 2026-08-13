<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Pages/chats.php");
    exit;
}

$sender_id = (int)$_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($receiver_id > 0 && $receiver_id !== $sender_id && $message !== '') {
    $stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../Pages/chat_detail.php?id=" . $receiver_id);
exit;
