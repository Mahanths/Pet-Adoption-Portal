<?php
// register.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if(isset($_POST['register'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif(strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if($stmt->rowCount() > 0) {
                $error = 'Username or email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();
                $success = 'Registration successful! You can now <a href=\"/pet_adoption_system/login.php\" class=\"btn btn-primary btn-sm\">Login</a>';
            }
        } catch(PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-white text-center">
                    <h2 class="mb-0" style="font-weight:700; color:#4a90e2;">Register</h2>
                </div>
                <div class="card-body p-4">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form action="" method="post" autocomplete="off" onsubmit="return validateRegisterForm()">
                        <div class="form-group mb-3">
                            <label for="username" class="font-weight-500">Username</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="font-weight-500">Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="font-weight-500">Password</label>
                            <div class="input-group" id="show_hide_password">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" name="password" id="password" class="form-control" required minlength="8" oninput="checkPasswordStrength()">
                                <div class="input-group-append">
                                    <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="password-strength-text" class="form-text text-muted"></small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="confirm_password" class="font-weight-500">Confirm Password</label>
                            <div class="input-group" id="show_hide_confirm_password">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="8">
                                <div class="input-group-append">
                                    <span class="input-group-text" style="cursor:pointer;" onclick="toggleConfirmPassword()"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary btn-block">Register</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="/pet_adoption_system/login.php">Already have an account? Login here</a><br>
                        <a href="/pet_adoption_system/index.php" class="text-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var passwordInput = document.getElementById('password');
    var icon = document.querySelector('#show_hide_password i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
function toggleConfirmPassword() {
    var passwordInput = document.getElementById('confirm_password');
    var icon = document.querySelector('#show_hide_confirm_password i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
function checkPasswordStrength() {
    var password = document.getElementById('password').value;
    var bar = document.getElementById('password-strength-bar');
    var text = document.getElementById('password-strength-text');
    var strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^A-Za-z0-9]/)) strength++;
    switch(strength) {
        case 0:
        case 1:
            bar.style.width = '25%';
            bar.className = 'progress-bar bg-danger';
            text.textContent = 'Weak';
            break;
        case 2:
            bar.style.width = '50%';
            bar.className = 'progress-bar bg-warning';
            text.textContent = 'Moderate';
            break;
        case 3:
            bar.style.width = '75%';
            bar.className = 'progress-bar bg-info';
            text.textContent = 'Good';
            break;
        case 4:
            bar.style.width = '100%';
            bar.className = 'progress-bar bg-success';
            text.textContent = 'Strong';
            break;
    }
}
function validateRegisterForm() {
    var password = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    if (password !== confirm) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?> 
