<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$profile_picture = $_SESSION['profile_picture'] ?? '../img/default_profile.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Explore</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
</head>
<body class="bg-light">

    <?php
    // Include top navigation bar
    include_once "../Components/nav.php";
    ?>

    <div class="container-fluid py-4 px-md-4">
        <div class="row g-4">
            
            <!-- 1. LEFT COLUMN: Friends (3/12 width) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friends.php"; ?>
                </div>
            </div>

            <!-- 2. CENTER COLUMN: Explore Content Feed (6/12 width) -->
            <div class="col-12 col-lg-6">

                <br><br>

                <!-- Search Header Card -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3">
                        <form action="explore.php" method="GET" class="d-flex gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 ps-3 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="q" class="form-control bg-light border-0 py-2" 
                                       placeholder="Search trending posts, topics, or people..." 
                                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary px-4 rounded-3 fw-bold">Search</button>
                            <?php if (!empty($_GET['q'])): ?>
                                <a href="explore.php" class="btn btn-outline-secondary rounded-3 d-flex align-items-center">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Section Title -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0">
                        <?php if (!empty($_GET['q'])): ?>
                            Search Results for "<span class="text-primary"><?php echo htmlspecialchars($_GET['q']); ?></span>"
                        <?php else: ?>
                            <i class="bi bi-compass me-2 text-primary"></i>Explore Posts
                        <?php endif; ?>
                    </h5>
                </div>

                <!-- Explore Feed Content -->
                <div>
                    <?php include_once "../Components/explore_feed.php"; ?>
                </div>

            </div>

            <!-- 3. RIGHT COLUMN: Friend Requests (3/12 width) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friendrequest.php"; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>