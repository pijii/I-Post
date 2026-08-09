<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../Styles/style.css">

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

                        <!-- PHP Alert for Incorrect Credentials -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php 
                                    echo htmlspecialchars($_SESSION['error']); 
                                    unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

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

    <script src="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>

    <script>
        // Form Validation Script
        (() => {
            'use strict';
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        })();

        // Password Toggle Visibility Script
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (togglePasswordBtn && passwordInput && toggleIcon) {
            togglePasswordBtn.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleIcon.classList.toggle('bi-eye');
                toggleIcon.classList.toggle('bi-eye-slash');
            });
        }
    </script>
</body>
</html>