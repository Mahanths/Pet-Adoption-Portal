<?php
// apply.php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/notification_functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php'); // Changed to relative path
}

// Check if pet ID is provided
if(!isset($_GET['pet_id']) || empty($_GET['pet_id'])) {
    redirect('pets.php'); // Changed to relative path
}

$pet_id = (int)$_GET['pet_id'];
$pet = getPetById($conn, $pet_id);

// If pet not found or already adopted, redirect
if(!$pet || $pet['is_adopted']) {
    redirect('pets.php'); // Changed to relative path
}

$error = '';
$success = '';

// Process application form
if(isset($_POST['submit_application'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $reason = sanitize($_POST['reason']);
    
    try {
        // Check if user already applied for this pet
        $stmt = $conn->prepare("SELECT * FROM adoption_applications WHERE pet_id = :pet_id AND user_id = :user_id");
        $stmt->bindParam(':pet_id', $pet_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $error = "You have already applied to adopt this pet";
        } else {
            // Insert application
            $stmt = $conn->prepare("INSERT INTO adoption_applications (pet_id, user_id, full_name, phone, address, reason_for_adoption) 
                                   VALUES (:pet_id, :user_id, :full_name, :phone, :address, :reason)");
            $stmt->bindParam(':pet_id', $pet_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':reason', $reason);
            
            if($stmt->execute()) {
                $application_id = $conn->lastInsertId();
                
                // Create notification for admins
                $pet_name = getPetById($conn, $pet_id)['name'];
                $title = "New Adoption Application";
                $message = "A new application has been submitted for {$pet_name}";
                createNotification($conn, 'application', $title, $message, $application_id);
                
                $success = "Your application has been submitted successfully! We will contact you soon.";
            } else {
                $error = "Something went wrong";
            }
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Changed to relative path for header
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h2>Adoption Application</h2>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>You're applying to adopt: <?php echo htmlspecialchars($pet['name']); ?></h5>
                        <p>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($pet['category_name']); ?></span>
                            <span class="badge badge-info"><?php echo htmlspecialchars($pet['breed']); ?></span>
                            <span class="badge badge-secondary"><?php echo htmlspecialchars($pet['age'] ?? 'Unknown'); ?></span>
                        </p>
                    </div>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <p>
                            <a href="pets.php" class="btn btn-primary">Browse More Pets</a> <!-- Changed to relative path -->
                        </p>
                    <?php else: ?>
                        <form action="" method="post">
                            <div class="form-group">
                                <label for="full_name">Full Name:</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number:</label>
                                <input type="text" name="phone" id="phone" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="reason">Why do you want to adopt this pet?</label>
                                <textarea name="reason" id="reason" class="form-control" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" name="submit_application" class="btn btn-primary">Submit Application</button>
                            <a href="pet_details.php?id=<?php echo $pet_id; ?>" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Changed to relative path for footer -->
<?php include 'includes/footer.php'; ?>