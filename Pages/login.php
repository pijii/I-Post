<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$login_error_message = '';
if (isset($_SESSION['error'])) {
    $login_error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/site.css">

    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post Login</title>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                
                <div class="card login-card p-4 p-md-5">
                    <div class="card-body">
                        
                        <!-- Logo Header -->
                        <div class="text-center mb-4">
                            <img src="../img/iPost_logo.png" alt="IPost Logo" class="brand-logo mb-3" width="150">
                            <h3 class="fw-bold mb-1">Welcome Back</h3>
                            <p class="text-muted fs-7">Please sign in to continue</p>
                        </div>

                        <!-- Login Form -->
                        <form id="loginForm" class="needs-validation" action="../Context/authenticate.php" method="POST" novalidate>
                            
                            <!-- Username Input -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" placeholder="Username" name="username" id="username" required>
                                <label for="username">Username</label>
                                <div class="invalid-feedback">
                                    Please enter your username.
                                </div>
                            </div>

                            <!-- Password Input with Toggle -->
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                <label for="password">Password</label>
                                <button class="btn btn-link text-decoration-none text-secondary position-absolute top-50 end-0 translate-middle-y me-2 z-3" type="button" id="togglePassword">
                                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label text-secondary fs-6" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none fs-6">Forgot password?</a>
                            </div>

                            <!-- Submit Button -->
                            <button class="btn btn-primary w-100 py-2 mb-3 text-white" type="submit">Sign In</button>

                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-transparent border-0 text-center pt-0">
                        <p class="text-muted mb-0 fs-6">Don't have an account? <a href="signup.php" class="text-decoration-none fw-semibold">Sign up</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">Login Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="mb-0 text-muted"><?php echo htmlspecialchars($login_error_message ?: 'Incorrect username or password.'); ?></p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
    <script src="../Scripts/auth.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($login_error_message)): ?>
                const loginErrorModal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
                loginErrorModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>