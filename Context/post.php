<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

// Ensure user is authenticated and form was submitted via POST
if (!isset($_SESSION['user_id']) || !isset($_POST['submit_post'])) {
    header("Location: ../Pages/dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_txt = trim($_POST['post_txt'] ?? '');
$post_img = null;

// Handle Image Upload Logic
if (isset($_FILES['post_img']) && $_FILES['post_img']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['post_img']['tmp_name'];
    $fileName = $_FILES['post_img']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../img/uploads/';

        // Ensure destination directory exists
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Save relative path for database storage
            $post_img = 'img/uploads/' . $newFileName;
        }
    }
}

// Insert into 'posts' database table if text or image is present
if (!empty($post_txt) || !empty($post_img)) {
    $stmt = $conn->prepare("INSERT INTO posts (user_id, post_txt, post_img, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $post_txt, $post_img);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to dashboard
header("Location: ../Pages/dashboard.php");
exit();