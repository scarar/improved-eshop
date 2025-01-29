<?php
require_once 'vendor/autoload.php';
require_once 'functions.php';

use Denpa\Bitcoin\Client as BitcoinClient;

session_start();

if(isset($_SESSION['user']) && $_SESSION['user'] !== '') {
    redirect('index.php');
}

$message = "";

if(isset($_POST['register'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $message = '<div id="message" class="alert alert-danger">Invalid request</div>';
    } else {
        include('securimage-master/securimage.php');
        $securimage = new Securimage();
        
        if ($securimage->check($_POST['captcha_code']) == false) {
            $message = '<div id="message" class="alert alert-warning">The security code entered was incorrect.</div>';
        } else {
            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];
            $password_repeat = $_POST['password_repeat'];
            $pin = sanitize_input($_POST['pin']);
            $referral_code = sanitize_input($_POST['referral_code']);

            if(empty($username) || empty($password) || empty($pin)) {
                $message = '<div id="message" class="alert alert-warning">All required fields must be filled</div>';
            } else if($password !== $password_repeat) {
                $message = '<div id="message" class="alert alert-warning">Passwords do not match</div>';
            } else if(!is_numeric($pin)) {
                $message = '<div id="message" class="alert alert-warning">PIN code must be numeric</div>';
            } else if(strlen($password) < 8) {
                $message = '<div id="message" class="alert alert-warning">Password must be at least 8 characters long</div>';
            } else {
                try {
                    $db = get_db_connection();
                    
                    // Check if username already exists
                    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetchColumn() > 0) {
                        $message = '<div id="message" class="alert alert-warning">Username already exists</div>';
                    } else {
                        // Create bitcoin wallet and address
                        $user_btc_address = "";
                        try {
                            $bitcoind = new BitcoinClient([
                                "scheme" => "http",
                                "host" => "localhost",
                                "port" => 18332,
                                "user" => 'root',
                                "password" => '1mN%AWP46J?W$CdW'
                            ]);
                            
                            $wallet_response = $bitcoind->createwallet($username);
                            $address_response = $bitcoind->wallet($username)->getnewaddress();
                            $user_btc_address = $address_response->result();
                        } catch (Exception $e) {
                            error_log("Bitcoin error: " . $e->getMessage());
                            $user_btc_address = "";
                        }

                        // Hash password and create user
                        $hashed_password = secure_password($password);
                        $user_refer_code = $username . bin2hex(random_bytes(4));
                        
                        $stmt = $db->prepare("INSERT INTO users (username, password_hash, pin, referral_code, referral, btc_payment_address) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt->execute([$username, $hashed_password, $pin, $referral_code, $user_refer_code, $user_btc_address])) {
                            $message = '<div id="message" class="alert alert-success">Registration successful. You can now login.</div>';
                        } else {
                            $message = '<div id="message" class="alert alert-warning">Registration failed. Please try again.</div>';
                        }
                    }
                } catch (PDOException $e) {
                    $message = '<div id="message" class="alert alert-danger">An error occurred. Please try again later.</div>';
                    error_log("Registration error: " . $e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'];?>/bootstrap/css/bootstrap.min.css">
    <link href="style.css" media="all" rel="stylesheet" />
</head>
<body>

<?php
// get main menu
include('parts/main_menu.php');
?>

<div class="container-fluid text-center main">
    <div class="col-sm-4 text-center">
    </div>
    <div class="col-sm-4 text-center">
        <h3>Register</h3>
        <form class="form" action="register.php" method="post">
            <?php echo $message;?>
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" 
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required 
                    pattern="[a-zA-Z0-9_-]{3,20}" title="Username must be between 3 and 20 characters, and can only contain letters, numbers, underscores and hyphens"/>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required
                    pattern=".{8,}" title="Password must be at least 8 characters long"/>
            </div>

            <div class="form-group">
                <label for="password_repeat">Confirm Password:</label>
                <input type="password" class="form-control" id="password_repeat" name="password_repeat" required/>
            </div>

            <div class="form-group">
                <label for="pin">PIN:</label>
                <input type="password" class="form-control" id="pin" name="pin" required
                    pattern="[0-9]{4,8}" title="PIN must be between 4 and 8 digits"/>
            </div>

            <div class="form-group">
                <label for="referral_code">Referral Code (optional):</label>
                <input type="text" class="form-control" name="referral_code" id="referral_code" 
                    value="<?php echo htmlspecialchars($_GET['referer'] ?? ''); ?>"/>
            </div>

            <div class="form-group">
                <img id="captcha" style="min-width: 100%;margin-top: 10px;" src="/securimage-master/securimage_show.php" alt="CAPTCHA Image" />
                <a href="#" onclick="document.getElementById('captcha').src = '/securimage-master/securimage_show.php?' + Math.random(); return false">Refresh Captcha</a>
                <input type="text" class="form-control" name="captcha_code" size="10" maxlength="6" required
                    placeholder="Enter the code shown above"/>
            </div>

            <input id="register" name="register" type="submit" class="btn btn-primary form-control" value="Register">
            <br/> <br/>
            Already have an account? <a id="login" href="login.php">Login</a>
        </form>
    </div>
    <div class="col-sm-4 text-center">
    </div>
</div>
<br/>
<br/>
<footer class="container-fluid text-center">
    <p>Footer Text</p>
</footer>

</body>
</html>
