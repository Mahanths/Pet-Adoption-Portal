<?php
// Function to update application status
function updateApplicationStatus($conn, $application_id, $status, $notes = '', $user_id) {
    try {
        $conn->beginTransaction();
        
        // Update application status
        if ($status === 'Approved') {
            $stmt = $conn->prepare("
                UPDATE adoption_applications 
                SET status = ?, 
                    status_updated_at = CURRENT_TIMESTAMP,
                    status_updated_by = ?,
                    notes = ?,
                    adopted_at = CURRENT_TIMESTAMP
                WHERE application_id = ?
            ");
            $stmt->execute([$status, $user_id, $notes, $application_id]);
        } else {
            $stmt = $conn->prepare("
                UPDATE adoption_applications 
                SET status = ?, 
                    status_updated_at = CURRENT_TIMESTAMP,
                    status_updated_by = ?,
                    notes = ?
                WHERE application_id = ?
            ");
            $stmt->execute([$status, $user_id, $notes, $application_id]);
        }
        
        // Add to history
        $stmt = $conn->prepare("
            INSERT INTO application_history 
            (application_id, status, notes, created_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$application_id, $status, $notes, $user_id]);
        
        $conn->commit();
        return true;
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        return false;
    }
}

// Function to get application history
function getApplicationHistory($conn, $application_id) {
    try {
        $stmt = $conn->prepare("
            SELECT h.*, u.username as updated_by
            FROM application_history h
            LEFT JOIN users u ON h.created_by = u.user_id
            WHERE h.application_id = ?
            ORDER BY h.created_at DESC
        ");
        $stmt->execute([$application_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching application history: " . $e->getMessage());
        return [];
    }
}

// Function to get application checklist
function getApplicationChecklist($conn, $application_id) {
    try {
        $stmt = $conn->prepare("
            SELECT c.*, u.username as completed_by_name
            FROM application_checklist c
            LEFT JOIN users u ON c.completed_by = u.user_id
            WHERE c.application_id = ?
            ORDER BY c.checklist_id ASC
        ");
        $stmt->execute([$application_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching application checklist: " . $e->getMessage());
        return [];
    }
}

// Function to update checklist item
function updateChecklistItem($conn, $checklist_id, $is_completed, $user_id) {
    try {
        $stmt = $conn->prepare("
            UPDATE application_checklist 
            SET is_completed = ?,
                completed_at = CASE WHEN ? = 1 THEN CURRENT_TIMESTAMP ELSE NULL END,
                completed_by = CASE WHEN ? = 1 THEN ? ELSE NULL END
            WHERE checklist_id = ?
        ");
        return $stmt->execute([$is_completed, $is_completed, $is_completed, $user_id, $checklist_id]);
    } catch(PDOException $e) {
        error_log("Error updating checklist item: " . $e->getMessage());
        return false;
    }
}

// Function to add checklist item
function addChecklistItem($conn, $application_id, $item_name) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO application_checklist 
            (application_id, item_name) 
            VALUES (?, ?)
        ");
        return $stmt->execute([$application_id, $item_name]);
    } catch(PDOException $e) {
        error_log("Error adding checklist item: " . $e->getMessage());
        return false;
    }
}

// Function to schedule interview
function scheduleInterview($conn, $application_id, $interview_date, $interview_location, $user_id) {
    try {
        $stmt = $conn->prepare("
            UPDATE adoption_applications 
            SET interview_date = ?,
                interview_location = ?,
                status_updated_by = ?
            WHERE application_id = ?
        ");
        return $stmt->execute([$interview_date, $interview_location, $user_id, $application_id]);
    } catch(PDOException $e) {
        error_log("Error scheduling interview: " . $e->getMessage());
        return false;
    }
}

// Function to schedule follow-up
function scheduleFollowUp($conn, $application_id, $follow_up_date, $follow_up_notes, $user_id) {
    try {
        $stmt = $conn->prepare("
            UPDATE adoption_applications 
            SET follow_up_date = ?,
                follow_up_notes = ?,
                status_updated_by = ?
            WHERE application_id = ?
        ");
        return $stmt->execute([$follow_up_date, $follow_up_notes, $user_id, $application_id]);
    } catch(PDOException $e) {
        error_log("Error scheduling follow-up: " . $e->getMessage());
        return false;
    }
}

// Get adoptions per month (last 12 months)
function getAdoptionsPerMonth($conn) {
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(adopted_at, '%Y-%m') as month, COUNT(*) as count
        FROM adoption_applications
        WHERE status = 'Approved' AND adopted_at IS NOT NULL
        GROUP BY month
        ORDER BY month DESC
        LIMIT 12
    ");
    $stmt->execute();
    return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Get applications by status
function getApplicationsByStatus($conn) {
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) as count
        FROM adoption_applications
        GROUP BY status
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user registrations per month (last 12 months)
function getUserRegistrationsPerMonth($conn) {
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
        FROM users
        GROUP BY month
        ORDER BY month DESC
        LIMIT 12
    ");
    $stmt->execute();
    return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Get all users
function getAllUsers($conn) {
    $stmt = $conn->prepare("SELECT user_id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get user by ID
function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update user role
function updateUserRole($conn, $user_id, $role) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    return $stmt->execute([$role, $user_id]);
}

// Check if user is super admin
function isSuperAdmin($conn, $user_id) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();
    return $role === 'superadmin';
} 