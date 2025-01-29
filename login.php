<?php
require_once 'functions.php';

// Initialize secure session
start_secure_session();

// Rate limiting
$rate_limit_key = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;
$lockout_time = 900; // 15 minutes

if (isset($_SESSION[$rate_limit_key])) {
    $attempts = $_SESSION[$rate_limit_key];
    if ($attempts['count'] >= $max_attempts && time() - $attempts['first_attempt'] < $lockout_time) {
        $remaining = $lockout_time - (time() - $attempts['first_attempt']);
        die("Too many login attempts. Please try again in " . ceil($remaining / 60) . " minutes.");
    } else if (time() - $attempts['first_attempt'] >= $lockout_time) {
        unset($_SESSION[$rate_limit_key]);
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$enable_pgp_login = false;
$message = '';

// Process login form
if (isset($_POST['login'])) {
    try {
        // Validate CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            throw new Exception('Invalid request. Please try again.');
        }

        // Validate CAPTCHA
        require_once 'securimage-master/securimage.php';
        $securimage = new Securimage();
        if (!$securimage->check($_POST['captcha_code'])) {
            throw new Exception('The security code entered was incorrect.');
        }

        // Validate input
        $validation_rules = [
            'username' => 'required|min:3|max:50',
            'password' => 'required|min:8'
        ];
        
        $input = [
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];
        
        $errors = validate_input($input, $validation_rules);
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Clean input
        $username = sanitize_input($input['username']);
        $password = $input['password']; // Don't sanitize password

        // Attempt login
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !verify_password($password, $user['password_hash'])) {
            // Update rate limiting
            if (!isset($_SESSION[$rate_limit_key])) {
                $_SESSION[$rate_limit_key] = [
                    'count' => 1,
                    'first_attempt' => time()
                ];
            } else {
                $_SESSION[$rate_limit_key]['count']++;
            }
            
            throw new Exception('Invalid username or password');
        }

        // Check if account is locked
        if (isset($user['locked']) && $user['locked']) {
            throw new Exception('This account has been locked. Please contact support.');
        }

        // Handle 2FA if enabled
        if (!empty($user['2fa_enabled']) && $user['2fa_enabled'] != 'NULL') {
            $enable_pgp_login = true;
            $pub_key = $user['public_key'];
            $hiddenusername = $user['username'];
        } else {
            // Log successful login
            $stmt = $db->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Create session
            login_user($user);
            
            // Clear rate limiting
            unset($_SESSION[$rate_limit_key]);
            
            // Redirect to dashboard
            redirect('index.php');
        }
    } catch (Exception $e) {
        $message = '<div id="message" class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
        error_log("Login error for user {$username}: " . $e->getMessage());
    }
}

// Process 2FA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decrypted_code'], $_POST['hiddenusername'])) {
    try {
        if (!verify_csrf_token($_POST['csrf_token'])) {
            throw new Exception('Invalid request. Please try again.');
        }

        require_once 'pgp-2fa-master/pgp-2fa.php';
        $pgp = new pgp_2fa();

        $username = sanitize_input($_POST['hiddenusername']);
        $code = sanitize_input($_POST['user-input']);

        if ($pgp->compare($code)) {
            $db = get_db_connection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('Invalid user account.');
            }

            // Log successful 2FA login
            $stmt = $db->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Create session
            login_user($user);

            // Clear rate limiting
            unset($_SESSION[$rate_limit_key]);

            redirect('profile/settings');
        } else {
            throw new Exception('Invalid 2FA code. Please try again.');
        }
    } catch (Exception $e) {
        $message = '<div id="message" class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
        error_log("2FA error for user {$username}: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login to your E-Shop account">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars(APP_NAME); ?> - Login</title>
    
    <!-- Security headers already set in config.php -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    
    <!-- Password strength meter -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js" 
            integrity="sha512-TZlMGFY9xKj38t/5m2FzJ+RM/aD5alMHDe26p0mYUMoCF5G7ibfHUQILq0qQPV3wlsnCwL+TPRNK4vIWGLOkUQ==" 
            crossorigin="anonymous" 
            referrerpolicy="no-referrer" 
            defer></script>
</head>
<body>
    <?php include 'parts/main_menu.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if ($enable_pgp_login === false): ?>
                            <h1 class="h3 mb-4 text-center">Login</h1>
                            
                            <?php if ($message): ?>
                                <div class="alert-message mb-4"><?php echo $message; ?></div>
                            <?php endif; ?>
                            
                            <form id="loginForm" action="login.php" method="post" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                
                                <div class="form-group mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                           required 
                                           pattern="[a-zA-Z0-9_-]{3,50}"
                                           autocomplete="username"
                                           autofocus>
                                    <div class="invalid-feedback">
                                        Please enter a valid username (3-50 characters, letters, numbers, - and _)
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               required 
                                               minlength="8"
                                               autocomplete="current-password">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                id="togglePassword" 
                                                aria-label="Toggle password visibility">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Password must be at least 8 characters long
                                    </div>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label for="captcha_code" class="form-label">Security Check</label>
                                    <div class="text-center mb-2">
                                        <img id="captcha" 
                                             class="img-fluid" 
                                             src="/securimage-master/securimage_show.php" 
                                             alt="CAPTCHA Image">
                                        <button type="button" 
                                                class="btn btn-link btn-sm" 
                                                onclick="document.getElementById('captcha').src = '/securimage-master/securimage_show.php?' + Math.random();">
                                            <i class="bi bi-arrow-clockwise"></i> New Image
                                        </button>
                                    </div>
                                    <input type="text" 
                                           class="form-control" 
                                           id="captcha_code" 
                                           name="captcha_code" 
                                           required 
                                           maxlength="6" 
                                           autocomplete="off"
                                           placeholder="Enter the code shown above">
                                    <div class="invalid-feedback">
                                        Please enter the security code
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" 
                                            id="login" 
                                            name="login" 
                                            class="btn btn-primary btn-lg">
                                        Login
                                    </button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                                    <span class="mx-2">•</span>
                                    <a href="register.php" class="text-decoration-none">Create Account</a>
                                </div>
                            </form>
                            
                        <?php else: ?>
                            <h2 class="h4 mb-4 text-center">Two-Factor Authentication Required</h2>
                            
                            <?php if ($message): ?>
                                <div class="alert-message mb-4"><?php echo $message; ?></div>
                            <?php endif; ?>
                            
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle"></i>
                                Please decrypt the following message using your PGP private key to complete login.
                            </div>
                            
                            <?php
                            $pgp = new pgp_2fa();
                            $pgp->generateSecret();
                            $pgpmessage = $pgp->encryptSecret($pub_key);
                            ?>
                            
                            <div class="form-group mb-4">
                                <label class="form-label">Encrypted Message:</label>
                                <textarea class="form-control font-monospace" 
                                          rows="8" 
                                          readonly><?php echo htmlspecialchars($pgpmessage); ?></textarea>
                            </div>
                            
                            <form action="login.php" method="post" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="hiddenusername" value="<?php echo htmlspecialchars($hiddenusername); ?>">
                                
                                <div class="form-group mb-4">
                                    <label for="user-input" class="form-label">Decrypted Code:</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="user-input" 
                                           name="user-input" 
                                           required 
                                           autocomplete="off"
                                           autofocus>
                                    <div class="invalid-feedback">
                                        Please enter the decrypted code
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" 
                                            name="decrypted_code" 
                                            class="btn btn-primary btn-lg">
                                        Verify & Login
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">&copy; <?php echo date('Y') . ' ' . htmlspecialchars(APP_NAME); ?>. All rights reserved.</span>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        if (togglePassword && password) {
            togglePassword.addEventListener('click', () => {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                togglePassword.querySelector('i').classList.toggle('bi-eye');
                togglePassword.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert-message');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
    </script>
</body>
</html>
