<?php
// Check if user is not logged in, redirect him to login page
session_start();
if($_SESSION['user']==''){
    header('Location:http://'.$_SERVER["HTTP_HOST"].'/login');
    exit;
}else{
//update only about you section
if(isset($_POST['update_about_you'])){
    $db_array = include("../../../../etc/return_db_array.php");

    //get user id
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
    }

    $sql = 'SELECT user_id FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        //if username was found, match the password
        // get row data
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['user_id'];
        }
    }

    //check if about you was existing or not
    $sql = 'SELECT meta_value FROM user_meta WHERE user_id=' . $user_id . ' AND meta_key="user_description" LIMIT 1';
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        //if username was found, match the password
        // get row data
        while ($row = $result->fetch_assoc()) {
            if(!empty($row['meta_value'])){
                //update
                $sql2 = 'UPDATE `user_meta` SET meta_key="user_description", meta_value="'.$_POST["about_you"].'", user_id='.$user_id;
                    if (!mysqli_query($conn, $sql2)) {
                        $message = '<div id="message" class="alert alert-warning">';
                        $message .= 'Profile could not be updated.';
                        $message .= mysqli_error($conn);
                        $message .= '</div>';
                    }else{
//                        echo '$user_id:'.$user_id;
//                        echo 'desc:'.$_POST["about_you"];

                        $message = '<div id="message" class="alert alert-success">';
                        $message .= 'Profile Updated.';
                        $message .= '</div>';
                        //change permissions back
                    }
            }else{
                echo 'empty meta';
            }

        }
    }else{
//        echo '2:';

        //Insert
        $sql2 = 'INSERT INTO `user_meta` (user_id,meta_key,meta_value) values ('.$user_id.',"user_description","'.$_POST["about_you"].'")';
        if (!mysqli_query($conn, $sql2)) {
            $message = '<div id="message" class="alert alert-warning">';
            $message .= 'Profile could not be updated.';
            $message .= mysqli_error($conn);
            $message .= '</div>';
        }else{
            echo '$user_id:'.$user_id;
            echo 'desc:'.$_POST["about_you"];

            $message = '<div id="message" class="alert alert-success">';
            $message .= 'Profile Updated.';
            $message .= '</div>';
            //change permissions back
        }
    }


}else{
    $db_array = include("../../../../etc/return_db_array.php");

    //get user id
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
    }

    $sql = 'SELECT user_id FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        //if username was found, match the password
        // get row data
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['user_id'];
        }
    }
}

    //update profile picture
    if(isset($_POST['upload_profile'])){
        if(isset($_FILES['profile_image']) AND $_FILES['profile_image']['tmp_name'] !=''){
            //file was set to upload
            //props
            $photo_file_name = $_FILES['profile_image']['name'];
            $photo_file_tmp = $_FILES['profile_image']['tmp_name'];
            $photo_file_size = $_FILES['profile_image']['size'];
            $photo_file_error = $_FILES['profile_image']['error'];
            //get extension
            $photo_file_ext = explode('.', $photo_file_name);
            $photo_file_ext = strtolower(end($photo_file_ext));
            $allowed   = array('jpg','jpeg','png','gif');
//            echo 'ext:'.$photo_file_ext;
            if(in_array( $photo_file_ext, $allowed )){
                if( $photo_file_error === 0 ){
//                    echo 'file size:'.$photo_file_size;
                    if($photo_file_size <= 500048 ){
                        $photo_name_new = uniqid('',true). '.' . $photo_file_ext;
                        $photo_dest = 'uploads/' . $photo_name_new;
                        chmod("uploads/", 0744);
                        if(move_uploaded_file( $photo_file_tmp , $photo_dest)){
                            chmod("uploads/", 0755);
                            $db_array = include("../../../../etc/return_db_array.php");
                            $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
                            if (!$conn) {
                                die("ERROR: Unable to connect: " . $conn->connect_error);
                            }
                            $sql2 = 'UPDATE `users` SET profile_image="'.$photo_name_new.'" WHERE username="'.$_SESSION['user'].'"';
                            if (!mysqli_query($conn, $sql2)) {
                                $message = '<div id="message" class="alert alert-warning">';
                                $message .= 'Profile image could not be updated.';
                                $message .= mysqli_error($conn);
                                $message .= '</div>';
                            }else{
                                $message = '<div id="message" class="alert alert-success">';
                                $message .= $photo_dest;
                                $message .= '</div>';
                                //change permissions back
                            }

                        }else{
                            $message = '<div id="message" class="alert alert-success">';
                            $message .= 'Error Uploading profile photo.';
                            $message .= '</div>';
                        }
                    }
                }
            }

        }else if(isset($_POST['deleteAvatar'])){
            //echo 'chk box value:'.$_POST['deleteAvatar'];
            $db_array = include("../../../../etc/return_db_array.php");
            $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
            if (!$conn) {
                die("ERROR: Unable to connect: " . $conn->connect_error);
            }

            $sql = 'SELECT profile_image FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                     $p_image = $row['profile_image'];
                    //del the profile image from dir
                    unlink('uploads/'.$row['profile_image']);
                }
            }

            $sql2 = 'UPDATE `users` SET profile_image="" WHERE username="'.$_SESSION['user'].'"';
            if (!mysqli_query($conn, $sql2)) {
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'Profile image could not be deleted:';
                $message .= mysqli_error($conn);
                $message .= '</div>';
            }else{

                $message = '<div id="message" class="alert alert-success">';
                $message .= 'Profile photo deleted.';
                $message .= '</div>';
                //change permissions back
            }




            //update the DB column form profile_image

        }else{
            echo 'photo file was not set.';
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
        <?php echo $message;?>
        <h3><span class="glyphicon glyphicon-cog"></span> Settings</h3>
        <ul class="nav nav-pills">
            <li class="active"><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/settings">Settings</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/reset-password">Reset Password</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pin">PIN</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/pgp-2fa">PGP/2FA</a></li>
            <li><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/btc_address">BTC Payment Address</a></li>

        </ul>
        <br>
        <div class="row">
            <div class="col-lg-5" >
                <div class="thumbnail" id="userAvatar">
                    <form enctype="multipart/form-data" style="min-height:500px;" method="post" action="http://<?php echo $_SERVER['HTTP_HOST'];?>/profile/settings">
                    <?php
                    $db_array = include("../../../../etc/return_db_array.php");
                    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
                    if (!$conn) {
                        die("ERROR: Unable to connect: " . $conn->connect_error);
                    }

                    $sql = 'SELECT profile_image, referral_code, referral FROM users WHERE username="' . $_SESSION['user'] . '" LIMIT 1';
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $image = $row['profile_image'];
                            $referral_code = $row['referral_code'];
                            $user_referral_code = $row['referral'];
                        }
                    }
                    if($image != ''){
                        echo '<img style="max-height: 250px;width: 250px;" src="uploads/'.$image.'" style="max-width:250px;">';
                    }else{
                        echo '<img style="max-height: 250px;width: 250px;" src="../images/no_image.png" style="max-width:250px;">';
                    }
                    ?>


                    <div class="caption">
                        <div class="checkbox">
                            <label>
                                <input name="deleteAvatar" value="true" type="checkbox">
                                <span class="control-label">Delete image</span>
                            </label>
                        </div>
                        <label for="file">Upload new image*:</label>
                        <input name="profile_image" style="width: 100%;" type="file" id="file">
                        <input type="submit" name="upload_profile" value="Update"/>
                    </div>
                    <span class="text-warning" style="float: right;">* Image must not be larger then 100kb. Supported formats are JPG/JPEG, PNG and GIF.</span>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">

                <div class="col-lg-6">
                    <div class="container-fluid">
                        <div class="form-group">
                            <label class="control-label" for="txtReferalCode">Referral code</label>
                            <h3>
                                <?php echo $user_referral_code;?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="container-fluid">
                        <div class="form-group">
                            <label class="control-label" for="txtReferalCode">Referral count</label>
                            <h3>
                                <?php
                                $sql = 'SELECT count(referral_code) FROM users WHERE username!="' . $_SESSION["user"] . '" AND referral_code="'.$user_referral_code.'"';
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                        echo $row['count(referral_code)'];
                                    }
                                }?>&nbsp;user(s)
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="container-fluid">
                            <div class="form-group">
                                Share <i>Referral code</i> or following URL with your friends
                                <div class="list-group" style="margin-bottom: 0px;">
                            <span class="list-group-item list-group-item-success">
                                http://24pmnq7f764xmtw5.onion/register?referer=<?php echo $user_referral_code;
                                ?>
                            </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <b>About You:</b>
                    <?php
//                    echo 'user:'.$_SESSION['user'];
                    $sql2 = 'SELECT * FROM user_meta WHERE meta_key="user_description" AND user_id=' . $user_id . ' LIMIT 1';
                    $result2 = $conn->query($sql2);
                    if ($result2->num_rows > 0) {
                        while ($row2 = $result2->fetch_assoc()) {
                            ?>
                            <form id="about_you_form" method="post">
                                <textarea style="width: 100%;" id="about_you" name="about_you"><?php echo $row2['meta_value'];?></textarea>
                                <input type="submit" value="Update" id="update_about_you" name="update_about_you"/>
                            </form>
                            <?php
                        }
                    }else{
                    ?>
                        <form id="about_you_form" method="post">
                            <textarea style="width: 100%;" id="about_you" name="about_you"></textarea>
                            <input type="submit" value="Update" id="update_about_you" name="update_about_you"/>
                        </form>
                        <?php
                    }
                    ?>
                </div>
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
