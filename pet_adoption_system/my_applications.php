<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT a.*, p.name as pet_name, p.image_url, p.category_id, c.name as category_name FROM adoption_applications a JOIN pets p ON a.pet_id = p.pet_id JOIN pet_categories c ON p.category_id = c.category_id WHERE a.user_id = ? ORDER BY a.created_at DESC");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="pets-page">
    <div class="container">
        <div class="section-title">
            <h2>My Adoption Applications</h2>
            <p>Track the status of your adoption requests.</p>
        </div>
        <?php if (!empty($applications)): ?>
            <div class="applications-table">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pet</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <img src="<?php echo !empty($app['image_url']) ? htmlspecialchars($app['image_url']) : 'assets/images/pet-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($app['pet_name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; margin-right: 10px;">
                                <?php echo htmlspecialchars($app['pet_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($app['category_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                    <?php echo htmlspecialchars($app['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-file-alt" style="color:#e2a15d; font-size:2.5rem;"></i>
                <h3>No Applications Yet</h3>
                <p>You haven't applied to adopt any pets yet. Browse pets and apply to start your adoption journey!</p>
                <a href="pets.php" class="btn view-details-btn">Browse Pets</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 