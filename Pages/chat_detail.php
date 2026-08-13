<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

$conversation_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($conversation_user_id <= 0) {
    header("Location: chats.php");
    exit;
}

require_once "../config.php";

$current_user_id = $_SESSION['user_id'];
if ($conversation_user_id === $current_user_id) {
    header("Location: chats.php");
    exit;
}

$user_stmt = $conn->prepare("SELECT id, fullname, username, profile_picture FROM users WHERE id = ? LIMIT 1");
$user_stmt->bind_param("i", $conversation_user_id);
$user_stmt->execute();
$conversation_user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if (!$conversation_user) {
    header("Location: chats.php");
    exit;
}

$mark_stmt = $conn->prepare("UPDATE chats SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$mark_stmt->bind_param("ii", $conversation_user_id, $current_user_id);
$mark_stmt->execute();
$mark_stmt->close();

$messages_stmt = $conn->prepare(
    "SELECT c.id AS message_id, c.sender_id, c.receiver_id, c.message, c.created_at, u.fullname, u.profile_picture
     FROM chats c
     JOIN users u ON u.id = c.sender_id
     WHERE (c.sender_id = ? AND c.receiver_id = ?) OR (c.sender_id = ? AND c.receiver_id = ?)
     ORDER BY c.created_at ASC"
);
$messages_stmt->bind_param("iiii", $current_user_id, $conversation_user_id, $conversation_user_id, $current_user_id);
$messages_stmt->execute();
$messages = $messages_stmt->get_result();
$messages_stmt->close();

function getImageUrl($path, $default) {
    return resolveUserImagePath($path, $default);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Chat with <?php echo htmlspecialchars($conversation_user['fullname']); ?></title>
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
    <link rel="stylesheet" href="../Styles/site.css">
</head>
<body class="bg-light">

    <?php include_once "../Components/nav.php"; ?>

    
    <div class="container-fluid py-4 px-md-4 chat-page-container">
        <div class="row g-4">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friends.php"; ?>
                </div>
            </div>

            

            <div class="col-12 col-lg-6 chat-layout">
                <br><br>
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <a href="chats.php" class="btn btn-light rounded-pill btn-sm d-flex align-items-center justify-content-center" aria-label="Back to chats" title="Back to chats">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                            <img src="<?php echo htmlspecialchars(getImageUrl($conversation_user['profile_picture'], '../img/default_profile.png')); ?>" alt="Avatar" class="chat-avatar border" onerror="this.onerror=null; this.src='../img/default_profile.png';">
                            <div>
                                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($conversation_user['fullname']); ?></h5>
                                <small class="text-muted">@<?php echo htmlspecialchars($conversation_user['username']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                

                <div class="card shadow-sm border-0 rounded-3 mb-4 chat-panel">
                    <div id="chatThread" class="card-body p-3 chat-thread">
                        <?php if ($messages && $messages->num_rows > 0): ?>
                            <?php while ($message = $messages->fetch_assoc()): ?>
                                <?php $isMine = $message['sender_id'] === $current_user_id; ?>
                                <div class="mb-3 <?php echo $isMine ? 'text-end' : 'text-start'; ?>">
                                    <div class="chat-bubble <?php echo $isMine ? 'sent' : 'received'; ?>">
                                        <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                        <small class="text-muted"><?php echo date('M d, h:i A', strtotime($message['created_at'])); ?></small>
                                    </div>
                                    <?php if ($isMine): ?>
                                        <div class="mt-1 text-end">
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" data-bs-toggle="modal" data-bs-target="#deleteMessageModal_<?php echo (int)$message['message_id']; ?>">Delete</button>
                                        </div>

                                        <div class="modal fade" id="deleteMessageModal_<?php echo (int)$message['message_id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-danger">Delete Message</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-2">
                                                        <p class="mb-0 text-muted">Delete this message?</p>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <form action="../Context/delete_message.php" method="POST" class="d-inline w-100">
                                                            <input type="hidden" name="message_id" value="<?php echo (int)$message['message_id']; ?>">
                                                            <input type="hidden" name="conversation_user_id" value="<?php echo (int)$conversation_user_id; ?>">
                                                            <div class="d-flex justify-content-end gap-2">
                                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger rounded-pill px-4">Delete</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-4 text-muted">
                                Start the conversation by sending a message.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-3 chat-composer">
                    <div class="card-body p-3">
                        <form action="../Context/send_message.php" method="POST">
                            <input type="hidden" name="receiver_id" value="<?php echo (int)$conversation_user['id']; ?>">
                            <div class="mb-3">
                                <textarea name="message" class="form-control rounded-4" rows="2" placeholder="Type your message..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-end align-items-center">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friendrequest.php"; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function scrollChatToBottom() {
            const chatThread = document.getElementById('chatThread');
            if (chatThread) {
                chatThread.scrollTop = chatThread.scrollHeight;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            scrollChatToBottom();

            const messageInput = document.querySelector('textarea[name="message"]');
            const chatForm = document.querySelector('form[action="../Context/send_message.php"]');

            if (messageInput && chatForm) {
                messageInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        chatForm.requestSubmit();
                    }
                });
            }

            const observer = new MutationObserver(function () {
                scrollChatToBottom();
            });

            const chatThread = document.getElementById('chatThread');
            if (chatThread) {
                observer.observe(chatThread, { childList: true, subtree: true });
            }
        });
    </script>
</body>
</html>
