<?php
// security.php - Website-wide security configuration

// Prevent direct access to this file
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Set security headers
function setSecurityHeaders() {
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: https:; img-src 'self' data: https:;");
}

// Secure session configuration
function secureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
    }
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

// Input sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Rate limiting
function checkRateLimit($ip, $action, $limit = 60, $period = 3600) {
    $rateFile = sys_get_temp_dir() . '/rate_' . md5($ip . $action) . '.tmp';
    
    if (file_exists($rateFile)) {
        $data = json_decode(file_get_contents($rateFile), true);
        if ($data['time'] > time() - $period) {
            if ($data['count'] >= $limit) {
                return false;
            }
            $data['count']++;
        } else {
            $data = ['count' => 1, 'time' => time()];
        }
    } else {
        $data = ['count' => 1, 'time' => time()];
    }
    
    file_put_contents($rateFile, json_encode($data));
    return true;
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// SQL Injection prevention
function sanitizeSQL($string) {
    global $conn;
    if ($conn instanceof PDO) {
        return $conn->quote($string);
    }
    return addslashes($string);
}

// XSS Prevention
function preventXSS($data) {
    if (is_array($data)) {
        return array_map('preventXSS', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Initialize security features
define('SECURE_ACCESS', true);
setSecurityHeaders();
secureSession();
generateCSRFToken(); 