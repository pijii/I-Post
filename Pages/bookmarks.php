<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

require_once "../config.php";

$user_id = $_SESSION['user_id'];
$profile_picture = resolveUserImagePath($_SESSION['profile_picture'] ?? '', '../img/default_profile.png');

// Helper function for user avatars
if (!function_exists('getAvatar')) {
    function getAvatar($path) {
        return resolveUserImagePath($path, '../img/default_profile.png');
    }
}

// Handle Unsave/Remove Bookmark Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_bookmark') {
    $post_id_to_remove = intval($_POST['post_id'] ?? 0);
    if ($post_id_to_remove > 0) {
        $del_stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
        $del_stmt->bind_param("ii", $user_id, $post_id_to_remove);
        $del_stmt->execute();
        $del_stmt->close();
        
        header("Location: bookmarks.php");
        exit;
    }
}

// Fetch saved/bookmarked posts with author details, likes count, comment count, and user states
$bookmarks_query = "
    SELECT 
        b.id AS bookmark_id,
        b.created_at AS saved_at,
        p.id AS post_id,
        p.post_txt AS content,
        p.post_img AS image_path,
        p.created_at AS post_created_at,
        u.id AS author_id,
        u.fullname AS author_name,
        u.username AS author_username,
        u.profile_picture AS author_avatar,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) AS is_liked
    FROM bookmarks b
    JOIN posts p ON b.post_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$bm_stmt = $conn->prepare($bookmarks_query);
$bm_stmt->bind_param("ii", $user_id, $user_id);
$bm_stmt->execute();
$saved_posts = $bm_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Saved Posts</title>
    <!-- Bootstrap CSS & Icons -->
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
</head>
<body class="bg-light">

    <?php
    // Include top navigation bar
    include_once "../Components/nav.php";
    ?>

    <div class="container-fluid py-4 px-md-4">
        <div class="row g-4">
            
            <!-- 1. LEFT COLUMN: Friends (3/12 width) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friends.php"; ?>
                </div>
            </div>

            <!-- 2. CENTER COLUMN: Bookmarks Content (6/12 width) -->
            <div class="col-12 col-lg-6">

                <br><br>

                <!-- Header Title Card -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bookmark-fill text-warning fs-4"></i>
                            <h5 class="fw-bold mb-0 text-dark">Saved Posts</h5>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            <?php echo $saved_posts ? $saved_posts->num_rows : 0; ?> Saved
                        </span>
                    </div>
                </div>

                <!-- Bookmarked Posts List matching image style -->
                <?php if ($saved_posts && $saved_posts->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($post = $saved_posts->fetch_assoc()): ?>
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body p-3">
                                    
                                    <!-- Post Header -->
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo getAvatar($post['author_avatar']); ?>" 
                                                 alt="Avatar" 
                                                 class="rounded-circle me-2 object-fit-cover" 
                                                 style="width: 40px; height: 40px;"
                                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                            <div class="lh-sm">
                                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['author_name']); ?></h6>
                                                <small class="text-muted">
                                                    @<?php echo htmlspecialchars($post['author_username']); ?> • <?php echo date('M d, Y h:i A', strtotime($post['post_created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Options Dropdown Menu -->
                                        <div class="dropdown">
                                            <button class="btn btn-link text-secondary p-1 border-0 shadow-none rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-5"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="post.php?id=<?php echo $post['post_id']; ?>">
                                                        <i class="bi bi-eye text-primary"></i> View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <form method="POST" action="bookmarks.php">
                                                        <input type="hidden" name="action" value="remove_bookmark">
                                                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger border-0 bg-transparent">
                                                            <i class="bi bi-bookmark-x-fill"></i> Unsave Post
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <?php if (!empty($post['content'])): ?>
                                        <p class="card-text mb-3 text-break text-dark"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                    <?php endif; ?>

                                    <!-- Media Attachment -->
                                    <?php if (!empty($post['image_path'])): ?>
                                        <div class="mb-3">
                                            <img src="<?php echo getAvatar($post['image_path']); ?>" alt="Post Image" class="img-fluid rounded-3 w-100 object-fit-cover" style="max-height: 450px;">
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-2 text-muted opacity-25">

                                    <!-- Post Action Footer Buttons matching user screenshot -->
                                    <div class="row g-2 text-center pt-1">
                                        <!-- Like Button -->
                                        <div class="col">
                                            <form action="../Context/like.php" method="POST" class="w-100">
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'bookmarks.php'); ?>">
                                                <button type="submit" class="btn btn-light w-100 py-2 border-0 text-secondary fw-semibold fs-7 rounded-3 d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi <?php echo $post['is_liked'] ? 'bi-heart-fill text-danger' : 'bi-heart-fill text-danger'; ?>"></i>
                                                    <span><?php echo $post['likes_count']; ?> Like<?php echo $post['likes_count'] == 1 ? '' : 's'; ?></span>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Comment Button -->
                                        <div class="col">
                                            <a href="post.php?id=<?php echo $post['post_id']; ?>" class="btn btn-light w-100 py-2 border-0 text-secondary fw-semibold fs-7 rounded-3 d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                                                <i class="bi bi-chat-left"></i>
                                                <span><?php echo $post['comments_count']; ?> Comment<?php echo $post['comments_count'] == 1 ? '' : 's'; ?></span>
                                            </a>
                                        </div>

                                        <!-- Saved Button -->
                                        <div class="col">
                                            <form method="POST" action="bookmarks.php" class="w-100">
                                                <input type="hidden" name="action" value="remove_bookmark">
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                <button type="submit" class="btn btn-light w-100 py-2 border-0 text-secondary fw-semibold fs-7 rounded-3 d-flex align-items-center justify-content-center gap-2">
                                                    <i class="bi bi-bookmark-fill text-warning"></i>
                                                    <span>Saved</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="card border-0 shadow-sm text-center p-5 rounded-3">
                        <div class="card-body py-4">
                            <i class="bi bi-bookmark-dash text-secondary display-4 mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark mb-1">No Saved Posts Yet</h5>
                            <p class="text-muted fs-7 mb-4">Items you save will be kept here for easy access.</p>
                            <a href="dashboard.php" class="btn btn-primary rounded-pill px-4 fw-bold">Explore Feed</a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- 3. RIGHT COLUMN: Friend Requests (3/12 width) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friendrequest.php"; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('[data-bs-toggle="dropdown"]');
            if (toggleBtn) {
                e.stopPropagation();
                if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                    const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(toggleBtn);
                    dropdownInstance.toggle();
                }
            }
        });
    });
    </script>
</body>
</html>