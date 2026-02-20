<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/notification_functions.php';

if (!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {
    redirect('../login.php');
}

$unread_count = getUnreadNotificationCount($conn);
$notifications = getUnreadNotifications($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pet Adoption System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=force2024">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <nav class="admin-navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <a href="index.php">
                    <i class="bi bi-gem"></i>
                    <span class="admin-title">Pet Adoption Admin</span>
                </a>
            </div>
            <div class="navbar-menu">
                <a href="../index.php" class="nav-link nav-back-to-site">
                    Back to Main Site
                </a>
                <a href="index.php" class="nav-link">
                    Dashboard
                </a>
                <a href="manage_pets.php" class="nav-link">
                    Manage Pets
                </a>
                <a href="applications.php" class="nav-link">
                    Applications
                </a>
            </div>
            <div class="navbar-icons">
                <div class="notifications-dropdown">
                    <button class="notifications-btn" id="notificationsBtn">
                        <i class="bi bi-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notifications-dropdown-content" id="notificationsDropdown">
                        <div class="notifications-header">
                            <h3>Notifications</h3>
                            <?php if ($unread_count > 0): ?>
                                <button class="mark-all-read" onclick="markAllNotificationsAsRead()">
                                    Mark all as read
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="notifications-list">
                            <?php if (empty($notifications)): ?>
                                <div class="no-notifications">
                                    <i class="bi bi-bell-slash"></i>
                                    <p>No new notifications</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item" data-id="<?php echo $notification['notification_id']; ?>">
                                        <div class="notification-icon">
                                            <?php if ($notification['type'] === 'application'): ?>
                                                <i class="bi bi-file-earmark-text"></i>
                                            <?php elseif ($notification['type'] === 'message'): ?>
                                                <i class="bi bi-envelope"></i>
                                            <?php else: ?>
                                                <i class="bi bi-info-circle"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="notification-content">
                                            <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <small><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="user-dropdown">
                    <a href="/pet_adoption_system/profile.php" class="user-btn">
                        Profile
                    </a>
                    <div class="user-dropdown-content">
                        <a href="../profile.php">
                            <i class="bi bi-gear"></i>
                            <span>Profile</span>
                        </a>
                        <a href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationsBtn = document.getElementById('notificationsBtn');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        
        notificationsBtn.addEventListener('click', function() {
            notificationsDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationsBtn.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                notificationsDropdown.classList.remove('show');
            }
        });

        // Mark notification as read when clicked
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                markNotificationAsRead(notificationId);
            });
        });
    });

    function markNotificationAsRead(notificationId) {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function markAllNotificationsAsRead() {
        fetch('mark_all_notifications_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>
    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 