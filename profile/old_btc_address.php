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

    $sql = 'SELECT btc_payment_address FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        //if username was found, match the password
        // get row data
        while ($row = $result->fetch_assoc()) {
            $btc_address = $row['btc_payment_address'];
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>BTC address for your payments</title>
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
        <h3><span class="glyphicon glyphicon-cog"></span> Set BTC Address</h3>
        <ul class="nav nav-pills">
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/settings">Settings</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/reset-password">Reset Password</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pin">Reset PIN</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pgp-2fa">PGP/2FA</a></li>
            <li class="active"><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/btc_address">BTC Payment Address</a></li>
        </ul>
        <br>
        <div class="row">
            <div class="col-md-8">
                <label for="btc_address">Your BTC Address for payments:</label>
                <br>
                <label style="font-weight: normal"><?php echo $btc_address;?></label>
                <br>
            </div>
            <div class="col-md-4">
                <b>Balance:</b>
                <p>0 BTC</p>
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
