<?php
// Check if user is not logged in, redirect him to login page
session_start();
if($_SESSION['user']==''){
    header('Location:http://'.$_SERVER["HTTP_HOST"].'/login');
    exit;
}else{
    $db_array = include("../../../../etc/return_db_array.php");
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
    }

    if(isset($_POST['reset_password'])){

        if($_POST['newpassword'] === $_POST['newpassword_repeat']){
        //get username using session, {loading SINGLE USER only}
            $sql = 'SELECT password_hash FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                    //if old password was matched, then update it with new one
                    if (password_verify($_POST['password'], $row["password_hash"])) {
                        //encrypt password and update it again
                        $p = password_hash($_POST['newpassword'], PASSWORD_BCRYPT);
                        $sql2 = 'UPDATE `users` SET password_hash="'.$p.'"';
                        if (!mysqli_query($conn, $sql2)) {
                            $message = '<div id="message" class="alert alert-warning">';
                            $message .= 'Password could not be updated.';
                            $message .= mysqli_error($conn);
                            $message .= '</div>';
                        }else{
                            $message = '<div id="message" class="alert alert-success">';
                            $message .= 'New password is updated.';
                            $message .= '</div>';
                        }
                    }else{
                        //old password did not match
                        $message = '<div id="message" class="alert alert-warning">';
                        $message .= 'Current password is wrong.';
                        $message .= '</div>';
                    }
                    //show message
                }
            }else{
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'No Result found.';
                $message .= '</div>';
            }
        }else{
            $message = '<div id="message" class="alert alert-warning">';
            $message .= 'New password must be the same with new confirm password.';
            $message .= '</div>';
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
                <li class="active"><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/reset-password">Reset Password</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pin">PIN</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pgp-2fa">PGP/2FA</a></li>
                <li class="active"><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/btc_address">BTC Payment Address</a></li>
            </ul>
        <br>
        <div class="row">
            <div class="col-lg-6">
                <form class="form" action="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/reset-password" method="post">
                    <?php echo $message;?>
                    <label for="password">Current Password:</label>
                    <input type="password" name="password" class="form-control" id="password" />
                    <br>
                    <label for="newpassword">New Password:</label>
                    <input type="password" name="newpassword" class="form-control" id="newpassword" />
                    <br>
                    <label for="newpassword_repeat">Confirm New Password:</label>
                    <input type="password" name="newpassword_repeat" class="form-control" id="newpassword_repeat" />
                    <br><br/>
                    <input type="submit" id="reset_password" name="reset_password" class="btn btn-primary form-control" value="Change Password"/>
                </form>
            </div>


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
