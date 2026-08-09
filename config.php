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
?>


<?php
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