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

// Search Query Processing
$search_term = trim($_GET['search'] ?? '');
$search_param = '%' . $search_term . '%';

// 1. Fetch Existing Friends List (Filtered by search if provided)
if (!empty($search_term)) {
    $friends_query = "
        SELECT 
            u.id AS friend_id,
            u.fullname,
            u.username,
            u.profile_picture
        FROM users u
        INNER JOIN friends f 
            ON (f.user_id = ? AND f.friend_user_id = u.id) 
            OR (f.friend_user_id = ? AND f.user_id = u.id)
        WHERE f.status = 'accepted'
          AND (u.fullname LIKE ? OR u.username LIKE ?)
    ";
    $stmt_friends = $conn->prepare($friends_query);
    $stmt_friends->bind_param("iiss", $user_id, $user_id, $search_param, $search_param);
} else {
    $friends_query = "
        SELECT 
            u.id AS friend_id,
            u.fullname,
            u.username,
            u.profile_picture
        FROM users u
        INNER JOIN friends f 
            ON (f.user_id = ? AND f.friend_user_id = u.id) 
            OR (f.friend_user_id = ? AND f.user_id = u.id)
        WHERE f.status = 'accepted'
    ";
    $stmt_friends = $conn->prepare($friends_query);
    $stmt_friends->bind_param("ii", $user_id, $user_id);
}
$stmt_friends->execute();
$friends_list = $stmt_friends->get_result();

// 2. Fetch "People You May Know" (Filtered by search if provided)
if (!empty($search_term)) {
    $suggestions_query = "
        SELECT 
            u.id AS user_id,
            u.fullname,
            u.username,
            u.profile_picture,
            (
                SELECT status FROM friends 
                WHERE (user_id = ? AND friend_user_id = u.id) 
                   OR (user_id = u.id AND friend_user_id = ?) 
                LIMIT 1
            ) AS request_status
        FROM users u
        WHERE u.id != ? 
          AND (u.fullname LIKE ? OR u.username LIKE ?)
          AND u.id NOT IN (
              SELECT friend_user_id FROM friends WHERE user_id = ? AND status = 'accepted'
              UNION
              SELECT user_id FROM friends WHERE friend_user_id = ? AND status = 'accepted'
          )
        LIMIT 12
    ";
    $stmt_suggest = $conn->prepare($suggestions_query);
    $stmt_suggest->bind_param("iiissii", $user_id, $user_id, $user_id, $search_param, $search_param, $user_id, $user_id);
} else {
    $suggestions_query = "
        SELECT 
            u.id AS user_id,
            u.fullname,
            u.username,
            u.profile_picture,
            (
                SELECT status FROM friends 
                WHERE (user_id = ? AND friend_user_id = u.id) 
                   OR (user_id = u.id AND friend_user_id = ?) 
                LIMIT 1
            ) AS request_status
        FROM users u
        WHERE u.id != ? 
          AND u.id NOT IN (
              SELECT friend_user_id FROM friends WHERE user_id = ? AND status = 'accepted'
              UNION
              SELECT user_id FROM friends WHERE friend_user_id = ? AND status = 'accepted'
          )
        LIMIT 12
    ";
    $stmt_suggest = $conn->prepare($suggestions_query);
    $stmt_suggest->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
}
$stmt_suggest->execute();
$suggested_users = $stmt_suggest->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Friends</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
</head>
<body class="bg-light">

    <?php include_once "../Components/nav.php"; ?>

    <div class="container-fluid py-4 px-md-4">
        <div class="row g-4">
            
            <!-- 1. LEFT COLUMN: Friends (3/12 width) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friends.php"; ?>
                </div>
            </div>

            <!-- 2. CENTER COLUMN: Search + Friends + Suggestions (6/12 width) -->
            <div class="col-12 col-lg-6">

                <br><br>

                <!-- Top Search Bar Card -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3">
                        <form action="friends.php" method="GET" class="d-flex align-items-center gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 ps-3 text-secondary">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control bg-light border-0 py-2 shadow-none" 
                                       placeholder="Search for someone by name or username..." 
                                       value="<?php echo htmlspecialchars($search_term); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold">Search</button>
                            <?php if (!empty($search_term)): ?>
                                <a href="friends.php" class="btn btn-light rounded-pill px-3 text-secondary" title="Clear search">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- SECTION 1: Existing Friends -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill text-primary fs-4"></i>
                                <h5 class="fw-bold mb-0 text-dark">Your Friends</h5>
                            </div>
                            <span class="badge bg-light text-secondary border rounded-pill px-3 py-2">
                                <?php echo $friends_list ? $friends_list->num_rows : 0; ?> Friends
                            </span>
                        </div>

                        <?php if ($friends_list && $friends_list->num_rows > 0): ?>
                            <div class="row row-cols-2 row-cols-sm-3 g-3">
                                <?php while ($friend = $friends_list->fetch_assoc()): ?>
                                    <div class="col">
                                        <div class="card h-100 border-0 bg-light rounded-3 text-center p-3">
                                            <img src="<?php echo getAvatar($friend['profile_picture']); ?>" 
                                                 alt="Profile" 
                                                 class="rounded-circle mx-auto mb-2 object-fit-cover border" 
                                                 style="width: 65px; height: 65px;"
                                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                            <h6 class="fw-bold mb-0 text-dark text-truncate"><?php echo htmlspecialchars($friend['fullname']); ?></h6>
                                            <small class="text-muted text-truncate d-block mb-3">@<?php echo htmlspecialchars($friend['username']); ?></small>
                                            
                                            <div class="mt-auto">
                                                <a href="profile.php?id=<?php echo $friend['friend_id']; ?>" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-semibold fs-7">
                                                    <i class="bi bi-person-fill me-1"></i> Profile
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-people text-muted display-6 mb-2 d-block"></i>
                                <p class="text-muted mb-0">
                                    <?php echo !empty($search_term) ? 'No friends matched your search.' : 'You don\'t have any friends added yet.'; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SECTION 2: People You May Know -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-person-plus-fill text-primary fs-4"></i>
                            <h5 class="fw-bold mb-0 text-dark">People You May Know</h5>
                        </div>

                        <?php if ($suggested_users && $suggested_users->num_rows > 0): ?>
                            <div class="row row-cols-2 row-cols-sm-3 g-3">
                                <?php while ($suggested = $suggested_users->fetch_assoc()): ?>
                                    <div class="col">
                                        <div class="card h-100 border-0 bg-light rounded-3 text-center p-3">
                                            <img src="<?php echo getAvatar($suggested['profile_picture']); ?>" 
                                                 alt="Profile" 
                                                 class="rounded-circle mx-auto mb-2 object-fit-cover border" 
                                                 style="width: 65px; height: 65px;"
                                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                                            <h6 class="fw-bold mb-0 text-dark text-truncate"><?php echo htmlspecialchars($suggested['fullname']); ?></h6>
                                            <small class="text-muted text-truncate d-block mb-3">@<?php echo htmlspecialchars($suggested['username']); ?></small>
                                            
                                            <div class="mt-auto">
                                                <?php if ($suggested['request_status'] === 'pending'): ?>
                                                    <button class="btn btn-secondary btn-sm w-100 rounded-pill fw-semibold fs-7" disabled>
                                                        <i class="bi bi-clock me-1"></i> Pending
                                                    </button>
                                                <?php else: ?>
                                                    <form action="../Context/add_friend.php" method="POST">
                                                        <input type="hidden" name="receiver_id" value="<?php echo $suggested['user_id']; ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold fs-7">
                                                            <i class="bi bi-person-plus-fill me-1"></i> Add Friend
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">
                                    <?php echo !empty($search_term) ? 'No suggestions matched your search.' : 'No new user suggestions available right now.'; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

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
</body>
</html>