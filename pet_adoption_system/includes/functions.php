<?php
// includes/functions.php

// Start session on all pages
session_start();

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Function to get all pets
function getAllPets($conn, $filter = []) {
    try {
        $sql = "SELECT p.*, c.name as category_name 
                FROM pets p 
                JOIN pet_categories c ON p.category_id = c.category_id 
                WHERE 1=1";
        
        // Add filters if provided
        if(isset($filter['category_id']) && !empty($filter['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
        }
        if(isset($filter['is_adopted'])) {
            $sql .= " AND p.is_adopted = :is_adopted";
        }
        if(isset($filter['size']) && !empty($filter['size'])) {
            $sql .= " AND p.size = :size";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters if provided
        if(isset($filter['category_id']) && !empty($filter['category_id'])) {
            $stmt->bindParam(':category_id', $filter['category_id']);
        }
        if(isset($filter['is_adopted'])) {
            $stmt->bindParam(':is_adopted', $filter['is_adopted']);
        }
        if(isset($filter['size']) && !empty($filter['size'])) {
            $stmt->bindParam(':size', $filter['size']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Function to get pet by ID
function getPetById($conn, $petId) {
    try {
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                                FROM pets p 
                                JOIN pet_categories c ON p.category_id = c.category_id 
                                WHERE p.pet_id = :pet_id");
        $stmt->bindParam(':pet_id', $petId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Function to get all categories
function getAllCategories($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM pet_categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Function to validate age category
function isValidAgeCategory($age) {
    $validCategories = ['Baby', 'Young', 'Adult', 'Senior'];
    return in_array(trim($age), $validCategories);
}

// FAVORITES FEATURE
function addFavorite($conn, $user_id, $pet_id) {
    $stmt = $conn->prepare("INSERT IGNORE INTO favorites (user_id, pet_id) VALUES (?, ?)");
    return $stmt->execute([$user_id, $pet_id]);
}

function removeFavorite($conn, $user_id, $pet_id) {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND pet_id = ?");
    return $stmt->execute([$user_id, $pet_id]);
}

function isFavorite($conn, $user_id, $pet_id) {
    $stmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND pet_id = ?");
    $stmt->execute([$user_id, $pet_id]);
    return $stmt->fetchColumn() ? true : false;
}

function getUserFavorites($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.* FROM favorites f JOIN pets p ON f.pet_id = p.pet_id WHERE f.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if user is admin, staff, or superadmin
function isAdminRole($conn, $user_id) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();
    return in_array($role, ['admin', 'staff', 'superadmin']);
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Error Logging
function logError($message) {
    $logMessage = date('[Y-m-d H:i:s]') . " " . $message . "\n";
    error_log($logMessage, 3, __DIR__ . '/../logs/error.log');
}