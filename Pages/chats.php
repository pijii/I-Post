<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

require_once "../config.php";

$user_id = $_SESSION['user_id'];

$threads_sql = "
    SELECT
        contact.id AS contact_id,
        contact.fullname,
        contact.username,
        contact.profile_picture,
        MAX(c.created_at) AS last_at,
        SUBSTRING_INDEX(GROUP_CONCAT(c.message ORDER BY c.created_at DESC SEPARATOR '||'), '||', 1) AS last_message,
        SUM(CASE WHEN c.receiver_id = ? AND c.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
    FROM chats c
    JOIN users contact ON (contact.id = c.sender_id OR contact.id = c.receiver_id)
    WHERE (c.sender_id = ? OR c.receiver_id = ?) AND contact.id != ?
    GROUP BY contact.id
    ORDER BY last_at DESC
";

$thread_stmt = $conn->prepare($threads_sql);
$thread_stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$thread_stmt->execute();
$threads = $thread_stmt->get_result();
$thread_stmt->close();

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
    <title>I-Post | Chats</title>
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
</head>
<body class="bg-light">

    <?php include_once "../Components/nav.php"; ?>


    <div class="container-fluid py-4 px-md-4">
        <div class="row g-4">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friends.php"; ?>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <br><br>
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-chat-dots-fill text-primary fs-4"></i>
                            <div>
                                <h5 class="fw-bold mb-0">Chats</h5>
                                <small class="text-muted">Your most recent conversations.</small>
                            </div>
                        </div>
                        <a href="notifications.php" class="btn btn-outline-secondary btn-sm">View Notifications</a>
                    </div>
                </div>
                

                <?php if ($threads && $threads->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($thread = $threads->fetch_assoc()): ?>
                            <div class="list-group-item d-flex align-items-center gap-3 rounded-3 mb-2 p-0">
                                <a href="profile.php?id=<?php echo (int)$thread['contact_id']; ?>" class="p-3 pe-0 d-inline-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars(getImageUrl($thread['profile_picture'], '../img/default_profile.png')); ?>" alt="Contact" class="rounded-circle border" style="width: 52px; height: 52px; object-fit: cover;">
                                </a>

                                
                                <a href="chat_detail.php?id=<?php echo (int)$thread['contact_id']; ?>" class="list-group-item list-group-item-action flex-grow-1 d-flex align-items-center gap-3 py-3 pe-3 rounded-3 border-0">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-semibold text-truncate"><?php echo htmlspecialchars($thread['fullname']); ?></h6>
                                            <small class="text-muted"><?php echo date('M d, h:i A', strtotime($thread['last_at'])); ?></small>
                                        </div>
                                        <p class="mb-0 text-muted text-truncate"><?php echo htmlspecialchars($thread['last_message']); ?></p>
                                    </div>
                                    
                                    <?php if ((int)$thread['unread_count'] > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo (int)$thread['unread_count']; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted">
                        No conversations yet. Start chatting with other users from their profile pages.
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friendrequest.php"; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
