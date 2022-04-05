<?php
// Check if user is not logged in, redirect him to login page
session_start();
include('pgp-2fa.php');
$pgp = new pgp_2fa();
$msg = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['pgp-key'])  ){
    $pgp->generateSecret();
    $pgpmessage = $pgp->encryptSecret($_POST['pgp-key']);
}

if($_SERVER['REQUEST_METHOD'] == 'POST' AND isset($_POST['decrypted_code']) ){
    if($pgp->compare($_POST['user-input'])){
        $db_array = include("../../../../etc/return_db_array.php");
        $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
        if (!$conn) {
            die("ERROR: Unable to connect: " . $conn->connect_error);
        }
        //get user credentials and process login request
        if ($_POST['pgp-key2'] !== '') {
            //get username and password using username, {loading SINGLE USER only}
            $sql = 'SELECT username FROM users WHERE public_key="' . $_POST['pgp-key2'] . '" LIMIT 1';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                    //decrypt password and match with user input
                    $sql = 'UPDATE `users` SET public_key="'.$_POST["pgp-key2"].'"';
                    if (!mysqli_query($conn, $sql)) {
                        $message = '<div id="message" class="alert alert-warning">';
                        $message .= 'Cannot set public key: ';
                        $message .= mysqli_error($conn);
                        $message .= '</div>';
                    }
                    //login user now
                    $_SESSION['user'] = $row["username"];
                    header('Location:http://'.$_SERVER["HTTP_HOST"].'/');
                    exit;
                }
            }else {
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'No user found with this key';
                $message .= '</div>';
                $message = urlencode($message);
                header('Location:/pgp-login?message='.$message.'');
                exit;
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
    <link href="../style.css" media="all" rel="stylesheet" />
</head>
<body>
<?php
// get main menu
include('../parts/main_menu.php');
?>

<div class="container-fluid text-center main">
    <div class="col-sm-4 text-center">
    </div>
    <div class="col-sm-4 text-center">
        <label for="pgp-msg">Encrypted Code:</label>
        <textarea rows="15" class="form-control" name="pgp-msg" id="pgp-msg"><?php echo $pgpmessage; ?></textarea>
        <form class="form" action="pgp.php" method="post">
            <?php echo $message;?>
            <label for="user-input">Decrypted Code:</label>
            <input type="text" name="user-input" class="form-control" id="user-input" />
            <input type="hidden" id="pgp-key2" name="pgp-key2" value="<?php echo $_POST['pgp-key'];?>"/>
            <br/>
            <input type="submit" id="decrypted_code" name="decrypted_code" class="btn btn-primary form-control" value="Set Public Key"/>
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
