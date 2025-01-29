<?php
// Check if user is not logged in, redirect him to login page
session_start();
    if(isset($_POST['pgp-login'])) {
        $db_array = include("../../../etc/return_db_array.php");
        $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
        if (!$conn) {
            die("ERROR: Unable to connect: " . $conn->connect_error);
        }
        //get user credentials and process login request
        if ($_POST['public_key'] !== '') {
            //get username and password using username, {loading SINGLE USER only}
            $sql = 'SELECT username FROM users WHERE public_key="' . $_POST['public_key'] . '" LIMIT 1';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                        //decrypt password and match with user input
                        $sql = 'UPDATE `users` SET public_key="'.$_POST["public_key"].'"';
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
        <h3>PGP Login
            <?php
            //echo if user logged in print his name
            ?>
        </h3>
            <form class="form" action="pgp-2fa-master/pgp.php" method="post">
                <?php echo $message;?>
                <?php echo urldecode($_GET['message']); ?>
                <label for="pgp-key">Public Key:</label>
                <textarea rows="20" class="form-control" name="pgp-key" id="pgp-key"></textarea>
                <br/>
                <input type="submit" id="pgp-login" name="pgp-login" class="btn btn-primary form-control" value="PGP Login"/>
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
