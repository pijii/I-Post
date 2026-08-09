<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection exists
if (!isset($conn)) {
    require_once "../Context/config.php";
}

$current_user_id = $_SESSION['user_id'] ?? null;

// Query to fetch accepted friends
$query = "
    SELECT 
        u.id AS friend_id,
        u.fullname,
        u.username,
        u.profile_picture
    FROM friends f
    JOIN users u ON (f.friend_user_id = u.id OR f.user_id = u.id)
    WHERE (f.user_id = ? OR f.friend_user_id = ?) 
      AND u.id != ? 
      AND f.status = 'accepted'
    ORDER BY u.fullname ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$friends_result = $stmt->get_result();
?>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-3">
        <h6 class="card-title fw-bold text-secondary mb-3">Friends</h6>

        <?php if ($friends_result && $friends_result->num_rows > 0): ?>
            <div class="d-flex flex-column gap-2">
                <?php while ($friend = $friends_result->fetch_assoc()): ?>
                    <?php 
                        $avatar = !empty($friend['profile_picture']) ? $friend['profile_picture'] : '../img/default_profile.png';
                        
                        // Sanitize image path
                        if (!str_starts_with($avatar, 'http') && !str_starts_with($avatar, '../') && !str_starts_with($avatar, './')) {
                            $avatar = '../' . $avatar;
                        }
                    ?>
                    <a href="profile.php?id=<?php echo $friend['friend_id']; ?>" class="d-flex align-items-center text-decoration-none p-1 rounded-2 hover-bg-light">
                        <img src="<?php echo htmlspecialchars($avatar); ?>" 
                             alt="Profile" 
                             class="rounded-circle me-2 object-fit-cover" 
                             width="36" 
                             height="36"
                             onerror="this.onerror=null; this.src='../img/default_profile.png';">
                        <div class="lh-1">
                            <span class="fw-bold d-block text-dark small text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($friend['fullname']); ?></span>
                            <small class="text-muted" style="font-size: 0.75rem;">@<?php echo htmlspecialchars($friend['username']); ?></small>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted small m-0">No friends added yet.</p>
        <?php endif; ?>
        <?php $stmt->close(); ?>
    </div>
</div>