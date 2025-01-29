<?php
require_once 'functions.php';

session_start();
if(isset($_SESSION['user']) && $_SESSION['user'] !== '') {
    redirect('index.php');
}

$enable_pgp_login = false;
$message = '';

if(isset($_POST['login'])) {
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

            if ($username !== '' && $password !== '') {
                try {
                    $db = get_db_connection();
                    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && verify_password($password, $user["password_hash"])) {
                        if($user["2fa_enabled"] == 'NULL' || $user["2fa_enabled"] == 0) {
                            $_SESSION['user'] = $user["username"];
                            $_SESSION['user_id'] = $user["id"];
                            $_SESSION['last_login'] = time();
                            redirect('index.php');
                        } else {
                            $enable_pgp_login = true;
                            $pub_key = $user["public_key"];
                            $hiddenusername = $user["username"];
                        }
                    } else {
                        $message = '<div id="message" class="alert alert-warning">Invalid username or password</div>';
                    }
                } catch (PDOException $e) {
                    $message = '<div id="message" class="alert alert-danger">An error occurred. Please try again later.</div>';
                    error_log("Login error: " . $e->getMessage());
                }
            } else {
                $message = '<div id="message" class="alert alert-warning">Please fill in all required fields</div>';
            }
        }
    }
}


if($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['decrypted_code']) AND $_POST['hiddenusername']!=''){
    include('pgp-2fa-master/pgp-2fa.php');
    $pgp = new pgp_2fa();

    if( isset($_POST["hiddenusername"]) ){
        if($pgp->compare($_POST['user-input'])){
            $_SESSION['user'] = $_POST["hiddenusername"];
            header('Location:http://'.$_SERVER["HTTP_HOST"].'/profile/settings');
            exit();
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
        <?php
        if($enable_pgp_login === false){
        ?>
            <h3>Login</h3>
            <form class="form" action="login.php" method="post">
                <?php echo $message;?>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required/>
                <br/>
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required/>
                <br/>
                <img id="captcha" style="min-width: 100%;margin-top: 10px;" src="/securimage-master/securimage_show.php" alt="CAPTCHA Image" />
                <a href="#" onclick="document.getElementById('captcha').src = '/securimage-master/securimage_show.php?' + Math.random(); return false">Refresh</a>
                <br/>
                <input type="text" style="min-width: 100%;margin-top: 10px;" name="captcha_code" size="10" maxlength="6" required placeholder="Enter the code above"/>
                <br/>
                <br/>
                <input id="login" name="login" type="submit" class="btn btn-primary form-control" value="Login">
            </form>
        <?php
        }
        if(isset($_POST['login']) AND $enable_pgp_login === true) {
            include('pgp-2fa-master/pgp-2fa.php');
            $pgp = new pgp_2fa();
            $msg = '';
            $pgp->generateSecret();
            $pgpmessage = $pgp->encryptSecret($pub_key);
            //echo 'k:'.$pub_key;
            //echo 'msg:'.$pgpmessage;
        ?>
            <h4>2FA is enabled on this account, please decrypt the encrypted message to login</h4>
            <textarea rows="15" class="form-control" name="pgp-msg"><?php echo $pgpmessage; ?></textarea>
            <form class="form" action="http://<?php echo $_SERVER['HTTP_HOST'];?>/login" method="post">
                <label for="user-input">Decrypted Code:</label>
                <input type="text" name="user-input" id="user-input" class="form-control"/>
                <input type="hidden" id="hiddenusername" name="hiddenusername" value="<?php echo $hiddenusername;?>"/>
                <input type="hidden" id="pgp-key2" name="pgp-key2" value="<?php echo $_POST['pgp-key'];?>"/>
                <br/>
                <input type="submit" id="decrypted_code" name="decrypted_code" class="btn btn-primary form-control" value="Login"/>
            </form>
        <?php
        }
        ?>
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
