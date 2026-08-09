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
    <title>I-Post | Dashboard</title>
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

            <!-- 2. CENTER COLUMN: Inline Post Creation Form + Feed (6/12 width) -->
            <div class="col-12 col-lg-6">

                <br><br>
                
                <!-- Single Unified Create Post Card -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                                 alt="Profile" 
                                 class="rounded-circle me-1 object-fit-cover" 
                                 width="40" 
                                 height="40"
                                 onerror="this.onerror=null; this.src='../img/default_profile.png';">

                            <input type="text" class="form-control rounded-pill bg-light border-0" placeholder="What's on your mind?" readonly data-bs-toggle="modal" data-bs-target="#uploadImageModal" style="cursor: pointer;">
                            
                            <!-- Image Icon Button to Select File -->
                            <label for="createPostFileInput" class="btn btn-light text-primary rounded-circle mb-0 p-2 border-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; cursor: pointer;">
                                <i class="bi bi-image fs-5"></i>
                            </label>
                            <input type="file" id="createPostFileInput" accept="image/*" class="d-none">
                        </div>
                    </div>
                </div>

                <!-- Feed Section -->
                <div>
                    <?php include_once "../Components/feed.php"; ?>
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

    <!-- Upload Image Preview Modal -->
    <div class="modal fade" id="uploadImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Create Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../Context/post.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <textarea name="post_txt" class="form-control border bg-light rounded-3 p-3 mb-3" rows="3" placeholder="What's on your mind?" style="resize: none;" required></textarea>

                        <!-- Preview Container -->
                        <div id="modalImagePreviewContainer" class="text-center bg-light rounded-3 p-2 border overflow-hidden position-relative">
                            <img id="modalImagePreview" src="" class="img-fluid rounded-2 object-fit-cover" style="max-height: 350px; width: 100%; display: none;" alt="Preview">
                        </div>

                        <!-- Hidden File Input synced with the trigger -->
                        <input type="file" name="post_img" id="modalPostFileInput" class="d-none" accept="image/*">
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_post" class="btn btn-primary rounded-pill px-4 fw-bold">Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script to handle photo picking & auto-opening the modal -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const createFileInput = document.getElementById('createPostFileInput');
        const modalFileInput = document.getElementById('modalPostFileInput');
        const previewImg = document.getElementById('modalImagePreview');
        const uploadModalElem = document.getElementById('uploadImageModal');

        if (createFileInput) {
            createFileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    modalFileInput.files = dataTransfer.files;

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        previewImg.src = event.target.result;
                        previewImg.style.display = 'block';

                        const modalInstance = bootstrap.Modal.getOrCreateInstance(uploadModalElem);
                        modalInstance.show();
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (uploadModalElem) {
            uploadModalElem.addEventListener('hidden.bs.modal', function () {
                if (previewImg) {
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                }
                if (createFileInput) createFileInput.value = '';
                if (modalFileInput) modalFileInput.value = '';
            });
        }
    });
    </script>

    <!-- Bootstrap JavaScript Bundle (Required for Dropdowns, Modals, and Collapsibles) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>