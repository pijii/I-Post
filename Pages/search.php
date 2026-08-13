<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

require_once "../config.php";

$search_query = trim($_GET['q'] ?? '');
$search_users = [];
$search_posts = [];

function getImageUrl($path, $default) {
    return resolveUserImagePath($path, $default);
}

if ($search_query !== '') {
    $like_query = "%{$search_query}%";

    $user_stmt = $conn->prepare(
        "SELECT id, fullname, username, profile_picture FROM users WHERE (fullname LIKE ? OR username LIKE ?) AND id != ? ORDER BY fullname ASC LIMIT 10"
    );
    $user_stmt->bind_param("ssi", $like_query, $like_query, $_SESSION['user_id']);
    $user_stmt->execute();
    $search_users = $user_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $user_stmt->close();

    $post_stmt = $conn->prepare(
        "SELECT p.id AS post_id, p.user_id, p.post_txt, p.post_img, p.created_at, u.fullname, u.username, u.profile_picture,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS likes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) AS is_liked
         FROM posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.post_txt LIKE ? OR u.fullname LIKE ? OR u.username LIKE ?
         ORDER BY p.created_at DESC
         LIMIT 20"
    );
    $post_stmt->bind_param("isss", $_SESSION['user_id'], $like_query, $like_query, $like_query);
    $post_stmt->execute();
    $search_posts = $post_stmt->get_result();
    $post_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Search</title>
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

                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3">
                        <form action="search.php" method="GET" class="d-flex gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 ps-3 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="q" class="form-control bg-light border-0 py-2" placeholder="Search posts, people, topics..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary px-4 rounded-3 fw-bold">Search</button>
                        </form>
                    </div>
                </div>

                <?php if ($search_query !== ''): ?>
                    <div class="mb-3">
                        <h5 class="fw-bold mb-2">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h5>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">People</h6>
                        <?php if (!empty($search_users)): ?>
                            <div class="row row-cols-1 row-cols-sm-2 g-3">
                                <?php foreach ($search_users as $user): ?>
                                    <div class="col">
                                        <div class="card border-0 shadow-sm rounded-3 h-100">
                                            <div class="card-body d-flex align-items-center gap-3">
                                                <a href="profile.php?id=<?php echo (int)$user['id']; ?>" class="d-inline-flex">
                                                    <img src="<?php echo htmlspecialchars(getImageUrl($user['profile_picture'], '../img/default_profile.png')); ?>" alt="Profile" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                                <a href="profile.php?id=<?php echo (int)$user['id']; ?>" class="text-decoration-none text-dark">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user['fullname']); ?></h6>
                                                        <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted">
                                No users found matching your query.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h6 class="fw-semibold mb-3">Posts</h6>
                        <?php if ($search_posts && $search_posts->num_rows > 0): ?>
                            <?php while ($post = $search_posts->fetch_assoc()): ?>
                                <div class="post-card card shadow-sm border-0 mb-3 rounded-3" data-post-id="<?php echo (int)$post['post_id']; ?>" style="cursor: pointer;">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <a href="profile.php?id=<?php echo (int)$post['user_id']; ?>" class="d-inline-flex">
                                                <img src="<?php echo htmlspecialchars(getImageUrl($post['profile_picture'], '../img/default_profile.png')); ?>" alt="Profile" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                                            </a>
                                            <a href="profile.php?id=<?php echo (int)$post['user_id']; ?>" class="text-decoration-none text-dark">
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['fullname']); ?></h6>
                                                    <small class="text-muted">@<?php echo htmlspecialchars($post['username']); ?> • <?php echo date('M d, Y h:i A', strtotime($post['created_at'])); ?></small>
                                                </div>
                                            </a>
                                        </div>
                                        <?php if (!empty($post['post_txt'])): ?>
                                            <p class="mb-3 text-break"><?php echo nl2br(htmlspecialchars($post['post_txt'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($post['post_img'])): ?>
                                            <img src="<?php echo htmlspecialchars(($post['post_img'][0] === '/' || str_starts_with($post['post_img'], '../')) ? $post['post_img'] : '../' . $post['post_img']); ?>" class="img-fluid rounded-3" alt="Post image">
                                        <?php endif; ?>

                                        <hr class="my-3">
                                        <div class="d-flex gap-2">
                                            <form action="../Context/like.php" method="POST" class="flex-fill">
                                                <input type="hidden" name="post_id" value="<?php echo (int)$post['post_id']; ?>">
                                                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'search.php'); ?>">
                                                <button type="submit" class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi <?php echo !empty($post['is_liked']) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                    <span><?php echo (int)$post['likes_count']; ?> Like<?php echo (int)$post['likes_count'] == 1 ? '' : 's'; ?></span>
                                                </button>
                                            </form>

                                            <a href="post.php?id=<?php echo (int)$post['post_id']; ?>" class="btn btn-light btn-sm flex-fill border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                                                <i class="bi bi-chat-left"></i>
                                                <span><?php echo (int)$post['comments_count']; ?> Comment<?php echo (int)$post['comments_count'] == 1 ? '' : 's'; ?></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted">
                                No posts found matching your query.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted">
                        Enter a search term to see users and posts.
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
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.post-card').forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (e.target.closest('a, button, form, input, textarea, select, .btn')) {
                    return;
                }

                const postId = card.getAttribute('data-post-id');
                if (postId) {
                    window.location.href = 'post.php?id=' + postId;
                }
            });
        });
    });
    </script>
</body>
</html>
