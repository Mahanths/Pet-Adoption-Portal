<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/notification_functions.php';
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Adoption System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/pet_adoption_system/assets/css/style.css">
    <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
        <link rel="stylesheet" href="/pet_adoption_system/assets/css/admin.css">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand site-title" href="/pet_adoption_system/index.php">Pet Adoption Portal</a>
        <div class="d-flex align-items-center flex-grow-1" style="min-width:0;">
            <div class="collapse navbar-collapse show flex-grow-1" id="navbarNav" style="flex-grow:1;">
                <ul class="navbar-nav d-flex align-items-center flex-row flex-nowrap w-100 justify-content-start" style="gap: 2px;">
                    <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/index.php">Home</a></li>
                    <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/pets.php">Available Pets</a></li>
                    <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/resources.php">Resources</a></li>
                    <?php if(isLoggedIn() && isAdmin()): ?>
                        <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/admin/index.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <?php if(isLoggedIn() && !isAdmin()): ?>
                        <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/my_applications.php">My Applications</a></li>
                    <?php endif; ?>
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/login.php">Login</a></li>
                        <li class="nav-item"><a class="btn nav-btn mx-1" href="/pet_adoption_system/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if(isLoggedIn()): ?>
                <div class="user-profile-btn d-flex align-items-center">
                    <a href="/pet_adoption_system/profile.php" class="profile-icon" style="display: flex; align-items: center; justify-content: center; background: #fff; border: 2px solid #e2a15d; border-radius: 50px; padding: 0.2rem 0.8rem !important; font-size: 0.95rem !important; font-weight: 700; color: #e2a15d; box-shadow: 0 2px 8px rgba(226,161,93,0.08); text-decoration: none; height: 34px !important;">
                        <i class="bi bi-person" style="font-size: 1.2rem; margin-right: 0.5em;"></i>
                        Profile
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    if (notificationsBtn && notificationsDropdown) {
        notificationsBtn.addEventListener('click', function() {
            notificationsDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function(event) {
            if (!notificationsBtn.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                notificationsDropdown.classList.remove('show');
            }
        });
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                markNotificationAsRead(notificationId);
            });
        });
    }
});
function markNotificationAsRead(notificationId) {
    fetch('/pet_adoption_system/admin/mark_notification_read.php', {
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
    fetch('/pet_adoption_system/admin/mark_all_notifications_read.php', {
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

<style>
    .nav-btn {
        min-width: 90px;
        height: 34px;
        font-size: 0.95rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 0;
        margin: 0 2px;
        line-height: 1;
        white-space: nowrap;
    }
    .profile-icon {
        height: 34px !important;
        font-size: 0.95rem !important;
        padding: 0.2rem 0.8rem !important;
    }
</style>


