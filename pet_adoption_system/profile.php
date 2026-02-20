<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
$profile_success = '';
$profile_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Invalid email address.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        if ($stmt->execute([$email, $user_id])) {
            $profile_success = 'Profile updated successfully!';
            $user['email'] = $email;
        } else {
            $profile_error = 'Error updating profile.';
        }
    }
}
// Handle password change
$pw_success = '';
$pw_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $hash = $stmt->fetchColumn();
    if (!password_verify($current, $hash)) {
        $pw_error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $pw_error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $pw_error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user_id]);
        $pw_success = 'Password changed successfully!';
    }
}
// Fetch adoption history
$stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.image_url FROM adoption_applications a JOIN pets p ON a.pet_id = p.pet_id WHERE a.user_id = ? AND a.status = 'Approved' ORDER BY a.created_at DESC");
$stmt->execute([$user_id]);
$adoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h4>My Profile</h4></div>
                <div class="card-body">
                    <?php if($profile_success): ?><div class="alert alert-success"><?php echo $profile_success; ?></div><?php endif; ?>
                    <?php if($profile_error): ?><div class="alert alert-danger"><?php echo $profile_error; ?></div><?php endif; ?>
                    <form method="post">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><h4>Change Password</h4></div>
                <div class="card-body">
                    <?php if($pw_success): ?><div class="alert alert-success"><?php echo $pw_success; ?></div><?php endif; ?>
                    <?php if($pw_error): ?><div class="alert alert-danger"><?php echo $pw_error; ?></div><?php endif; ?>
                    <form method="post">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h4>Adoption History</h4></div>
                <div class="card-body">
                    <?php if (!empty($adoptions)): ?>
                        <ul class="list-group">
                            <?php foreach ($adoptions as $adopt): ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <img src="<?php echo !empty($adopt['image_url']) ? htmlspecialchars($adopt['image_url']) : 'assets/images/pet-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($adopt['pet_name']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; margin-right: 12px;">
                                    <span><?php echo htmlspecialchars($adopt['pet_name']); ?></span>
                                    <span class="badge badge-success ml-auto">Adopted</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="no-results text-center">
                            <i class="fas fa-paw" style="color:#e2a15d; font-size:2rem;"></i>
                            <p class="mt-2 mb-0">No successful adoptions yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 