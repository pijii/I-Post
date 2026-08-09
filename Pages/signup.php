<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../Styles/style.css">

    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post Sign Up</title>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <!-- TOP POP-UP SUCCESS NOTIFICATION MODAL (Fixed Positioning) -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="successToast" class="toast toast-success-custom hide align-items-center border-0 p-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Account Created!</h6>
                        <small class="text-muted">Redirecting you to login...</small>
                    </div>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                
                <div class="card login-card p-4 p-md-5">
                    <div class="card-body">
                        
                        <!-- Logo Header -->
                        <div class="text-center mb-4">
                            <img src="../img/iPost_logo.png" alt="IPost Logo" class="brand-logo mb-3" width="150">
                            <h3 class="fw-bold mb-1">Create Account</h3>
                            <p class="text-muted fs-7">Please fill in your details to get started</p>
                        </div>

                        <!-- Registration Form -->
                        <form id="signupForm" action="../Context/register.php" method="POST">
                            
                            <!-- Full Name Input -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" placeholder="John Doe" name="fullname" id="fullname" required>
                                <label for="fullname">Full Name</label>
                            </div>

                            <!-- Username Input -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" placeholder="username" name="username" id="username" required>
                                <label for="username">Username</label>
                            </div>

                            <!-- Password Input -->
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                <label for="password">Password</label>
                            </div>

                            <!-- Confirm Password Input -->
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
                                <label for="confirmPassword">Confirm Password</label>
                            </div>

                            <!-- Terms & Conditions Checkbox -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="termsCheck" name="terms" required>
                                <label class="form-check-label text-secondary fs-6" for="termsCheck">
                                    I agree to the <a href="#" class="text-decoration-none">Terms &amp; Conditions</a>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button class="btn btn-primary btn-outline-secondary w-100 py-2 mb-2 text-white" type="submit">Create Account</button>

                            <!-- Error Message Container Under Button -->
                            <div id="errorMessage" class="text-danger text-center fs-7 fw-semibold mt-2" style="display: none;">
                                Passwords do not match.
                            </div>

                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-transparent border-0 text-center pt-0">
                        <p class="text-muted mb-0 fs-6">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Sign in</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>

    <script src="../Scripts/signup.js"></script>
</body>
</html>