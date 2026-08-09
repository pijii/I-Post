<?php
    include_once "../Context/getuserdata.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../Styles/nav.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">

    <title>Navigation</title>
</head>
<body class="bg-light">

   <?php
    include "../Context/getuserdata.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../Assets/bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="../Styles/nav.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Styles/style.css">

    <title>Navigation</title>
</head>
<body class="bg-light">
    <?php
        // Get the current page filename (e.g., 'dashboard.php', 'explore.php')
        $current_page = basename($_SERVER['PHP_SELF']);
    ?>

    <!-- TOP NAVBAR -->
    <nav class="navbar bg-white border-bottom fixed-top py-2 w-100" style="z-index: 1040; top: 0; left: 0;">
        
        <!-- MOBILE SEARCH OVERLAY -->
        <div class="mobile-search-overlay" id="mobileSearchOverlay">
            <form class="w-100 d-flex align-items-center gap-2" action="search.php" method="GET">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-light border-end-0 text-secondary-custom">
                        <i class="bi bi-search"></i>
                    </span>
                    <input class="form-control bg-light border-start-0 ps-0" type="search" name="query" id="mobileSearchInput" placeholder="Search I-Post..." required>
                </div>
                <button type="button" class="btn btn-icon-custom" id="closeMobileSearch" title="Close Search">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </form>
        </div>

        <div class="container-fluid px-2 px-md-4 d-flex align-items-center justify-content-between flex-nowrap">
            
            <!-- BRAND LOGO & DESKTOP SEARCH BAR (Hidden on Mobile) -->
            <div class="d-none d-lg-flex align-items-center gap-3 flex-grow-0 me-2">
                <a href="dashboard.php" class="navbar-brand m-0 p-0">
                    <img src="../img/iPost_logo.png" alt="I-Post Logo" height="38">
                </a>
                <form role="search" action="search.php" method="GET" style="width: 240px;">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary-custom">
                            <i class="bi bi-search"></i>
                        </span>
                        <input class="form-control bg-light border-start-0 ps-0" type="search" name="query" placeholder="Search I-Post..." required>
                    </div>
                </form>
            </div>

            <!-- MOBILE SEARCH ICON TRIGGER -->
            <button class="btn btn-icon-custom d-lg-none" id="openMobileSearch" title="Search">
                <i class="bi bi-search fs-5"></i>
            </button>

            <!-- DESKTOP CENTER NAVIGATION -->
            <ul class="navbar-nav mx-auto d-none d-lg-flex flex-row justify-content-center gap-5">
                <li class="nav-item mx-3">
                    <a class="nav-link nav-icon-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php" title="Feed">
                        <i class="bi bi-house-door-fill"></i>
                    </a>
                </li>
                <li class="nav-item mx-3">
                    <a class="nav-link nav-icon-link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>" href="explore.php" title="Explore">
                        <i class="bi bi-compass-fill"></i>
                    </a>
                </li>
                <li class="nav-item mx-3">
                    <a class="nav-link nav-icon-link <?php echo ($current_page == 'bookmarks.php') ? 'active' : ''; ?>" href="bookmarks.php" title="Saved Posts">
                        <i class="bi bi-bookmark-fill"></i>
                    </a>
                </li>
                <li class="nav-item mx-3">
                    <a class="nav-link nav-icon-link <?php echo ($current_page == 'friends.php') ? 'active' : ''; ?>" href="friends.php" title="Friends">
                        <i class="bi bi-people-fill"></i>
                    </a>
                </li>
                <li class="mx-3 d-none d-md-block">
                    <span class="mx-2 mx-md-4"></span>
                </li>
            </ul>

            <!-- RIGHT ACTIONS -->
            <div class="d-flex align-items-center gap-2 flex-nowrap">

                <span class="mx-2 mx-md-4"></span>
                
                <!-- Messages Trigger -->
                <button class="btn btn-icon-custom position-relative" data-bs-toggle="modal" data-bs-target="#chatsModal" title="Messages">
                    <i class="bi bi-chat-dots-fill fs-5"></i>
                    <?php if (isset($unread_chat_count) && $unread_chat_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    <?php endif; ?>
                </button>

                <!-- Notifications Trigger -->
                <button class="btn btn-icon-custom position-relative" data-bs-toggle="modal" data-bs-target="#notificationsModal" title="Notifications">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if (isset($unread_notif_count) && $unread_notif_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    <?php endif; ?>
                </button>

                <!-- USER PROFILE DROPDOWN -->
                <div class="dropdown me-2 me-md-3 position-relative">
                    <button class="profile-nav-btn border-0 bg-transparent p-0 d-flex align-items-center cursor-pointer" 
                            type="button" 
                            id="profileDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <img src="<?php echo getAvatar($user_data['profile_picture'] ?? ''); ?>" 
                            alt="Profile" 
                            class="rounded-circle border profile-nav-img" 
                            style="width: 38px; height: 38px; object-fit: cover;"
                            onerror="this.onerror=null; this.src='../img/default_profile.png';">
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" 
                        id="profileDropdownMenu" 
                        aria-labelledby="profileDropdownBtn" 
                        style="min-width: 200px; position: absolute; right: 0; left: auto; z-index: 1070;">
                        <li>
                            <div class="dropdown-item-text">
                                <p class="mb-0 fw-bold text-secondary-custom text-truncate"><?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?></p>
                                <small class="text-muted text-truncate d-block">@<?php echo htmlspecialchars($user_data['username'] ?? 'username'); ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="profile.php">
                                <i class="bi bi-person-circle text-secondary-custom fs-6"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="settings.php">
                                <i class="bi bi-gear text-secondary-custom fs-6"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger fw-semibold" href="../Context/logout.php">
                                <i class="bi bi-box-arrow-right fs-6"></i> Logout
                            </a>
                        </li>
                    </ul>
</div>

            </div>

        </div>
    </nav>

    <!-- MOBILE BOTTOM NAV -->
    <div class="bottom-nav-mobile d-lg-none">
        <ul class="nav justify-content-around align-items-center m-0 p-0">
            <li class="nav-item">
                <a class="nav-link nav-icon-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php" title="Feed">
                    <i class="bi bi-house-door-fill"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link nav-icon-link <?php echo ($current_page == 'explore.php') ? 'active' : ''; ?>" href="explore.php" title="Explore">
                    <i class="bi bi-compass-fill"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link nav-icon-link <?php echo ($current_page == 'bookmarks.php') ? 'active' : ''; ?>" href="bookmarks.php" title="Saved Posts">
                    <i class="bi bi-bookmark-fill"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link nav-icon-link <?php echo ($current_page == 'friends.php') ? 'active' : ''; ?>" href="friends.php" title="Friends">
                    <i class="bi bi-people-fill"></i>
                </a>
            </li>
        </ul>
    </div>

    <!-- CHATS MODAL -->
    <div class="modal fade modal-dropdown" id="chatsModal" tabindex="-1" aria-labelledby="chatsModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-secondary-custom mb-0" id="chatsModalLabel">
                        <i class="bi bi-chat-dots-fill text-primary-custom me-2"></i>Messages
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-2 border-bottom bg-light">
                    <input type="text" class="form-control form-control-sm rounded-pill" placeholder="Search conversations...">
                </div>
                <div class="modal-body p-0" style="max-height: 380px;">
                    <div class="list-group list-group-flush">
                        <?php if (isset($recent_chats) && mysqli_num_rows($recent_chats) > 0): ?>
                            <?php while ($chat = mysqli_fetch_assoc($recent_chats)): ?>
                                <a href="chat_detail.php?id=<?php echo $chat['sender_id']; ?>" class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3 <?php echo $chat['is_read'] ? '' : 'bg-light'; ?>">
                                    <img src="<?php echo getAvatar($chat['profile_picture']); ?>" alt="Avatar" class="rounded-circle border" style="width: 42px; height: 42px; object-fit: cover;">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-semibold text-truncate text-secondary-custom fs-7"><?php echo htmlspecialchars($chat['fullname']); ?></h6>
                                            <small class="text-muted fs-8"><?php echo date('h:i A', strtotime($chat['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-0 text-muted fs-8 text-truncate"><?php echo htmlspecialchars($chat['message']); ?></p>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-4 text-muted fs-7">No messages yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-top justify-content-center p-2 bg-light">
                    <a href="chats.php" class="text-decoration-none fw-semibold fs-7 text-primary-custom">See all in Messenger</a>
                </div>
            </div>
        </div>
    </div>

    <!-- NOTIFICATIONS MODAL -->
    <div class="modal fade modal-dropdown" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-secondary-custom mb-0" id="notificationsModalLabel">
                        <i class="bi bi-bell-fill text-primary-custom me-2"></i>Notifications
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="max-height: 380px;">
                    <div class="list-group list-group-flush">
                        <?php if (isset($notifications) && mysqli_num_rows($notifications) > 0): ?>
                            <?php while ($notif = mysqli_fetch_assoc($notifications)): ?>
                                <a href="notifications.php" class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3 <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>">
                                    <img src="<?php echo getAvatar($notif['profile_picture']); ?>" alt="Avatar" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fs-8">
                                            <strong class="text-secondary-custom"><?php echo htmlspecialchars($notif['fullname']); ?></strong> 
                                            <?php echo htmlspecialchars($notif['type']); ?>
                                        </p>
                                        <small class="text-primary-custom fw-semibold fs-8"><?php echo date('M d, g:i a', strtotime($notif['created_at'])); ?></small>
                                    </div>
                                    <?php if (!$notif['is_read']): ?>
                                        <span class="p-1 bg-primary rounded-circle"></span>
                                    <?php endif; ?>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-4 text-muted fs-7">No notifications.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-top justify-content-center p-2 bg-light">
                    <a href="notifications.php" class="text-decoration-none fw-semibold fs-7 text-primary-custom">View all notifications</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Place this right before </body> in dashboard.php -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mobile Search Overlay Logic
        const openSearchBtn = document.getElementById('openMobileSearch');
        const closeSearchBtn = document.getElementById('closeMobileSearch');
        const searchOverlay = document.getElementById('mobileSearchOverlay');
        const searchInput = document.getElementById('mobileSearchInput');

        if (openSearchBtn && closeSearchBtn && searchOverlay) {
            openSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchOverlay.classList.add('active');
                if (searchInput) searchInput.focus();
            });

            closeSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchOverlay.classList.remove('active');
            });
        }

        // Direct JS Click Fallback Handler for Profile Dropdown
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Try Bootstrap Native Dropdown Toggle
                if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                    const instance = bootstrap.Dropdown.getOrCreateInstance(profileBtn);
                    instance.toggle();
                } else {
                    // Fail-safe manual CSS toggle if Bootstrap JS hasn't loaded yet
                    profileMenu.classList.toggle('show');
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.remove('show');
                }
            });
        }
    });
    </script>

</body>
</html>