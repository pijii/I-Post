<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/chats.php');
    exit;
}

require_once __DIR__ . '/../config.php';

$current_user_id = (int)$_SESSION['user_id'];
$message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
$conversation_user_id = isset($_POST['conversation_user_id']) ? (int)$_POST['conversation_user_id'] : 0;

if ($message_id > 0) {
    $stmt = $conn->prepare('DELETE FROM chats WHERE id = ? AND sender_id = ?');
    $stmt->bind_param('ii', $message_id, $current_user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: ../Pages/chat_detail.php?id=' . $conversation_user_id);
exit;
