<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Check if request was submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header("Location: ../Pages/login.php");
        exit;
    }

    // Query checking ONLY the username column
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            // Verify password hash
            if (password_verify($password, $row['password_hash'])) {

                // Secure session handling
                session_regenerate_id(true);

                $_SESSION['user_id']  = $row['id'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['profile_picture'] = $row['profile_picture'] ?? '';

                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                header("Location: ../Pages/dashboard.php");
                exit;

            } else {
                // Incorrect password
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                
                $_SESSION['error'] = 'Invalid username or password.';
                header("Location: ../Pages/login.php");
                exit;
            }

        } else {
            // Username not found
            mysqli_stmt_close($stmt);
            mysqli_close($conn);

            $_SESSION['error'] = 'Invalid username or password.';
            header("Location: ../Pages/login.php");
            exit;
        }

    } else {
        $_SESSION['error'] = 'Database query failed. Please try again.';
        header("Location: ../Pages/login.php");
        exit;
    }

} else {
    header("Location: ../Pages/login.php");
    exit;
}
?>