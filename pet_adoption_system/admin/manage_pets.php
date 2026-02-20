<?php
// admin/manage_pets.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {
    redirect('index.php');
}

// Handle pet deletion
if (isset($_GET['delete'])) {
    $pet_id = (int)$_GET['delete'];
    try {
        // Delete related adoption applications first
        $stmt = $conn->prepare("DELETE FROM adoption_applications WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        // Delete pet's image if exists
        $stmt = $conn->prepare("SELECT image_url FROM pets WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        $pet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pet && !empty($pet['image_url'])) {
            $image_path = '../' . $pet['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete pet
        $stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        
        redirect('manage_pets.php?deleted=1');
    } catch(PDOException $e) {
        $error = "Error deleting pet: " . $e->getMessage();
    }
}

// Handle mark as adopted
if (isset($_GET['adopt'])) {
    $pet_id = (int)$_GET['adopt'];
    try {
        $stmt = $conn->prepare("UPDATE pets SET is_adopted = 1 WHERE pet_id = ?");
        $stmt->execute([$pet_id]);
        redirect('manage_pets.php?adopted=1');
    } catch(PDOException $e) {
        $error = "Error marking pet as adopted: " . $e->getMessage();
    }
}

// Get filter parameters
$species = isset($_GET['species']) ? $_GET['species'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT p.*, c.name as category_name 
          FROM pets p 
          LEFT JOIN pet_categories c ON p.category_id = c.category_id 
          WHERE 1=1";
$params = [];

if (!empty($species)) {
    $query .= " AND p.category_id = ?";
    $params[] = $species;
}

if ($status === 'available') {
    $query .= " AND p.is_adopted = 0";
} elseif ($status === 'adopted') {
    $query .= " AND p.is_adopted = 1";
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.created_at DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching pets: " . $e->getMessage();
    $pets = [];
}

// Get all categories for filter
try {
    $stmt = $conn->query("SELECT * FROM pet_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching categories: " . $e->getMessage();
    $categories = [];
}

include '../includes/admin_header.php';
?>

<div class="manage-pets">
    <div class="container">
        <div class="page-header">
            <h1>Manage Pets</h1>
            <a href="add_pet.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Pet
            </a>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Pet has been deleted successfully.</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['adopted'])): ?>
            <div class="alert alert-success">Pet has been marked as adopted.</div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="species">Species</label>
                    <select name="species" id="species" class="form-control">
                        <option value="">All Species</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php echo $species == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="adopted" <?php echo $status === 'adopted' ? 'selected' : ''; ?>>Adopted</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Pets Table -->
        <div class="pets-table">
            <?php if (empty($pets)): ?>
                <div class="no-results">
                    <i class="fas fa-paw"></i>
                    <h3>No Pets Found</h3>
                    <p>No pets match your search criteria. Try adjusting your filters.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Age</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pets as $pet): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($pet['image_url'])): ?>
                                        <img src="../<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="pet-thumbnail">
                                    <?php else: ?>
                                        <img src="../assets/images/pet-placeholder.jpg" alt="No image" class="pet-thumbnail">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                <td><?php echo htmlspecialchars($pet['category_name']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($pet['age_number']) && $pet['age_number'] > 0) {
                                        echo '(' . (int)$pet['age_number'] . ' years)';
                                    } else {
                                        echo htmlspecialchars($pet['age']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $pet['is_adopted'] ? 'adopted' : 'available'; ?>">
                                        <?php echo $pet['is_adopted'] ? 'Adopted' : 'Available'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="../pet_details.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="edit_pet.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if (!$pet['is_adopted']): ?>
                                        <a href="?adopt=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this pet as adopted?')">
                                            <i class="fas fa-home"></i> Mark Adopted
                                        </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this pet?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>