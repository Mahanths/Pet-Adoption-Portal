<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/application_functions.php';

// Check if user is logged in and is super admin
if (!isLoggedIn() || !isSuperAdmin($conn, $_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

// Handle role update
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];
    if (updateUserRole($conn, $user_id, $role)) {
        $success = 'User role updated successfully.';
    } else {
        $error = 'Failed to update user role.';
    }
}

$users = getAllUsers($conn);

include '../includes/admin_header.php';
?>
<div class="container mt-4">
    <h2>Manage Users & Roles</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="card p-3 mb-4" style="background: #fff7ec; border: 1px solid #f3c892;">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form method="post" class="form-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <select name="role" class="form-control" <?php if ($user['user_id'] == $_SESSION['user_id']) echo 'disabled'; ?>>
                                        <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
                                        <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                                        <option value="staff" <?php if ($user['role'] === 'staff') echo 'selected'; ?>>Staff</option>
                                        <option value="volunteer" <?php if ($user['role'] === 'volunteer') echo 'selected'; ?>>Volunteer</option>
                                        <option value="superadmin" <?php if ($user['role'] === 'superadmin') echo 'selected'; ?>>Super Admin</option>
                                    </select>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                    <button type="submit" name="update_role" class="btn btn-sm btn-primary" <?php if ($user['user_id'] == $_SESSION['user_id']) echo 'disabled'; ?>>Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 