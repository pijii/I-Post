<?php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "i-post"
);

if(!$conn){
    die("db is connected");

}

function resolveUserImagePath($path, $default = '../img/default_profile.png') {
    if (empty($path)) {
        return $default;
    }

    $normalized = trim(str_replace('\\', '/', $path));
    $normalized = preg_replace('#^img/uploads/#', 'uploads/', $normalized);
    $normalized = preg_replace('#^uploads/uploads/#', 'uploads/', $normalized);

    if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
        return $normalized;
    }

    $project_root = __DIR__;
    $basename = basename($normalized);
    $root_rel = ltrim($normalized, '/');

    $candidates = [
        $normalized,
        '../' . $root_rel,
        './' . $root_rel,
        '../uploads/' . $basename,
        $project_root . '/uploads/' . $basename,
        $project_root . '/' . $root_rel,
    ];

    if (str_starts_with($normalized, 'uploads/')) {
        $candidates[] = '../' . $normalized;
    }

    if (str_starts_with($normalized, 'img/')) {
        $candidates[] = '../uploads/' . $basename;
    }

    $seen = [];
    foreach ($candidates as $candidate) {
        $key = trim((string)$candidate);
        if ($key === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;

        if (str_starts_with($key, 'http://') || str_starts_with($key, 'https://')) {
            continue;
        }

        $abs = $project_root . '/' . ltrim($key, '/');
        if (str_starts_with($key, '../') || str_starts_with($key, './')) {
            $abs = $project_root . '/' . ltrim(substr($key, 3), '/');
        }

        if (str_starts_with($key, 'uploads/')) {
            $abs = $project_root . '/' . $key;
            if (file_exists($abs) && is_file($abs)) {
                return '../' . $key;
            }
        }

        if (file_exists($abs) && is_file($abs)) {
            if (str_starts_with($key, '../') || str_starts_with($key, './')) {
                return $key;
            }
            if (str_starts_with($key, 'uploads/')) {
                return '../' . $key;
            }
            return '../' . $root_rel;
        }

        $uploads_abs = $project_root . '/uploads/' . $basename;
        if (file_exists($uploads_abs) && is_file($uploads_abs)) {
            return '../uploads/' . $basename;
        }
    }

    return $default;
}

// Function to add a notification to the database
function createNotification($conn, $userId, $actorId, $type, $postId = null, $commentId = null) {
    // Prevent users from receiving notifications for their own actions
    if ($userId == $actorId) {
        return false;
    }

    $query = "INSERT INTO notifications (user_id, actor_id, post_id, comment_id, type, is_read, created_at) 
              VALUES (?, ?, ?, ?, ?, 0, NOW())";
              
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("iiiis", $userId, $actorId, $postId, $commentId, $type);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    return false;
}
?>