<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection exists
if (!isset($conn)) {
    require_once "../Context/config.php";
}

$current_user_id = $_SESSION['user_id'] ?? null;
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// ---------------------------------------------------------------------
// 1. FETCH EXPLORE PEOPLE (User Suggestions or Search Matching Users)
// ---------------------------------------------------------------------
$explore_users = [];
if (!empty($search_query)) {
    $u_search = "%" . $search_query . "%";
    $u_sql = "
        SELECT 
            u.id, 
            u.fullname, 
            u.username, 
            u.profile_picture,
            f.status AS friend_status,
            f.user_id AS friend_action_user_id
        FROM users u
        LEFT JOIN friends f ON 
            ((f.user_id = ? AND f.friend_user_id = u.id) OR (f.user_id = u.id AND f.friend_user_id = ?))
        WHERE (u.fullname LIKE ? OR u.username LIKE ?) AND u.id != ?
        LIMIT 5
    ";
    $u_stmt = $conn->prepare($u_sql);
    $u_stmt->bind_param("iissi", $current_user_id, $current_user_id, $u_search, $u_search, $current_user_id);
    $u_stmt->execute();
    $explore_users = $u_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $u_stmt->close();
} else if ($current_user_id) {
    // Default: Suggest random users excluding current user
    $u_sql = "
        SELECT 
            u.id, 
            u.fullname, 
            u.username, 
            u.profile_picture,
            f.status AS friend_status,
            f.user_id AS friend_action_user_id
        FROM users u
        LEFT JOIN friends f ON 
            ((f.user_id = ? AND f.friend_user_id = u.id) OR (f.user_id = u.id AND f.friend_user_id = ?))
        WHERE u.id != ? 
        ORDER BY RAND() 
        LIMIT 5
    ";
    $u_stmt = $conn->prepare($u_sql);
    $u_stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
    $u_stmt->execute();
    $explore_users = $u_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $u_stmt->close();
}

// ---------------------------------------------------------------------
// 2. FETCH POSTS (Filtered by search query OR sorted by popularity)
// ---------------------------------------------------------------------
if (!empty($search_query)) {
    $searchTerm = "%" . $search_query . "%";
    $posts_sql = "
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
        WHERE p.post_txt LIKE ? OR u.fullname LIKE ? OR u.username LIKE ?
        ORDER BY p.created_at DESC
    ";
    $stmt = $conn->prepare($posts_sql);
    $stmt->bind_param("iiSSS", $current_user_id, $current_user_id, $searchTerm, $searchTerm, $searchTerm);
} else {
    // Default Explore Feed: Posts ranked by popularity (likes + comments) and recency
    $posts_sql = "
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
        ORDER BY (likes_count + comments_count) DESC, p.created_at DESC
    ";
    $stmt = $conn->prepare($posts_sql);
    $stmt->bind_param("ii", $current_user_id, $current_user_id);
}

$stmt->execute();
$posts_result = $stmt->get_result();
?>

<div class="explore-feed-container">

    <!-- ================================================================= -->
    <!-- EXPLORE PEOPLE SECTION                                            -->
    <!-- ================================================================= -->
    <?php if (!empty($explore_users)): ?>
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill text-primary"></i> 
                    <?php echo !empty($search_query) ? 'People Matching Search' : 'People You May Know'; ?>
                </h6>
                
                <div class="d-flex gap-3 overflow-x-auto pb-2" style="scrollbar-width: thin;">
                    <?php foreach ($explore_users as $person): ?>
                        <?php 
                            $person_avatar = !empty($person['profile_picture']) ? $person['profile_picture'] : '../img/default_profile.png';
                            if (!str_starts_with($person_avatar, 'http') && !str_starts_with($person_avatar, '../') && !str_starts_with($person_avatar, './')) {
                                $person_avatar = '../' . $person_avatar;
                            }
                            
                            $status = $person['friend_status'] ?? null;
                            $action_user = $person['friend_action_user_id'] ?? null;
                        ?>
                        <div class="card border border-light bg-light rounded-3 p-3 text-center flex-shrink-0" style="width: 140px;">
                            <img src="<?php echo htmlspecialchars($person_avatar); ?>" 
                                 class="rounded-circle object-fit-cover mx-auto mb-2" 
                                 width="50" 
                                 height="50" 
                                 alt="User"
                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                            
                            <span class="fw-bold text-dark d-block text-truncate small" title="<?php echo htmlspecialchars($person['fullname']); ?>">
                                <?php echo htmlspecialchars($person['fullname']); ?>
                            </span>
                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.75rem;">
                                @<?php echo htmlspecialchars($person['username']); ?>
                            </small>

                            <!-- Dynamic Friend Action Button -->
                            <form action="../Context/friend_action.php" method="POST">
                                <input type="hidden" name="target_user_id" value="<?php echo $person['id']; ?>">
                                <?php if ($status === 'accepted'): ?>
                                    <button type="submit" name="action" value="unfriend" class="btn btn-outline-secondary btn-sm rounded-pill w-100 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-check-fill"></i> Friends
                                    </button>
                                <?php elseif ($status === 'pending' && $action_user == $current_user_id): ?>
                                    <button type="submit" name="action" value="cancel_request" class="btn btn-secondary btn-sm rounded-pill w-100 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock-history"></i> Pending
                                    </button>
                                <?php elseif ($status === 'pending' && $action_user != $current_user_id): ?>
                                    <button type="submit" name="action" value="accept_request" class="btn btn-success btn-sm rounded-pill w-100 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-check"></i> Accept
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="add_friend" class="btn btn-primary btn-sm rounded-pill w-100 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-plus-fill"></i> Add
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ================================================================= -->
    <!-- EXPLORE POSTS FEED SECTION                                        -->
    <!-- ================================================================= -->
    <?php if ($posts_result && $posts_result->num_rows > 0): ?>
        <?php while ($post = $posts_result->fetch_assoc()): ?>
            <?php 
                $profile_pic = !empty($post['profile_picture']) ? $post['profile_picture'] : '../img/default_profile.png';
                if (!str_starts_with($profile_pic, 'http') && !str_starts_with($profile_pic, '../') && !str_starts_with($profile_pic, './')) {
                    $profile_pic = '../' . $profile_pic;
                }
            ?>
            <div class="card shadow-sm border-0 mb-3 rounded-3" style="overflow: visible;">
                <div class="card-body p-3">
                    
                    <!-- Post Header -->
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

                        <!-- Options Dropdown for Post Owner -->
                        <?php if ($current_user_id && $current_user_id == $post['user_id']): ?>
                            <div class="dropdown me-1">
                                <button class="btn btn-link text-secondary p-1 border-0 shadow-none rounded-circle" 
                                        type="button" 
                                        id="exploreDropdownMenu_<?php echo $post['post_id']; ?>" 
                                        data-bs-toggle="dropdown" 
                                        data-bs-display="static"
                                        aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow" 
                                    aria-labelledby="exploreDropdownMenu_<?php echo $post['post_id']; ?>" 
                                    style="right: 0 !important; left: auto !important; z-index: 1050;">
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-toggle="modal" data-bs-target="#editPostModal<?php echo $post['post_id']; ?>">
                                            <i class="bi bi-pencil-square text-primary"></i> Edit Post
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center gap-2 text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deletePostModal<?php echo $post['post_id']; ?>">
                                            <i class="bi bi-trash3 text-danger"></i> Delete Post
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
                            if (!str_starts_with($post_img_path, '../') && !str_starts_with($post_img_path, './') && !str_starts_with($post_img_path, 'http')) {
                                $post_img_path = '../' . $post_img_path;
                            }
                        ?>
                        <div class="mb-3 text-center bg-light rounded-3 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($post_img_path); ?>" class="img-fluid rounded-3 w-100 object-fit-cover" style="max-height: 450px;" alt="Post Image">
                        </div>
                    <?php endif; ?>

                    <!-- Action Bar -->
                    <hr class="my-2 text-muted opacity-25">
                    <div class="d-flex justify-content-between text-center pt-1">
                        <!-- Like Form -->
                        <form action="../Context/like.php" method="POST" class="flex-fill px-1">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi <?php echo $post['is_liked'] ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                <span><?php echo $post['likes_count']; ?> Like<?php echo $post['likes_count'] == 1 ? '' : 's'; ?></span>
                            </button>
                        </form>

                        <!-- Comment Toggle -->
                        <div class="flex-fill px-1">
                            <button class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#exploreComments<?php echo $post['post_id']; ?>" 
                                    aria-expanded="false">
                                <i class="bi bi-chat-left"></i>
                                <span><?php echo $post['comments_count']; ?> Comment<?php echo $post['comments_count'] == 1 ? '' : 's'; ?></span>
                            </button>
                        </div>

                        <!-- Bookmark Form -->
                        <form action="../Context/save_bookmark.php" method="POST" class="flex-fill px-1">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" class="btn btn-light btn-sm w-100 border-0 text-secondary fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi <?php echo $post['is_saved'] ? 'bi-bookmark-fill text-warning' : 'bi-bookmark'; ?>"></i>
                                <span><?php echo $post['is_saved'] ? 'Saved' : 'Save'; ?></span>
                            </button>
                        </form>
                    </div>

                    <!-- Collapsible Comment Section -->
                    <div class="collapse mt-3" id="exploreComments<?php echo $post['post_id']; ?>">
                        <div class="border-top pt-3">
                            
                            <!-- Comment Input Form -->
                            <form action="../Context/comment.php" method="POST" class="d-flex gap-2 mb-3">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                <input type="text" name="comment_txt" class="form-control form-control-sm rounded-pill bg-light border-0 px-3" placeholder="Write a comment..." required>
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Post</button>
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
                                            $c_avatar = !empty($comment['profile_picture']) ? $comment['profile_picture'] : '../img/default_profile.png';
                                            if (!str_starts_with($c_avatar, 'http') && !str_starts_with($c_avatar, '../') && !str_starts_with($c_avatar, './')) {
                                                $c_avatar = '../' . $c_avatar;
                                            }
                                        ?>
                                        <div class="d-flex align-items-start justify-content-between bg-light p-2 rounded-3">
                                            <div class="d-flex align-items-start gap-2">
                                                <img src="<?php echo htmlspecialchars($c_avatar); ?>" 
                                                     class="rounded-circle object-fit-cover me-1" 
                                                     width="32" 
                                                     height="32" 
                                                     alt="User"
                                                     onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                                <div class="lh-sm">
                                                    <span class="fw-bold d-block small text-dark"><?php echo htmlspecialchars($comment['fullname']); ?></span>
                                                    <small class="text-secondary"><?php echo htmlspecialchars($comment['comment_txt']); ?></small>
                                                </div>
                                            </div>

                                            <!-- Comment Options for Owner -->
                                            <?php if ($current_user_id && $current_user_id == $comment['user_id']): ?>
                                                <div class="dropdown">
                                                    <button class="btn btn-link text-secondary p-0 border-0 shadow-none" 
                                                            type="button" 
                                                            id="commentDropdown_<?php echo $comment['comment_id']; ?>" 
                                                            data-bs-toggle="dropdown" 
                                                            aria-expanded="false">
                                                        <i class="bi bi-three-dots fs-6"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm small" 
                                                        aria-labelledby="commentDropdown_<?php echo $comment['comment_id']; ?>">
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

                                                <!-- Edit Comment Modal -->
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
                                                                    <input type="text" name="comment_txt" class="form-control form-control-sm bg-light border-0" value="<?php echo htmlspecialchars($comment['comment_txt']); ?>" required>
                                                                </div>
                                                                <div class="modal-footer border-0 pt-0">
                                                                    <button type="button" class="btn btn-light btn-sm rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-bold">Save</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Delete Comment Modal -->
                                                <div class="modal fade" id="deleteCommentModal<?php echo $comment['comment_id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header border-0 pb-0">
                                                                <h6 class="modal-title fw-bold text-danger">Delete Comment</h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body py-2">
                                                                <p class="small text-muted mb-0">Delete this comment permanently?</p>
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
                                <p class="text-muted small mb-0 text-center">No comments yet. Start the conversation!</p>
                            <?php endif; ?>
                            <?php $c_stmt->close(); ?>

                        </div>
                    </div>

                </div>
            </div>

            <!-- Modals for Post Owner -->
            <?php if ($current_user_id && $current_user_id == $post['user_id']): ?>
                <!-- Edit Post Modal -->
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
                                    <textarea name="post_txt" class="form-control border bg-light rounded-3 p-3" rows="3" style="resize: none;" required><?php echo htmlspecialchars($post['post_txt']); ?></textarea>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="action" value="update" class="btn btn-primary rounded-pill px-4 fw-bold">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Post Modal -->
                <div class="modal fade" id="deletePostModal<?php echo $post['post_id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-danger">Delete Post</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-2">
                                <p class="text-muted small mb-0">Are you sure you want to delete this post?</p>
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
        <div class="card shadow-sm border-0 p-5 text-center text-muted rounded-3">
            <i class="bi bi-search fs-1 mb-2 text-secondary"></i>
            <h5>No posts found</h5>
            <p class="m-0 small">Try searching for other keywords or check back later.</p>
        </div>
    <?php endif; ?>
    <?php $stmt->close(); ?>
</div>