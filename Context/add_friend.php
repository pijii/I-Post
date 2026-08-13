<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Map the add friend form field to the friend_action handler.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['action'])) {
        $_POST['action'] = 'add_friend';
    }
    if (!isset($_POST['target_user_id']) && isset($_POST['receiver_id'])) {
        $_POST['target_user_id'] = intval($_POST['receiver_id']);
    }
}

require_once __DIR__ . '/friend_action.php';
