<?php
// pets.php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get filter parameters
$species = isset($_REQUEST['species']) ? $_REQUEST['species'] : '';
$gender = isset($_REQUEST['gender']) ? $_REQUEST['gender'] : '';
$age = isset($_REQUEST['age']) ? trim($_REQUEST['age']) : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$perPage = 12; // Number of pets per page

// Debug: Show all age values in database
$debugStmt = $conn->prepare("SELECT DISTINCT age FROM pets");
$debugStmt->execute();
$allAges = $debugStmt->fetchAll(PDO::FETCH_COLUMN);
error_log("All age values in database: " . print_r($allAges, true));

// Build query
$query = "SELECT p.*, c.name as category_name FROM pets p LEFT JOIN pet_categories c ON p.category_id = c.category_id WHERE p.is_adopted = 0";
$countQuery = "SELECT COUNT(*) FROM pets p LEFT JOIN pet_categories c ON p.category_id = c.category_id WHERE p.is_adopted = 0";
$params = [];

if (!empty($species)) {
    $query .= " AND c.category_id = ?";
    $countQuery .= " AND c.category_id = ?";
    $params[] = $species;
}

if (!empty($gender)) {
    $query .= " AND p.gender = ?";
    $countQuery .= " AND p.gender = ?";
    $params[] = $gender;
}

if (!empty($age)) {
    // Use exact match for age category (ENUM)
    $query .= " AND p.age = ?";
    $countQuery .= " AND p.age = ?";
    $params[] = $age;
}

// Debug query and parameters
error_log("Query: " . $query);
error_log("Params: " . print_r($params, true));

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $countQuery .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY p.created_at DESC LIMIT " . (($page - 1) * $perPage) . ", $perPage";

try {
    // Debug: Show all pets without filters
    $debugStmt = $conn->prepare("SELECT pet_id, name, age FROM pets WHERE is_adopted = 0");
    $debugStmt->execute();
    $allPets = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $totalPets = $stmt->fetchColumn();
    
    // Get pets for current page
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching pets: " . $e->getMessage();
    $pets = [];
    $totalPets = 0;
}

// Get all categories for filter
$categories = getAllCategories($conn);

$pet_images = [
    'pexels-artem-makarov-289670876-13224675.jpg',
    'pexels-lilartsy-2397448.jpg',
    'pexels-kowalievska-1416792.jpg',
    'pexels-jennifer-murray-402778-1090408.jpg',
    'pexels-julia-volk-6408365.jpg',
    'pexels-goochie-poochie-3361739.jpg',
    'pexels-catscoming-1359307.jpg',
    'pexels-anastasiya-gepp-654466-1462636.jpg',
    'pexels-roman-odintsov-12715266.jpg',
    'pexels-caio-56733.jpg',
];

// If this is an AJAX request, only return the pets grid
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (!empty($pets)): ?>
        <div class="pet-grid">
            <?php $img_idx = 0; ?>
            <?php $user_id = $_SESSION['user_id'] ?? null; ?>
            <?php foreach ($pets as $pet): ?>
                <div class="pet-card">
                    <div class="pet-image">
                        <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'images/default-pet.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($pet['name']); ?>">
                    </div>
                    <div class="pet-info">
                        <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                        <div class="pet-meta">
                            <span><i class="fas fa-paw"></i> <?php echo htmlspecialchars($pet['category_name'] ?? ''); ?></span>
                            <span><i class="fas fa-venus-mars"></i> <?php echo htmlspecialchars($pet['gender']); ?></span>
                            <span><i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pet['age']); ?></span>
                        </div>
                        <p class="pet-description"><?php echo htmlspecialchars($pet['description']); ?></p>
                    </div>
                    <div class="pet-actions">
                        <?php if (!empty($pet['pet_id'])): ?>
                            <a href="pet_details.php?id=<?php echo urlencode($pet['pet_id']); ?>" class="view-details-btn" style="z-index: 1000; position: relative;">
                                View Details
                            </a>
                        <?php endif; ?>
                        <?php if (isLoggedIn()): ?>
                            <button class="favorite-star-btn" data-pet-id="<?php echo $pet['pet_id']; ?>">
                                <i class="far fa-star"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-paw"></i>
            <h3>No Pets Found</h3>
            <p>We couldn't find any pets matching your search criteria. Try adjusting your filters or search terms.</p>
            <a href="pets.php" class="btn view-details-btn">Clear Filters</a>
        </div>
    <?php endif;
    exit;
}
?>

<!-- Add CSS -->
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/pet-filter.css">
<link rel="stylesheet" href="assets/css/ux-improvements.css">

<div class="pets-page">
    <div class="container">
        <!-- Search and Filter Section -->
        <div class="search-section">
            <h3>Find Your Perfect Pet</h3>
            <form method="GET" class="row" id="filterForm">
                <div class="col-md-3">
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
                </div>
                <div class="col-md-3">
                    <div class="filter-group">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" class="form-control">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-group">
                        <label for="age">Age</label>
                        <select name="age" id="age" class="form-control">
                            <option value="">All Ages</option>
                            <option value="Baby" <?php echo $age === 'Baby' ? 'selected' : ''; ?>>Baby</option>
                            <option value="Young" <?php echo $age === 'Young' ? 'selected' : ''; ?>>Young</option>
                            <option value="Adult" <?php echo $age === 'Adult' ? 'selected' : ''; ?>>Adult</option>
                            <option value="Senior" <?php echo $age === 'Senior' ? 'selected' : ''; ?>>Senior</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn search-btn">
                        <i class="fas fa-search"></i> Search Pets
                    </button>
                </div>
            </form>
        </div>

        <!-- Pets Grid -->
        <?php if (!empty($pets)): ?>
            <div class="pet-grid">
                <?php $img_idx = 0; ?>
                <?php $user_id = $_SESSION['user_id'] ?? null; ?>
                <?php foreach ($pets as $pet): ?>
                    <div class="pet-card">
                        <div class="pet-image">
                            <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'images/default-pet.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($pet['name']); ?>">
                        </div>
                        <div class="pet-info">
                            <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <div class="pet-meta">
                                <span><i class="fas fa-paw"></i> <?php echo htmlspecialchars($pet['category_name'] ?? ''); ?></span>
                                <span><i class="fas fa-venus-mars"></i> <?php echo htmlspecialchars($pet['gender']); ?></span>
                                <span><i class="fas fa-birthday-cake"></i> <?php echo htmlspecialchars($pet['age']); ?></span>
                            </div>
                            <p class="pet-description"><?php echo htmlspecialchars($pet['description']); ?></p>
                        </div>
                        <div class="pet-actions">
                            <?php if (!empty($pet['pet_id'])): ?>
                                <a href="pet_details.php?id=<?php echo urlencode($pet['pet_id']); ?>" class="view-details-btn" style="z-index: 1000; position: relative;">
                                    View Details
                                </a>
                            <?php endif; ?>
                            <?php if (isLoggedIn()): ?>
                                <button class="favorite-star-btn" data-pet-id="<?php echo $pet['pet_id']; ?>">
                                    <i class="far fa-star"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-paw"></i>
                <h3>No Pets Found</h3>
                <p>We couldn't find any pets matching your search criteria. Try adjusting your filters or search terms.</p>
                <a href="pets.php" class="btn view-details-btn">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add JavaScript -->
<script src="assets/js/ux-improvements.js"></script>

<?php include 'includes/footer.php'; ?>