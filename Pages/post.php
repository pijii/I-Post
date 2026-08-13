<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config.php';

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT p.id AS post_id, p.user_id, p.post_txt, p.post_img, p.created_at, u.fullname, u.username, u.profile_picture,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) AS is_liked,
        (SELECT COUNT(*) FROM bookmarks WHERE post_id = p.id AND user_id = ?) AS is_saved
     FROM posts p
     JOIN users u ON u.id = p.user_id
     WHERE p.id = ?"
);
$stmt->bind_param("iii", $user_id, $user_id, $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    header("Location: dashboard.php");
    exit;
}

$profile_picture = resolveUserImagePath($_SESSION['profile_picture'] ?? '', '../img/default_profile.png');
$author_avatar = resolveUserImagePath($post['profile_picture'] ?? '', '../img/default_profile.png');
$post_image = !empty($post['post_img']) ? resolveUserImagePath($post['post_img'], '') : '';

$comment_stmt = $conn->prepare(
    "SELECT c.id AS comment_id, c.user_id, c.comment_txt, c.created_at, u.fullname, u.profile_picture,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) AS comment_likes_count,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id AND user_id = ?) AS is_comment_liked
     FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.post_id = ?
     ORDER BY c.created_at ASC"
);
$comment_stmt->bind_param("ii", $user_id, $post_id);
$comment_stmt->execute();
$comments_result = $comment_stmt->get_result();
$comment_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-Post | Post</title>
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
</head>
<body class="bg-light">
    <?php include_once "../Components/nav.php"; ?>

    <br><br><br>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="profile.php?id=<?php echo (int)$post['user_id']; ?>" class="d-flex align-items-center text-decoration-none flex-grow-1 me-2">
                                <img src="<?php echo htmlspecialchars($author_avatar); ?>"
                                     alt="Profile"
                                     class="rounded-circle me-2 object-fit-cover"
                                     width="46"
                                     height="46"
                                     onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                <div class="lh-sm">
                                    <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['fullname']); ?></h5>
                                    <small class="text-muted">@<?php echo htmlspecialchars($post['username']); ?> • <?php echo date('M d, Y h:i A', strtotime($post['created_at'])); ?></small>
                                </div>
                            </a>

                            <a href="dashboard.php" class="btn btn-light rounded-pill btn-sm">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>

                        <?php if (!empty($post['post_txt'])): ?>
                            <p class="card-text text-break mb-3 fs-6"><?php echo nl2br(htmlspecialchars($post['post_txt'])); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($post_image)): ?>
                            <div class="mb-3 text-center">
                                <img src="<?php echo htmlspecialchars($post_image); ?>"
                                     class="img-fluid rounded-3 w-100 object-fit-cover"
                                     style="max-height: 600px;"
                                     alt="Post image"
                                     onerror="this.onerror=null; this.src='../img/default_profile.png';">
                            </div>
                        <?php endif; ?>

                        <hr class="my-3">

                        <div class="d-flex gap-2 flex-wrap">
                            <form action="../Context/like.php" method="POST" class="d-inline">
                                <input type="hidden" name="post_id" value="<?php echo (int)$post['post_id']; ?>">
                                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'post.php?id=' . (int)$post['post_id']); ?>">
                                <button type="submit" class="btn btn-light rounded-pill px-3">
                                    <i class="bi <?php echo $post['is_liked'] ? 'bi-heart-fill text-danger' : 'bi-heart'; ?> me-1"></i>
                                    <?php echo (int)$post['likes_count']; ?> Like<?php echo (int)$post['likes_count'] === 1 ? '' : 's'; ?>
                                </button>
                            </form>

                            <form action="../Context/save_bookmark.php" method="POST" class="d-inline">
                                <input type="hidden" name="post_id" value="<?php echo (int)$post['post_id']; ?>">
                                <button type="submit" class="btn btn-light rounded-pill px-3">
                                    <i class="bi <?php echo $post['is_saved'] ? 'bi-bookmark-fill text-warning' : 'bi-bookmark'; ?> me-1"></i>
                                    <?php echo $post['is_saved'] ? 'Saved' : 'Save'; ?>
                                </button>
                            </form>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="bi bi-chat-left-text me-1"></i>
                                Comments (<?php echo (int)$post['comments_count']; ?>)
                            </h6>

                            <form action="../Context/comment.php" method="POST" class="d-flex gap-2 mb-3">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="post_id" value="<?php echo (int)$post['post_id']; ?>">
                                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'post.php?id=' . (int)$post['post_id']); ?>">
                                <input type="text" name="comment_txt" class="form-control rounded-pill bg-light border-0" placeholder="Write a comment..." required>
                                <button type="submit" class="btn btn-primary rounded-pill px-3">Post</button>
                            </form>

                            <?php if ($comments_result && $comments_result->num_rows > 0): ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php while ($comment = $comments_result->fetch_assoc()): ?>
                                        <?php $comment_avatar = resolveUserImagePath($comment['profile_picture'] ?? '', '../img/default_profile.png'); ?>
                                        <div class="d-flex align-items-start gap-2 bg-light p-2 rounded-3">
                                            <img src="<?php echo htmlspecialchars($comment_avatar); ?>"
                                                 alt="Comment avatar"
                                                 class="rounded-circle object-fit-cover"
                                                 width="32"
                                                 height="32"
                                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small mb-1"><?php echo htmlspecialchars($comment['fullname']); ?></div>
                                                <div class="small text-dark mb-2"><?php echo htmlspecialchars($comment['comment_txt']); ?></div>
                                                <form action="../Context/comment_like.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="comment_id" value="<?php echo (int)$comment['comment_id']; ?>">
                                                    <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'post.php?id=' . (int)$post['post_id']); ?>">
                                                    <button type="submit" class="btn btn-link btn-sm p-0 text-secondary text-decoration-none d-inline-flex align-items-center gap-1">
                                                        <i class="bi <?php echo !empty($comment['is_comment_liked']) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                        <span><?php echo (int)$comment['comment_likes_count']; ?></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">No comments yet. Be the first to comment.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
