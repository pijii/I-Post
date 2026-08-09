<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

require_once "../Context/config.php";

$logged_in_user_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $logged_in_user_id;
$is_own_profile = ($logged_in_user_id === $profile_id);

// Fetch user profile information
$user_query = "
    SELECT 
        id, fullname, username, bio, profile_picture, cover_photo, 
        location, birth_date, is_private, created_at 
    FROM users 
    WHERE id = ?
";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: profile.php");
    exit;
}

// Helper function for user avatars and cover photos
if (!function_exists('getImageUrl')) {
    function getImageUrl($path, $default) {
        if (empty($path)) return $default;
        if (str_starts_with($path, 'http') || str_starts_with($path, '../') || str_starts_with($path, './')) {
            return $path;
        }
        return '../' . $path;
    }
}

$avatar_src = getImageUrl($user['profile_picture'], '../img/default_profile.png');
$cover_src = getImageUrl($user['cover_photo'], '../img/default_cover.jpg');

// Query user posts with author details, likes, comments count, like state, and bookmark state
$query = "
    SELECT 
        p.id AS post_id,
        p.user_id,
        p.post_txt,
        p.post_img,
        p.created_at,
        u.fullname,
        u.username,
        u.profile_picture,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS likes_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) AS is_liked,
        (SELECT COUNT(*) FROM bookmarks WHERE post_id = p.id AND user_id = ?) AS is_saved
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $logged_in_user_id, $logged_in_user_id, $profile_id);
$stmt->execute();
$posts_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | <?php echo htmlspecialchars($user['fullname']); ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
    <style>
        .cover-container {
            height: 280px;
            background-color: #e0e0e0;
            background-size: cover;
            background-position: center;
            border-radius: 0 0 12px 12px;
        }
        .profile-avatar-wrapper {
            width: 150px;
            height: 150px;
            margin-top: -75px;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid #fff;
        }
    </style>
</head>
<body class="bg-light">

    <?php include_once "../Components/nav.php"; ?>

    <div class="container py-4">
        
        <!-- COVER PHOTO BANNER -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
            <div class="cover-container" style="background-image: url('<?php echo $cover_src; ?>');"></div>
            
            <div class="card-body p-4 bg-white">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end justify-content-between gap-3">
                    
                    <!-- PROFILE PHOTO & MAIN INFORMATION -->
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end gap-3 text-center text-md-start">
                        <div class="profile-avatar-wrapper position-relative">
                            <img src="<?php echo $avatar_src; ?>" 
                                 alt="Profile" 
                                 class="profile-avatar rounded-circle shadow"
                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($user['fullname']); ?></h3>
                            <p class="text-muted mb-1">@<?php echo htmlspecialchars($user['username']); ?></p>
                            <?php if (!empty($user['bio'])): ?>
                                <p class="mb-0 text-secondary"><?php echo htmlspecialchars($user['bio']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ACTION BUTTON / EDIT PROFILE -->
                    <div>
                        <?php if ($is_own_profile): ?>
                            <button class="btn btn-outline-primary rounded-pill fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil-square me-1"></i> Edit Profile
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary rounded-pill fw-semibold px-4">
                                <i class="bi bi-person-plus-fill me-1"></i> Add Friend
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- LOWER SECTION: PERSONAL DETAILS & POSTS -->
        <div class="row g-4">
            
            <!-- LEFT COLUMN: Personal Details -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                    <h5 class="fw-bold mb-3 text-dark">Personal Details</h5>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-secondary fs-6">
                        <?php if (!empty($user['location'])): ?>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <span>Lives in <strong><?php echo htmlspecialchars($user['location']); ?></strong></span>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['birth_date'])): ?>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-cake2-fill text-primary"></i>
                                <span>Born <strong><?php echo date("F j, Y", strtotime($user['birth_date'])); ?></strong></span>
                            </li>
                        <?php endif; ?>

                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <span>Joined <strong><?php echo date("F Y", strtotime($user['created_at'])); ?></strong></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- RIGHT COLUMN: Post Something & Posts List -->
            <div class="col-lg-8">

                <!-- Single Unified Create Post Card -->
                <?php if ($is_own_profile): ?>
                    <div class="card shadow-sm border-0 mb-4 rounded-3">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?php echo $avatar_src; ?>" 
                                     alt="Profile" 
                                     class="rounded-circle me-1 object-fit-cover" 
                                     width="40" 
                                     height="40"
                                     onerror="this.onerror=null; this.src='../img/default_profile.png';">

                                <input type="text" class="form-control rounded-pill bg-light border-0" placeholder="What's on your mind?" readonly data-bs-toggle="modal" data-bs-target="#uploadImageModal" style="cursor: pointer;">
                                
                                <!-- Image Icon Button to Select File -->
                                <label for="createPostFileInput" class="btn btn-light text-primary rounded-circle mb-0 p-2 border-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; cursor: pointer;">
                                    <i class="bi bi-image fs-5"></i>
                                </label>
                                <input type="file" id="createPostFileInput" accept="image/*" class="d-none">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Interactive Feed Container -->
                <div class="feed-container">
                    <?php if ($posts_result && $posts_result->num_rows > 0): ?>
                        <?php while ($post = $posts_result->fetch_assoc()): ?>
                            <?php 
                                $profile_pic = !empty($post['profile_picture']) ? $post['profile_picture'] : 'img/default_profile.png';
                                if (!str_starts_with($profile_pic, 'http') && !str_starts_with($profile_pic, '../')) {
                                    $profile_pic = '../' . $profile_pic;
                                }
                            ?>
                            <div class="card shadow-sm border-0 mb-3 rounded-3" style="overflow: visible;">
                                <div class="card-body p-3">
                                    
                                    <!-- Header with User Info and Action Dropdown -->
                                    <div class="d-flex align-items-center justify-content-between mb-3 position-relative">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                                                 alt="Profile" 
                                                 class="rounded-circle me-2 object-fit-cover" 
                                                 width="40" 
                                                 height="40"
                                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                            <div class="lh-sm">
                                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['fullname']); ?></h6>
                                                <small class="text-muted">@<?php echo htmlspecialchars($post['username']); ?> • <?php echo date('M d, Y h:i A', strtotime($post['created_at'])); ?></small>
                                            </div>
                                        </div>

                                        <!-- Dropdown options (Owner Options) -->
                                        <?php if ($logged_in_user_id && $logged_in_user_id == $post['user_id']): ?>
                                            <div class="dropdown me-1">
                                                <button class="btn btn-link text-secondary p-1 border-0 shadow-none rounded-circle" 
                                                        type="button" 
                                                        id="postDropdownMenu_<?php echo $post['post_id']; ?>" 
                                                        data-bs-toggle="dropdown" 
                                                        data-bs-display="static"
                                                        aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow" 
                                                    aria-labelledby="postDropdownMenu_<?php echo $post['post_id']; ?>" 
                                                    style="right: 0 !important; left: auto !important; z-index: 1050;">
                                                    <li>
                                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#editPostModal<?php echo $post['post_id']; ?>">
                                                            <i class="bi bi-pencil-square text-primary"></i> Edit
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item d-flex align-items-center gap-2 text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deletePostModal<?php echo $post['post_id']; ?>">
                                                            <i class="bi bi-trash3 text-danger"></i> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Post Text Content -->
                                    <?php if (!empty($post['post_txt'])): ?>
                                        <p class="card-text mb-3 text-break"><?php echo nl2br(htmlspecialchars($post['post_txt'])); ?></p>
                                    <?php endif; ?>

                                    <!-- Post Image Content -->
                                    <?php if (!empty($post['post_img'])): ?>
                                        <?php 
                                            $post_img_path = $post['post_img'];
                                            if (!str_starts_with($post_img_path, '../')) {
                                                $post_img_path = '../' . $post_img_path;
                                            }
                                        ?>
                                        <div class="mb-3">
                                            <img src="<?php echo htmlspecialchars($post_img_path); ?>" class="img-fluid rounded-3 w-100 object-fit-cover" style="max-height: 450px;" alt="Post content">
                                        </div>
                                    <?php endif; ?>

                                    <!-- Action Bar (Like, Comment, Save) -->
                                    <hr class="my-2 text-muted opacity-25">
                                    <div class="d-flex justify-content-between text-center pt-1">
                                        <!-- Like Button -->
                                        <form action="../Context/like.php" method="POST" class="flex-fill px-1">
                                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                            <button type="submit" class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi <?php echo $post['is_liked'] ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                <span><?php echo $post['likes_count']; ?> Like<?php echo $post['likes_count'] == 1 ? '' : 's'; ?></span>
                                            </button>
                                        </form>

                                        <!-- Comment Button -->
                                        <div class="flex-fill px-1">
                                            <button class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#commentsContainer<?php echo $post['post_id']; ?>" 
                                                    aria-expanded="false">
                                                <i class="bi bi-chat-left"></i>
                                                <span><?php echo $post['comments_count']; ?> Comment<?php echo $post['comments_count'] == 1 ? '' : 's'; ?></span>
                                            </button>
                                        </div>

                                        <!-- Save / Bookmark Button -->
                                        <form action="../Context/save_bookmark.php" method="POST" class="flex-fill px-1">
                                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                            <button type="submit" class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2">
                                                <i class="bi <?php echo $post['is_saved'] ? 'bi-bookmark-fill text-warning' : 'bi-bookmark'; ?>"></i>
                                                <span><?php echo $post['is_saved'] ? 'Saved' : 'Save'; ?></span>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Collapsible Comment Section -->
                                    <div class="collapse mt-3" id="commentsContainer<?php echo $post['post_id']; ?>">
                                        <div class="border-top pt-3">
                                            
                                            <!-- Comment Input Form -->
                                            <form action="../Context/comment.php" method="POST" class="d-flex gap-2 mb-3">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                <input type="text" name="comment_txt" class="form-control form-control-sm rounded-pill bg-light" placeholder="Write a comment..." required>
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Post</button>
                                            </form>

                                            <!-- Fetch Comments -->
                                            <?php
                                                $c_stmt = $conn->prepare("
                                                    SELECT c.id AS comment_id, c.user_id, c.comment_txt, c.created_at, u.fullname, u.profile_picture 
                                                    FROM comments c 
                                                    JOIN users u ON c.user_id = u.id 
                                                    WHERE c.post_id = ? 
                                                    ORDER BY c.created_at ASC
                                                ");
                                                $c_stmt->bind_param("i", $post['post_id']);
                                                $c_stmt->execute();
                                                $comments_res = $c_stmt->get_result();
                                            ?>

                                            <?php if ($comments_res && $comments_res->num_rows > 0): ?>
                                                <div class="d-flex flex-column gap-2">
                                                    <?php while ($comment = $comments_res->fetch_assoc()): ?>
                                                        <?php 
                                                            $c_avatar = !empty($comment['profile_picture']) ? $comment['profile_picture'] : 'img/default_profile.png';
                                                            if (!str_starts_with($c_avatar, 'http') && !str_starts_with($c_avatar, '../')) {
                                                                $c_avatar = '../' . $c_avatar;
                                                            }
                                                        ?>
                                                        <div class="d-flex align-items-start justify-content-between bg-light p-2 rounded-3">
                                                            <div class="d-flex align-items-start gap-2">
                                                                <img src="<?php echo htmlspecialchars($c_avatar); ?>" 
                                                                     class="rounded-circle object-fit-cover" 
                                                                     width="30" 
                                                                     height="30" 
                                                                     alt="User"
                                                                     onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                                                <div class="lh-sm">
                                                                    <span class="fw-bold d-block small"><?php echo htmlspecialchars($comment['fullname']); ?></span>
                                                                    <small class="text-dark"><?php echo htmlspecialchars($comment['comment_txt']); ?></small>
                                                                </div>
                                                            </div>

                                                            <!-- Comment Options for Owner -->
                                                            <?php if ($logged_in_user_id && $logged_in_user_id == $comment['user_id']): ?>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-link text-secondary p-0 border-0 shadow-none" 
                                                                            type="button" 
                                                                            id="commentDropdownMenu_<?php echo $comment['comment_id']; ?>" 
                                                                            data-bs-toggle="dropdown" 
                                                                            data-bs-display="static"
                                                                            aria-expanded="false">
                                                                        <i class="bi bi-three-dots fs-6"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm small" 
                                                                        aria-labelledby="commentDropdownMenu_<?php echo $comment['comment_id']; ?>"
                                                                        style="right: 0 !important; left: auto !important; z-index: 1050;">
                                                                        <li>
                                                                            <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#editCommentModal<?php echo $comment['comment_id']; ?>">
                                                                                <i class="bi bi-pencil-square text-primary"></i> Edit
                                                                            </button>
                                                                        </li>
                                                                        <li>
                                                                            <button class="dropdown-item d-flex align-items-center gap-2 text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteCommentModal<?php echo $comment['comment_id']; ?>">
                                                                                <i class="bi bi-trash3 text-danger"></i> Delete
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                </div>

                                                                <!-- Modal to Edit Comment -->
                                                                <div class="modal fade" id="editCommentModal<?php echo $comment['comment_id']; ?>" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                                        <div class="modal-content border-0 shadow">
                                                                            <div class="modal-header border-0 pb-0">
                                                                                <h6 class="modal-title fw-bold">Edit Comment</h6>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <form action="../Context/comment.php" method="POST">
                                                                                <div class="modal-body">
                                                                                    <input type="hidden" name="action" value="update">
                                                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                                                                    <input type="text" name="comment_txt" class="form-control form-control-sm bg-light" value="<?php echo htmlspecialchars($comment['comment_txt']); ?>" required>
                                                                                </div>
                                                                                <div class="modal-footer border-0 pt-0">
                                                                                    <button type="button" class="btn btn-light btn-sm rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-bold">Save</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Modal to Delete Comment -->
                                                                <div class="modal fade" id="deleteCommentModal<?php echo $comment['comment_id']; ?>" tabindex="-1" aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                                        <div class="modal-content border-0 shadow">
                                                                            <div class="modal-header border-0 pb-0">
                                                                                <h6 class="modal-title fw-bold text-danger">Delete Comment</h6>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body py-2">
                                                                                <p class="small text-muted mb-0">Are you sure you want to delete this comment? This action cannot be undone.</p>
                                                                            </div>
                                                                            <div class="modal-footer border-0 pt-0">
                                                                                <form action="../Context/comment.php" method="POST">
                                                                                    <input type="hidden" name="action" value="delete">
                                                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                                                                    <button type="button" class="btn btn-light btn-sm rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                                                    <button type="submit" class="btn btn-danger btn-sm rounded-pill fw-bold">Delete</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted small mb-0 text-center">No comments yet. Be the first to comment!</p>
                                            <?php endif; ?>
                                            <?php $c_stmt->close(); ?>

                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Edit Modal for Post Owner -->
                            <?php if ($logged_in_user_id && $logged_in_user_id == $post['user_id']): ?>
                                <div class="modal fade" id="editPostModal<?php echo $post['post_id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Edit Post</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="../Context/post_action.php" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                    <textarea name="post_txt" class="form-control border bg-light rounded-3 p-3" rows="3" style="resize: none;" required><?php echo htmlspecialchars($post['post_txt'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="action" value="update" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Modal for Post Owner -->
                                <div class="modal fade" id="deletePostModal<?php echo $post['post_id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold text-danger">Delete Post</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-2">
                                                <p class="text-muted small mb-0">Are you sure you want to delete this post? This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <form action="../Context/post_action.php" method="POST">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="action" value="delete" class="btn btn-danger rounded-pill px-4 fw-bold">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card shadow-sm border-0 p-4 text-center text-muted rounded-3">
                            <p class="m-0">No posts available right now.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>

    <!-- Upload Image Preview Modal -->
    <?php if ($is_own_profile): ?>
    <div class="modal fade" id="uploadImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Create Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../Context/post.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <textarea name="post_txt" class="form-control border bg-light rounded-3 p-3 mb-3" rows="3" placeholder="What's on your mind?" style="resize: none;" required></textarea>

                        <!-- Preview Container -->
                        <div id="modalImagePreviewContainer" class="text-center bg-light rounded-3 p-2 border overflow-hidden position-relative">
                            <img id="modalImagePreview" src="" class="img-fluid rounded-2 object-fit-cover" style="max-height: 350px; width: 100%; display: none;" alt="Preview">
                        </div>

                        <!-- Hidden File Input synced with the trigger -->
                        <input type="file" name="post_img" id="modalPostFileInput" class="d-none" accept="image/*">
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_post" class="btn btn-primary rounded-pill px-4 fw-bold">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT PROFILE MODAL -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 border-0">
                <form action="../Context/update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="editProfileModalLabel">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="fullname" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bio</label>
                            <textarea name="bio" class="form-control rounded-3" rows="3" placeholder="Tell something about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="City, Country">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Birth Date</label>
                            <input type="date" name="birth_date" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control rounded-3" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cover Photo</label>
                            <input type="file" name="cover_photo" class="form-control rounded-3" accept="image/*">
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap JavaScript Bundle & Event Handlers -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dropdown toggle handling
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

        // Close open dropdowns when a modal opens
        document.addEventListener('show.bs.modal', function () {
            const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
            openDropdowns.forEach(function (menu) {
                menu.classList.remove('show');
            });
        });

        // File Sync & Preview Handler between Create Post Card and Modal
        const outerFileInput = document.getElementById('createPostFileInput');
        const modalFileInput = document.getElementById('modalPostFileInput');
        const modalPreviewImg = document.getElementById('modalImagePreview');

        function displayPreview(file) {
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (modalPreviewImg) {
                        modalPreviewImg.src = e.target.result;
                        modalPreviewImg.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        if (outerFileInput && modalFileInput) {
            // When selecting a file from the card icon
            outerFileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    modalFileInput.files = this.files;
                    displayPreview(this.files[0]);
                    
                    const modalElement = document.getElementById('uploadImageModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modalInstance.show();
                    }
                }
            });

            // When selecting a file directly inside the modal
            modalFileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    displayPreview(this.files[0]);
                } else if (modalPreviewImg) {
                    modalPreviewImg.src = '';
                    modalPreviewImg.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>