<?php
session_start();
include "../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Retrieve and sanitize form inputs
    $fullname         = trim($_POST['fullname'] ?? '');
    $username         = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Server-side check for password matching (fallback safety)
    if ($password !== $confirm_password) {
        header("Location: ../Pages/signup.php?error=passwordmismatch");
        exit();
    }

    // 3. Check if username already exists in database
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            header("Location: ../Pages/signup.php?error=userexists");
            exit();
        }
        mysqli_stmt_close($stmt);
    }

    // 4. Encrypt/Hash the password securely (Bcrypt)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert new user record
    $sql = "INSERT INTO users (fullname, username, password_hash) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $fullname, $username, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            // Redirect to login page upon success
            header("Location: ../Pages/login.php?signup=success");
            exit();
        } else {
            mysqli_stmt_close($stmt);
            header("Location: ../Pages/signup.php?error=sqlerror");
            exit();
        }
    } else {
        header("Location: ../Pages/signup.php?error=sqlerror");
        exit();
    }

} else {
    // Redirect back if page is accessed without submitting form
    header("Location: ../Pages/signup.php");
    exit();
}
?>