<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Pages/signup.php");
    exit;
}

require_once "../config.php";

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;

    // Fetch current user details for image cleanup/fallbacks
    $stmt_curr = $conn->prepare("SELECT profile_picture, cover_photo FROM users WHERE id = ?");
    $stmt_curr->bind_param("i", $user_id);
    $stmt_curr->execute();
    $current_user = $stmt_curr->get_result()->fetch_assoc();

    $profile_picture_path = $current_user['profile_picture'];
    $cover_photo_path = $current_user['cover_photo'];

    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle Profile Picture Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $upload_dir . $new_filename;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $profile_picture_path = "uploads/" . $new_filename;
                $_SESSION['profile_picture'] = $profile_picture_path;
            }
        }
    }

    // Handle Cover Photo Upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['cover_photo']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['cover_photo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = "cover_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $upload_dir . $new_filename;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $cover_photo_path = "uploads/" . $new_filename;
            }
        }
    }

    // Update database record
    $update_query = "
        UPDATE users 
        SET fullname = ?, bio = ?, location = ?, birth_date = ?, profile_picture = ?, cover_photo = ?
        WHERE id = ?
    ";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("ssssssi", $fullname, $bio, $location, $birth_date, $profile_picture_path, $cover_photo_path, $user_id);

    if ($stmt_update->execute()) {
        $_SESSION['fullname'] = $fullname;
        header("Location: ../Pages/profile.php?updated=success");
        exit;
    } else {
        header("Location: ../Pages/profile.php?error=failed");
        exit;
    }
}