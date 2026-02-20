<?php
// login.php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if(isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $user['password'])) {
                // Start session if not already started
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Remember Me (set cookie for 7 days)
                if ($remember) {
                    setcookie('remember_user', $user['username'], time() + (86400 * 7), "/");
                } else {
                    setcookie('remember_user', '', time() - 3600, "/");
                }
                
                if($user['is_admin']) {
                    redirect('/pet_adoption_system/admin/index.php');
                } else {
                    redirect('/pet_adoption_system/index.php');
                }
            } else {
                $error = "Incorrect password";
            }
        } else {
            $error = "User not found";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-white text-center">
                    <h2 class="mb-0" style="font-weight:700; color:#4a90e2;">Login</h2>
                </div>
                <div class="card-body p-4">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form action="" method="post" autocomplete="off">
                        <div class="form-group mb-3">
                            <label for="username" class="font-weight-500">Username</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="username" id="username" class="form-control" required value="<?php echo htmlspecialchars($_COOKIE['remember_user'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="font-weight-500">Password</label>
                            <div class="input-group" id="show_hide_password">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" name="password" id="password" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" <?php if(isset($_COOKIE['remember_user'])) echo 'checked'; ?>>
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="/pet_adoption_system/register.php">Don't have an account? Register here</a><br>
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
</script>

<?php include 'includes/footer.php'; ?>
