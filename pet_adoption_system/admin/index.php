<?php
// admin/index.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/application_functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {
    redirect('../index.php');
}

// Get recent applications only
try {
    $stmt = $conn->prepare("
        SELECT a.*, p.name as pet_name, u.username 
        FROM adoption_applications a 
        JOIN pets p ON a.pet_id = p.pet_id 
        JOIN users u ON a.user_id = u.user_id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching recent applications: " . $e->getMessage();
}

include '../includes/admin_header.php';
?>

<div class="container admin-dashboard-main mt-5 mb-5">
    <div class="dashboard-header-row">
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <div class="welcome-message">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
    </div>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <div class="dashboard-card">
        <h2 class="section-title">Recent Applications</h2>
        <?php if (!empty($recentApplications)): ?>
            <div class="applications-table-wrapper">
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Pet</th>
                            <th>Applicant</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentApplications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['pet_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="applications.php?id=<?php echo $app['application_id']; ?>" class="btn btn-sm btn-orange">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-file-alt"></i>
                <h3>No Recent Applications</h3>
                <p>There are no recent adoption applications to display.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
