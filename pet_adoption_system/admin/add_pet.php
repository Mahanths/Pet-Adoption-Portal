<?php
// admin/add_pet.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$success = '';

// Get all categories
try {
    $stmt = $conn->query("SELECT * FROM pet_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching categories: " . $e->getMessage();
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $age = trim($_POST['age'] ?? '');
    $age_number = (int)($_POST['age_number'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = '';

    // Validate input
    if (empty($name)) {
        $error = "Pet name is required.";
    } elseif ($category_id <= 0) {
        $error = "Please select a category.";
    } elseif (empty($age)) {
        $error = "Age category is required.";
    } elseif (empty($gender)) {
        $error = "Gender is required.";
    } elseif (empty($description)) {
        $error = "Description is required.";
    } else {
        // Handle multiple image upload
        $main_image_url = '';
        $image_urls = [];
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $upload_dir = '../assets/images/pets/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            foreach ($_FILES['images']['name'] as $idx => $img_name) {
                if ($_FILES['images']['error'][$idx] === UPLOAD_ERR_OK) {
                    $type = $_FILES['images']['type'][$idx];
                    $size = $_FILES['images']['size'][$idx];
                    if (!in_array($type, $allowed_types)) {
                        $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                        break;
                    } elseif ($size > $max_size) {
                        $error = "File is too large. Maximum size is 5MB.";
                        break;
                    } else {
                        $file_extension = pathinfo($img_name, PATHINFO_EXTENSION);
                        $file_name = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$idx], $target_path)) {
                            $url = 'assets/images/pets/' . $file_name;
                            $image_urls[] = $url;
                            if ($main_image_url === '') $main_image_url = $url;
                        } else {
                            $error = "Error uploading image.";
                            break;
                        }
                    }
                }
            }
        }
        if (empty($error)) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO pets (name, category_id, age, age_number, gender, description, image_url, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $category_id, $age, $age_number, $gender, $description, $main_image_url]);
                $pet_id = $conn->lastInsertId();
                // Save all images to pet_images
                foreach ($image_urls as $url) {
                    $stmt2 = $conn->prepare("INSERT INTO pet_images (pet_id, image_url) VALUES (?, ?)");
                    $stmt2->execute([$pet_id, $url]);
                }
                $success = "Pet added successfully!";
                // Clear form
                $name = $description = '';
                $category_id = $age = 0;
                $gender = '';
            } catch(PDOException $e) {
                $error = "Error adding pet: " . $e->getMessage();
            }
        }
    }
}

include '../includes/admin_header.php';
?>

<div class="add-pet">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="mb-0">Add New Pet</h1>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <a href="manage_pets.php" class="btn btn-orange">
                    <i class="fas fa-arrow-left"></i> Back to Pets
                </a>
            </div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card shadow-lg p-4">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data" class="pet-form">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Pet Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($category_id ?? 0) == $category['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="age">Age Category</label>
                            <select name="age" id="age" class="form-control" required>
                                <option value="">Select Age Category</option>
                                <option value="Baby" <?php echo ($age ?? '') === 'Baby' ? 'selected' : ''; ?>>Baby</option>
                                <option value="Young" <?php echo ($age ?? '') === 'Young' ? 'selected' : ''; ?>>Young</option>
                                <option value="Adult" <?php echo ($age ?? '') === 'Adult' ? 'selected' : ''; ?>>Adult</option>
                                <option value="Senior" <?php echo ($age ?? '') === 'Senior' ? 'selected' : ''; ?>>Senior</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="age_number">Age (Number)</label>
                            <input type="number" name="age_number" id="age_number" class="form-control" min="0" value="<?php echo htmlspecialchars($age_number ?? ''); ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($gender ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($gender ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="images">Pet Images</label>
                            <input type="file" name="images[]" id="images" accept="image/*" multiple style="display:none;">
                            <label for="images" class="btn btn-orange" style="display:inline-block; white-space:nowrap; padding:0.7rem 2.2rem; min-width:180px; text-align:center; font-size:1.1rem; border-radius:8px; font-weight:700; cursor:pointer; margin-bottom:0.5rem;">Browse Images</label>
                            <span id="file-names" class="d-block mt-2" style="font-size:0.98rem; color:#888;"></span>
                            <small class="form-text text-muted mt-2">You can upload multiple images. Max size: 5MB each. Allowed types: JPG, PNG, GIF</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12">
                            <div class="image-preview mt-2"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-orange">
                            <i class="fas fa-save"></i> Add Pet
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('images').addEventListener('change', function(e) {
    const files = Array.from(e.target.files).map(f => f.name).join(', ');
    document.getElementById('file-names').textContent = files || '';
});
</script>

<?php include '../includes/footer.php'; ?>