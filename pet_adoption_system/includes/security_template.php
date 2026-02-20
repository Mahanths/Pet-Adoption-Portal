<?php
// Example of how to use security features in your pages

// 1. Include security configuration
define('SECURE_ACCESS', true);
require_once 'config/security.php';

// 2. Example of form submission with security measures
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limiting
    if (!checkRateLimit($_SERVER['REMOTE_ADDR'], 'form_submission', 10, 300)) {
        die('Too many requests. Please try again later.');
    }

    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid security token');
    }

    // Sanitize input
    $sanitizedData = sanitizeInput($_POST);

    // Example of password handling
    if (isset($_POST['password'])) {
        $hashedPassword = hashPassword($_POST['password']);
        // Store $hashedPassword in database
    }

    // Example of SQL query with sanitization
    $safeQuery = "SELECT * FROM users WHERE username = " . sanitizeSQL($sanitizedData['username']);

    // Example of output with XSS prevention
    echo preventXSS($sanitizedData['message']);
}

// 3. Example of form with CSRF token
?>
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <!-- Your form fields here -->
</form> 