<?php
// Check if user is not logged in, redirect him to login page
session_start();
if($_SESSION['user']==''){
    header('Location:http://'.$_SERVER["HTTP_HOST"].'/login');
    exit();
}
include('../pgp-2fa-master/pgp-2fa.php');
$pgp = new pgp_2fa();
$msg = '';
if( $_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['pgp-key']) AND $_POST['pgp-key'] != '' ){
    $pgp->generateSecret();
    $pgpmessage = $pgp->encryptSecret($_POST['pgp-key']);
    //echo '2FA:'.$_POST['is2fa'];
}

if($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['decrypted_code']) ){
    if($pgp->compare($_POST['user-input'])){
        $db_array = include("../../../../etc/return_db_array.php");
        $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
        if (!$conn) {
            die("ERROR: Unable to connect: " . $conn->connect_error);
        }
        //echo '2FA:'.$_POST['is2fa2'];

        //get user credentials and process login request
        if ($_POST['pgp-key2'] !== '') {
                if($_POST['is2fa2'] == on ){
                    //echo '2FA 2 is true:'.$_POST['is2fa2'];
                    $pgp_2fa = 1;
                }else{
                    //echo '2FA 2 is false: '.$_POST['is2fa2'];
                    $pgp_2fa = 0;
                }
            //get username and password using username, {loading SINGLE USER only}
            //if username was found, match the password
            // get row data

            //decrypt password and match with user input
            $sql = 'UPDATE `users` SET public_key="' . $_POST["pgp-key2"] . '" , 2fa_enabled="'.$pgp_2fa.'"';
            if (!mysqli_query($conn, $sql)) {
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'Could not update 2FA settings.';
                $message .= mysqli_error($conn);
                $message .= '</div>';
            }else{
                $message = '<div id="message" class="alert alert-success">';
                $message .= '2FA settings updated.';
                $message .= '</div>';
            }
            //login user now
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PGP 2FA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'];?>/bootstrap/css/bootstrap.min.css">
    <link href="../style.css" media="all" rel="stylesheet" />
</head>
<body>
<?php
// get main menu
include('../parts/main_menu.php');
?>
<div class="container-fluid text-center main">
    <div class="col-sm-3 text-center sidebar">
        left sidebar
    </div>
    <div class="col-sm-9 text-left main">
        <h3><span class="glyphicon glyphicon-cog"></span> Settings</h3>
        <ul class="nav nav-pills">
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/settings">Settings</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/reset-password">Reset Password</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pin">PIN</a></li>
            <li class="active"><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pgp-2fa">PGP/2FA</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/btc_address">BTC Payment Address</a></li>

        </ul>
        <br>
        <div class="row">
            <?php
            if(!isset($_POST['pgp-key']) OR $_POST['pgp-key']  == '' ){
                //connect to db and show the pub key if already set
                $db_array = include("../../../../etc/return_db_array.php");
                $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
                if (!$conn) {
                    die("ERROR: Unable to connect: " . $conn->connect_error);
                }

                ?>
                <form class="form" action="pgp-2fa" method="post">
                    <?php echo $message; ?>
                    <?php echo urldecode($_GET['message']); ?>
                    <label for="pgp-key">Public Key:</label>
                    <textarea rows="10" class="form-control" name="pgp-key" id="pgp-key"><?php
                        $sql3 = 'SELECT public_key,2fa_enabled FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
            $result = $conn->query($sql3);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                    //print pub key for text area
                    echo $row['public_key'];
                    $is_2fa_enabled = $row['2fa_enabled'];
                }
            }
                       ?></textarea>
                    <br/>
                    <div class="form-group">
                        <div class="checkbox" style=" min-width: 250px;">
                            <label>
                                <input type="radio" name="is2fa" value="on" <?php if($is_2fa_enabled){echo 'checked';}?>> Enable</input>&nbsp;&nbsp;&nbsp;<input type="radio" name="is2fa" value="off" <?php if(!$is_2fa_enabled){echo 'checked';}?>> Disable</input>
                                <?php
                                    if($is_2fa_enabled == true){
                                        echo '<b style="color:green">  (2FA is Enabled) </b>';
                                    }else{
                                        echo '<b style="color:grey">  (2FA is disabled)</b>';
                                    }
                                    ?>
                            </label>
                        </div>
                    </div>
                    <input type="submit" id="pgp-2fa" name="pgp-2fa" class="btn btn-primary form-control" value="Save"/>
                </form>
            <?php
            }
            if($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['pgp-key']) AND $_POST['pgp-key']!='' ){
            ?>
            <div class="col-sm-12 text-left">
                <label for="pgp-msg">Encrypted Code:</label>
                <textarea rows="10" class="form-control" name="pgp-msg" id="pgp-msg"><?php echo $pgpmessage; ?></textarea>
                <form class="form" action="pgp-2fa" method="post">
                    <?php echo $message;?>
                    <label for="user-input">Decrypted Code:</label>
                    <input type="text" name="user-input" class="form-control" id="user-input" />
                    <input type="hidden" name="is2fa2" value="<?php echo $_POST['is2fa'];?>"/>
                    <input type="hidden" id="pgp-key2" name="pgp-key2" value="<?php echo $_POST['pgp-key'];?>"/>
                    <br/>
                    <input type="submit" id="decrypted_code" name="decrypted_code" class="btn btn-primary form-control" value="Save"/>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<br/>
<br/>
<footer class="container-fluid text-center">
    <p>Footer Text</p>
</footer>

</body>
</html>