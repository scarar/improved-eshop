<?php
// Check if user is not logged in, redirect him to login page
session_start();
if(isset($_SESSION['user']) && $_SESSION['user']!=''){
    header('Location:http://'.$_SERVER["HTTP_HOST"].'/');
    exit();
}
//for enabling PGP for users who have enabled PGP Login
$enable_pgp_login = false;

if(isset($_POST['login'])) {
    include('securimage-master/securimage.php');

    $securimage = new Securimage();
    if ($securimage->check($_POST['captcha_code']) == false) {
        // the code was incorrect
        // you should handle the error so that the form processor doesn't continue
        // or you can use the following code if there is no validation or you do not know how
        $message = '<div id="message" class="alert alert-warning">';
        $message .= 'The security code entered was incorrect.';
        $message .= '</div>';
    }else{
        //echo 'TRUE:'.$_POST['captcha_code'];
        $db_array = include("../../../etc/return_db_array.php");
        $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
        if (!$conn) {
            die("ERROR: Unable to connect: " . $conn->connect_error);
        }
        //     echo 'u:'.$_POST['username'];
        //   echo 'u:'.$_POST['password'];

        //get user credentials and process login request
        if ($_POST['username'] !== '' && $_POST['password'] !== '') {
            //get username and password using username, {loading SINGLE USER only}
            $sql = 'SELECT * FROM users WHERE username="' . $_POST['username'] . '" LIMIT 1';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                    //decrypt password and match with user input
                    if (password_verify($_POST['password'], $row["password_hash"])) {
                        //login
                        if($row["2fa_enabled"] == 'NULL' OR $row["2fa_enabled"] == 0){
                            $_SESSION['user'] = $row["username"];
                            header("Location:/");
                            exit;
                        }else{
                            //2fa enabled
                            $enable_pgp_login = true;
                            $pub_key = $row["public_key"];
                            $hiddenusername = $row["username"];
                        }

                    } else {
                        $message = '<div id="message" class="alert alert-warning">';
                        $message .= 'Wrong Password';
                        $message .= '</div>';
                    }
                }
            }else{
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'Account doesn\'t exist';
                $message .= '</div>';
            }
        }else{
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'Please fill in the required fields';
                $message .= '</div>';
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
                <label class="return_message" id="return_message"></label>
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username"/>
                <br/>
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password"/>
                <br/>
                <img id="captcha" style="min-width: 100%;margin-top: 10px;" src="/securimage-master/securimage_show.php" alt="CAPTCHA Image" />
                <br/>
                <input type="text" style="min-width: 100%;margin-top: 10px;" name="captcha_code" size="10" maxlength="6" />
                <br/>
                    <br/>
                <input id="login" name="login" type="submit" class="btn btn-primary form-control" value="login">
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
