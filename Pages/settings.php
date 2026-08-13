<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit;
}

require_once "../config.php";

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT fullname, bio, location, birth_date, profile_picture, cover_photo FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

function getImageUrl($path, $default) {
    return resolveUserImagePath($path, $default);
}

$avatar_src = getImageUrl($user['profile_picture'] ?? '', '../img/default_profile.png');
$cover_src = getImageUrl($user['cover_photo'] ?? '', '../img/default_cover.jpg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/iPost_logo.png">
    <title>I-Post | Settings</title>
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/nav.css">
    <style>
        .cover-banner {
            height: 240px;
            background-size: cover;
            background-position: center;
            border-radius: 0.75rem 0.75rem 0 0;
        }
    </style>
</head>
<body class="bg-light">

    <?php include_once "../Components/nav.php"; ?>

    <div class="container-fluid py-4 px-md-4">
        <div class="row g-4">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friends.php"; ?>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <br><br>
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
                    <div class="cover-banner" style="background-image: url('<?php echo htmlspecialchars($cover_src); ?>');"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="<?php echo htmlspecialchars($avatar_src); ?>" alt="Avatar" class="rounded-circle border" style="width: 90px; height: 90px; object-fit: cover;">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user['fullname'] ?? 'Your profile'); ?></h5>
                                <small class="text-muted">Update your profile information and upload new images.</small>
                            </div>
                        </div>

                        <form action="../Context/update_profile.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Bio</label>
                                <textarea name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Location</label>
                                    <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Birth Date</label>
                                    <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row g-3 mt-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Profile Picture</label>
                                    <input type="file" name="profile_picture" accept="image/*" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Cover Photo</label>
                                    <input type="file" name="cover_photo" accept="image/*" class="form-control">
                                </div>
                            </div>
                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="profile.php" class="btn btn-outline-secondary">View Profile</a>
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 80px; z-index: 1;">
                    <?php include_once "../Components/friendrequest.php"; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="settingsStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold <?php echo isset($_GET['updated']) ? 'text-success' : 'text-danger'; ?>"><?php echo isset($_GET['updated']) ? 'Success' : 'Update Failed'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="mb-0 text-muted"><?php echo isset($_GET['updated']) ? 'Profile updated successfully.' : 'Unable to update profile. Please try again.'; ?></p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (isset($_GET['updated']) || isset($_GET['error'])): ?>
                const settingsStatusModal = new bootstrap.Modal(document.getElementById('settingsStatusModal'));
                settingsStatusModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>
