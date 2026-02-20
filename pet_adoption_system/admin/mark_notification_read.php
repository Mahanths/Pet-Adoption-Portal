<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/notification_functions.php';

if (!isLoggedIn() || !isAdminRole($conn, $_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = (int)$_POST['notification_id'];
    $success = markNotificationAsRead($conn, $notification_id);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']); 