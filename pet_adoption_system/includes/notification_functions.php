<?php
// Function to create a new notification
function createNotification($conn, $type, $title, $message, $related_id = null) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (type, title, message, related_id) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$type, $title, $message, $related_id]);
    } catch(PDOException $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

// Function to get unread notifications
function getUnreadNotifications($conn) {
    try {
        $stmt = $conn->query("
            SELECT * FROM notifications 
            WHERE is_read = FALSE 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching notifications: " . $e->getMessage());
        return [];
    }
}

// Function to mark notification as read
function markNotificationAsRead($conn, $notification_id) {
    try {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE notification_id = ?
        ");
        return $stmt->execute([$notification_id]);
    } catch(PDOException $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

// Function to mark all notifications as read
function markAllNotificationsAsRead($conn) {
    try {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE is_read = FALSE
        ");
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error marking all notifications as read: " . $e->getMessage());
        return false;
    }
}

// Function to get notification count
function getUnreadNotificationCount($conn) {
    try {
        $stmt = $conn->query("
            SELECT COUNT(*) FROM notifications 
            WHERE is_read = FALSE
        ");
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
} 