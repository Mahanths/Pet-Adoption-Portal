<?php

// admin/applications.php

require_once '../config/database.php';

require_once '../includes/functions.php';

require_once '../includes/application_functions.php';



// Check if user is logged in and is admin

if(!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {

    redirect('../login.php');

}



$error = '';
$success = '';

// Handle application status update
if (isset($_POST['update_status'])) {
    $application_id = (int)$_POST['application_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    if (updateApplicationStatus($conn, $application_id, $status, $notes, $_SESSION['user_id'])) {
        $success = "Application status updated successfully.";
    } else {
        $error = "Failed to update application status.";
    }
}

// Handle checklist item update
if (isset($_POST['update_checklist'])) {
    $checklist_id = (int)$_POST['checklist_id'];
    $is_completed = isset($_POST['is_completed']) ? 1 : 0;
    
    if (updateChecklistItem($conn, $checklist_id, $is_completed, $_SESSION['user_id'])) {
        $success = "Checklist item updated successfully.";
    } else {
        $error = "Failed to update checklist item.";
    }
}

// Handle new checklist item
if (isset($_POST['add_checklist_item'])) {
    $application_id = (int)$_POST['application_id'];
    $item_name = $_POST['item_name'];
    
    if (addChecklistItem($conn, $application_id, $item_name)) {
        $success = "Checklist item added successfully.";
    } else {
        $error = "Failed to add checklist item.";
    }
}

// Handle interview scheduling
if (isset($_POST['schedule_interview'])) {
    $application_id = (int)$_POST['application_id'];
    $interview_date = $_POST['interview_date'];
    $interview_location = $_POST['interview_location'];
    
    if (scheduleInterview($conn, $application_id, $interview_date, $interview_location, $_SESSION['user_id'])) {
        $success = "Interview scheduled successfully.";
    } else {
        $error = "Failed to schedule interview.";
    }
}

// Handle follow-up scheduling
if (isset($_POST['schedule_follow_up'])) {
    $application_id = (int)$_POST['application_id'];
    $follow_up_date = $_POST['follow_up_date'];
    $follow_up_notes = $_POST['follow_up_notes'];
    
    if (scheduleFollowUp($conn, $application_id, $follow_up_date, $follow_up_notes, $_SESSION['user_id'])) {
        $success = "Follow-up scheduled successfully.";
    } else {
        $error = "Failed to schedule follow-up.";
    }
}

// Get all applications with pet and user details
try {
    $stmt = $conn->query("
        SELECT a.*, p.name as pet_name, p.image_url, u.username, u.email,
               c.name as category_name
        FROM adoption_applications a
        JOIN pets p ON a.pet_id = p.pet_id
        JOIN users u ON a.user_id = u.user_id
        JOIN pet_categories c ON p.category_id = c.category_id
        ORDER BY a.created_at DESC
    ");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching applications: " . $e->getMessage();
    $applications = [];
}



include '../includes/admin_header.php';

?>



<div class="container mt-4">

    <h2>Adoption Applications</h2>

    

    <?php if ($error): ?>

        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>

    <?php endif; ?>

    

    <?php if ($success): ?>

        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>

    <?php endif; ?>

    

    <div class="card">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Pet</th>

                            <th>Applicant</th>

                            <th>Status</th>

                            <th>Date</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($applications as $application): ?>

                            <tr>

                                <td>

                                    <?php if ($application['image_url']): ?>

                                        <img src="../<?php echo htmlspecialchars($application['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($application['pet_name']); ?>" 
                                             class="pet-thumbnail">

                                    <?php endif; ?>

                                    <?php echo htmlspecialchars($application['pet_name']); ?>

                                    <br>

                                    <small class="text-muted"><?php echo htmlspecialchars($application['category_name']); ?></small>

                                </td>

                                <td>

                                    <?php echo htmlspecialchars($application['username']); ?>

                                    <br>

                                    <small class="text-muted"><?php echo htmlspecialchars($application['email']); ?></small>

                                </td>

                                <td>

                                    <span class="badge badge-<?php 
                                        echo $application['status'] === 'Approved' ? 'success' : 
                                            ($application['status'] === 'Rejected' ? 'danger' : 'warning'); 
                                    ?>">

                                        <?php echo htmlspecialchars($application['status']); ?>

                                    </span>

                                </td>

                                <td><?php echo date('M d, Y', strtotime($application['created_at'])); ?></td>

                                <td>

                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-toggle="modal" 
                                            data-target="#applicationModal<?php echo $application['application_id']; ?>">

                                        View Details

                                    </button>

                                </td>

                            </tr>

                            

                            <!-- Application Details Modal -->

                            <div class="modal fade" id="applicationModal<?php echo $application['application_id']; ?>" tabindex="-1">

                                <div class="modal-dialog modal-lg">

                                    <div class="modal-content">

                                        <div class="modal-header">

                                            <h5 class="modal-title">Application Details</h5>

                                            <button type="button" class="close" data-dismiss="modal">

                                                <span>&times;</span>

                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <!-- Applicant Info -->

                                            <div class="mb-4">

                                                <div><strong>Name:</strong> <?php echo htmlspecialchars($application['full_name'] ?? $application['username']); ?></div>

                                                <div><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></div>

                                                <div><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone'] ?? 'N/A'); ?></div>

                                            </div>

                                            <!-- Application Status Update Form -->

                                            <form method="post" class="mb-0">

                                                <input type="hidden" name="application_id" value="<?php echo $application['application_id']; ?>">

                                                <div class="form-group">

                                                    <label class="font-weight-bold">Status</label>

                                                    <select name="status" class="form-control" required>

                                                        <option value="Pending" <?php echo $application['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>

                                                        <option value="Approved" <?php echo $application['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>

                                                        <option value="Rejected" <?php echo $application['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>

                                                    </select>

                                                </div>

                                                <div class="form-group">

                                                    <label class="font-weight-bold">Notes</label>

                                                    <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes here..."><?php echo htmlspecialchars($application['notes'] ?? ''); ?></textarea>

                                                </div>

                                                <button type="submit" name="update_status" class="btn btn-primary btn-block">Update Status</button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



<?php include '../includes/footer.php'; ?>