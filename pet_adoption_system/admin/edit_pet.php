<?php
// admin/edit_pet.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {
    redirect('index.php');
}

// Get pet ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('manage_pets.php');
}
$pet_id = (int)$_GET['id'];

// Fetch pet data
$pet = getPetById($conn, $pet_id);
if (!$pet) {
    redirect('manage_pets.php');
}

// Get all categories
try {
    $stmt = $conn->query("SELECT * FROM pet_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = [];
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $age = trim($_POST['age'] ?? '');
    $age_number = (int)($_POST['age_number'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = $pet['image_url'];

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
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            } elseif ($_FILES['image']['size'] > $max_size) {
                $error = "File is too large. Maximum size is 5MB.";
            } else {
                $upload_dir = '../assets/images/pets/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Delete old image if exists
                    if (!empty($pet['image_url']) && file_exists('../' . $pet['image_url'])) {
                        unlink('../' . $pet['image_url']);
                    }
                    $image_url = 'assets/images/pets/' . $file_name;
                } else {
                    $error = "Error uploading image.";
                }
            }
        }

        if (empty($error)) {
            try {
                $stmt = $conn->prepare("
                    UPDATE pets SET name=?, category_id=?, age=?, age_number=?, gender=?, description=?, image_url=? WHERE pet_id=?
                ");
                $stmt->execute([$name, $category_id, $age, $age_number, $gender, $description, $image_url, $pet_id]);
                $success = "Pet updated successfully!";
                // Refresh pet data
                $pet = getPetById($conn, $pet_id);
                // Optionally redirect after a short delay
                echo '<meta http-equiv="refresh" content="1.5;url=manage_pets.php">';
            } catch(PDOException $e) {
                $error = "Error updating pet: " . $e->getMessage();
            }
        }
    }
}

include '../includes/admin_header.php';
?>

<div class="edit-pet">
    <div class="container">
        <div class="page-header">
            <h1>Edit Pet</h1>
            <a href="manage_pets.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Pets
            </a>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data" class="pet-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Pet Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($pet['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>" <?php echo ($pet['category_id'] ?? 0) == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select name="gender" id="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($pet['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($pet['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="age">Age Category</label>
                                <select name="age" id="age" class="form-control" required>
                                    <option value="">Select Age Category</option>
                                    <option value="Baby" <?php echo ($pet['age'] ?? '') === 'Baby' ? 'selected' : ''; ?>>Baby</option>
                                    <option value="Young" <?php echo ($pet['age'] ?? '') === 'Young' ? 'selected' : ''; ?>>Young</option>
                                    <option value="Adult" <?php echo ($pet['age'] ?? '') === 'Adult' ? 'selected' : ''; ?>>Adult</option>
                                    <option value="Senior" <?php echo ($pet['age'] ?? '') === 'Senior' ? 'selected' : ''; ?>>Senior</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="age_number">Age (Number)</label>
                                <input type="number" name="age_number" id="age_number" class="form-control" min="0" value="<?php echo htmlspecialchars($pet['age_number'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="image">Pet Image</label>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input" accept="image/*">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Max size: 5MB. Allowed types: JPG, PNG, GIF</small>
                        <?php if (!empty($pet['image_url'])): ?>
                            <div class="image-preview"><img src="../<?php echo htmlspecialchars($pet['image_url']); ?>" alt="Current Image" style="max-width:120px; margin-top:10px; border-radius:6px;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($pet['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-actions" style="max-width:400px;margin:40px auto 0 auto;display:block;width:100%;">
                        <button type="submit" class="btn btn-primary mb-3" style="width: 100%;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="manage_pets.php" class="btn btn-outline-secondary" style="width: 100%;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Preview image before upload
const imageInput = document.getElementById('image');
if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'image-preview';
                    imageInput.parentNode.appendChild(preview);
                } else {
                    preview.innerHTML = '';
                }
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '120px';
                img.style.marginTop = '10px';
                img.style.borderRadius = '6px';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?> 