<?php
//include bitcoin dependency
require_once 'vendor/autoload.php';
use Denpa\Bitcoin\Client as BitcoinClient;
// Check if user is not logged in, redirect him to login page
session_start();
//if user session not set, then redirect to home
if(!isset($_SESSION['user']) || $_SESSION['user']!=''){
    header('Location:http://'.$_SERVER["HTTP_HOST"].'/');
    exit;
}else{
    $message = "";
    if(isset($_POST['register'])){

        include('securimage-master/securimage.php');

        //create bitcoin instance
        $bitcoind = new BitcoinClient([
            "scheme" => "http",
            "host" => "localhost",
            "port" => 18332,
            "user" => 'root',
            "password" => '1mN%AWP46J?W$CdW'
        ]);

        $securimage = new Securimage();
        if ($securimage->check($_POST['captcha_code']) == false) {
            // the code was incorrect
            // you should handle the error so that the form processor doesn't continue
            // or you can use the following code if there is no validation or you do not know how
            $message = '<div id="message" class="alert alert-warning">';
            $message .= 'The security code entered was incorrect.';
            $message .= '</div>';
        }else{


            //process registration request
            $db_array = include("../../../etc/return_db_array.php");
            $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
            if (!$conn) {
                die("ERROR: Unable to connect: " . $conn->connect_error);
            }

            /*process inputs and return if needed */
            //if inputs are empty, then show errors
            if($_POST['username'] == '' || $_POST['password'] == '' || $_POST['pin'] == '') {
                //validate inputs
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'All required fields must be filled';
                $message .= '</div>';
            }else if($_POST['password'] !== $_POST['password_repeat']){
                //validate PIN
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'Passwords do not match';
                $message .= '</div>';
            }else if(!is_numeric($_POST['pin'])){
                //validate PIN
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'PIN code must be numeric';
                $message .= '</div>';
            }else{

                //create account address and save into users table
                $user_btc_address = "";
                try {
                    $wallet_response = $bitcoind->createwallet($_POST['username']);
                    $address_response = $bitcoind->wallet($_POST['username'])->getnewaddress();
                    $user_btc_address = $address_response->result();
                } catch (Exception $e) {
                    print_r("error");
                }

                //encrypt the password and start insert
                $p = password_hash($_POST['password'], PASSWORD_BCRYPT);
                //create random number for referral code
                $user_refer_code = $_POST["username"].rand(5, 15);
                //process the insert statement
                $sql = 'INSERT INTO `users` ( username, password_hash,pin,referral_code,referral, btc_payment_address ) VALUES ("'.$_POST["username"].'", "'.$p.'", "'.$_POST["pin"].'", "'.$_POST["referral_code"].'", "'.$user_refer_code.'","'.$user_btc_address.'")';
                if (!mysqli_query($conn, $sql)) {
                    $message = '<div id="message" class="alert alert-warning">';
                    $message .= 'Cannot Register User: ';
                    $message .= mysqli_error($conn);
                    $message .= '</div>';
                } else {
                    $message .= '<div id="message" class="alert alert-success">';
                    $message .=  'User Registered.';
                    $message .= '</div>';
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
            <label class="return_message" id="return_message"></label>
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo $_POST['username'];?>"/>
            <br/>
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" value="<?php echo $_POST['password'];?>"/>
            <br/>
            <label for="password_repeat">Confirm Password:</label>
            <input type="password" class="form-control" id="password_repeat" name="password_repeat" value="<?php echo $_POST['password_repeat'];?>"/>
            <br/>
            <label for="pin">PIN:</label>
            <input type="password" class="form-control" id="pin" name="pin" value="<?php echo $_POST['pin'];?>"/>
            <br/>
            <label for="referral_code">Referral Code:</label>
            <input type="text" class="form-control" name="referral_code" id="referral_code" value="<?php echo $_GET['referer'];?>"/>
            <br/>
            <img id="captcha" style="min-width: 100%;margin-top: 10px;" src="/securimage-master/securimage_show.php" alt="CAPTCHA Image" />
            <br/>
            <input type="text" style="min-width: 100%;margin-top: 10px;" name="captcha_code" size="10" maxlength="6" />
            <br/>
            <br/>
            <input id="register" name="register" type="submit" class="btn btn-primary form-control" value="Register">
            <br/> <br/>
            Already have an account: <a id="login" href="login">Login</a>
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
