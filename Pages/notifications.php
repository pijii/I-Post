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

$update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();
$update_stmt->close();

$notifications_stmt = $conn->prepare(
    "SELECT n.id, n.type, n.post_id, n.comment_id, n.created_at, n.is_read, u.id AS actor_id, u.fullname, u.profile_picture
     FROM notifications n
     JOIN users u ON u.id = n.actor_id
     WHERE n.user_id = ?
     ORDER BY n.created_at DESC"
);
$notifications_stmt->bind_param("i", $user_id);
$notifications_stmt->execute();
$notifications = $notifications_stmt->get_result();
$notifications_stmt->close();

function getImageUrl($path, $default) {
    return resolveUserImagePath($path, $default);
}

function formatNotificationText($type) {
    return match ($type) {
        'like' => 'liked your post.',
        'comment' => 'commented on your post.',
        'friend_request' => 'sent you a friend request.',
        'friend_accept' => 'accepted your friend request.',
        default => 'sent you an update.',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Notifications</title>
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
                            <i class="bi bi-bell-fill text-primary fs-4"></i>
                            <div>
                                <h5 class="fw-bold mb-0">Notifications</h5>
                                <small class="text-muted">All recent activity on your profile.</small>
                            </div>
                        </div>
                        <a href="chats.php" class="btn btn-outline-secondary btn-sm">Go to Chats</a>
                    </div>
                </div>

                <?php if ($notifications && $notifications->num_rows > 0): ?>
                    <div class="list-group">
                        <?php while ($notif = $notifications->fetch_assoc()): ?>
                            <a href="../Context/mark_notification_read.php?id=<?php echo (int)($notif['id'] ?? 0); ?>&redirect=profile.php?id=<?php echo (int)($notif['actor_id'] ?? 0); ?>" class="list-group-item list-group-item-action d-flex gap-3 align-items-start <?php echo $notif['is_read'] ? '' : 'bg-light'; ?> rounded-3 mb-2 text-decoration-none text-dark">
                                <img src="<?php echo htmlspecialchars(getImageUrl($notif['profile_picture'], '../img/default_profile.png')); ?>" alt="Actor" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <p class="mb-1"><strong><?php echo htmlspecialchars($notif['fullname']); ?></strong> <?php echo htmlspecialchars(formatNotificationText($notif['type'])); ?></p>
                                    <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></small>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted">
                        No new notifications yet.
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
