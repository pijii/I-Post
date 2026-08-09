<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection exists
if (!isset($conn)) {
    require_once "../Context/config.php";
}

$current_user_id = $_SESSION['user_id'] ?? null;

// Query to fetch pending incoming friend requests
$query = "
    SELECT 
        f.id AS request_id,
        u.id AS user_id,
        u.fullname,
        u.username,
        u.profile_picture
    FROM friends f
    JOIN users u ON f.user_id = u.id
    WHERE f.friend_user_id = ? AND f.status = 'pending'
    ORDER BY f.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$requests_result = $stmt->get_result();
?>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-3">
        <h6 class="card-title fw-bold text-secondary mb-3">Friend Requests</h6>

        <?php if ($requests_result && $requests_result->num_rows > 0): ?>
            <div class="d-flex flex-column gap-3">
                <?php while ($request = $requests_result->fetch_assoc()): ?>
                    <?php 
                        $avatar = !empty($request['profile_picture']) ? $request['profile_picture'] : '../img/default_profile.png';
                        
                        // Sanitize path if it's a relative local path
                        if (!str_starts_with($avatar, 'http') && !str_starts_with($avatar, '../') && !str_starts_with($avatar, './')) {
                            $avatar = '../' . $avatar;
                        }
                    ?>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center me-2">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" 
                                 alt="Profile" 
                                 class="rounded-circle me-2 object-fit-cover" 
                                 width="40" 
                                 height="40"
                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">
                            <div class="lh-1">
                                <span class="fw-bold d-block text-dark text-truncate" style="max-width: 110px;"><?php echo htmlspecialchars($request['fullname']); ?></span>
                                <small class="text-muted">@<?php echo htmlspecialchars($request['username']); ?></small>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <form action="../Context/confirm_friend.php" method="POST" class="d-inline">
                                <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                                <input type="hidden" name="target_user_id" value="<?php echo (int)$request['user_id']; ?>">
                                <button type="submit" name="action" value="accept_request" class="btn btn-primary btn-sm rounded-pill px-2 py-1">Confirm</button>
                                <button type="submit" name="action" value="decline_request" class="btn btn-light btn-sm rounded-pill px-2 py-1 text-secondary">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted small m-0">No pending friend requests.</p>
        <?php endif; ?>
        <?php $stmt->close(); ?>
    </div>
</div>