<?php
// Check if user is not logged in, redirect him to login page
session_start();
if($_SESSION['user']==''){
    header("Location:http://".$_SERVER['HTTP_HOST']."/login.php");
    exit;
}else {
    $db_array = include("../../../../etc/return_db_array.php");
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
    }
}

//get counts for user feedbacks
$sql = 'SELECT feedback_value, count(feedback_value)
 FROM `user_feedbacks` WHERE vendor_id="' . mysqli_real_escape_string($conn, $_GET['v']) . '"
 GROUP by feedback_value';

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // get row data
    while ($row = $result->fetch_assoc()) {
        if($row['feedback_value'] == 0){
            $negatives = $row['feedback_value'];
        }
        if($row['feedback_value'] == 1){
            $neutrals  = $row['feedback_value'];
        }
        if($row['feedback_value'] == 2){
            $positives = $row['feedback_value'];
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="../style.css" media="all" rel="stylesheet" />
</head>
<body>
<?php
// get main menu
include('../parts/main_menu.php');
?>
<div class="container-fluid text-center main">
    <div style="background: #fbfbfb;padding: 10px;text-align: left;" class="col-md-12">
        <h3><?php echo $_GET['v'];?></h3>
        About:<br>
        <small>
            <?php
            $db_array = include("../../../../etc/return_db_array.php");

            //get user id
            $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
            if (!$conn) {
                die("ERROR: Unable to connect: " . $conn->connect_error);
            }

            $sql = 'SELECT user_id FROM users WHERE username="' . $_GET['v'] . '" LIMIT 1';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data
                while ($row = $result->fetch_assoc()) {
                    $user_id = $row['user_id'];
                }
            }
            //                    echo 'user:'.$_SESSION['user'];
            $sql2 = 'SELECT * FROM user_meta WHERE meta_key="user_description" AND user_id=' . $user_id . ' LIMIT 1';
            $result2 = $conn->query($sql2);
            if ($result2->num_rows > 0) {
                while ($row2 = $result2->fetch_assoc()) {
                    echo $row2['meta_value'];
                }
            }
            ?>
        </small>
        <br><br>
        <h3>Feedbacks Summary:</h3>
        <div class="row">
            <div class="col-md-3">
                &nbsp;
            </div>
            <div class="col-md-2">
                <label>Positive</label>
                <br>
                <?php echo $positives;?>
            </div>
            <div class="col-md-2">
                <label>Neutral</label>
                <br>
                <?php echo $neutrals;?>
            </div>
            <div class="col-md-2">
                <label>Negative</label>
                <br>
                <?php echo $negatives;?>
            </div>
            <div class="col-md-3">
                &nbsp;
            </div>
        </div>
        <br>
        <h3>Customer Reviews:</h3>
        <?php
        //get total vendor products
        $sql = 'SELECT * FROM order_reviews WHERE vendor_id="'.$_GET['v'].'"';
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            //$total_pages =  ceil($result->num_rows / 5 );

            while ($row = $result->fetch_assoc()) {
                echo '<div class="customer_review">';
                if($row['review_rating'] == 1){
                    $width = '0px';
                }elseif($row['review_rating'] == 2){
                    $width = '20px';
                }elseif($row['review_rating'] == 3){
                    $width = '38px';
                }elseif($row['review_rating'] == 4){
                    $width = '56px';
                }elseif($row['review_rating'] == 5){
                    $width = '78px';
                }
                echo '<span class="review_stars_empty"></span><span style="width:'.$width.';" class="review_stars_filled"></span> <b style="margin-left:105px;">'.$row['review_title'].'</b><br>';
                echo 'by <a href="http://'.$_SERVER['HTTP_HOST'].'/vendors/vendor_products?v='.$row['user_id'].'" title="'.$row['user_id'].'">'.$row['user_id'].'</a> on '.$row['rated_on'];
                echo '<br><small>'.$row['review_description'].'</small>';
                echo '</div><hr style="border-bottom: 1px solid #dcdcdc;margin: 5px 0;"/><br>';
            }
        }
        ?>
    </div>
    <div style="background: #fbfbfb;padding: 10px;text-align: left;" class="col-md-12 main">
            <h3 style="margin-top: 20px;">User Products:</h3>
            <?php
            //get total vendor products
            $sql = 'SELECT * FROM products WHERE vendor="'.$_GET['v'].'"';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $total_pages =  ceil($result->num_rows / 5 );
            }
            //get products from categories
            if(  $_GET['page'] == '' || $_GET['page'] == 1 || $_GET['page'] == 0 ){
                $start = 0;
            }else{
                $start = ( ($_GET['page'] - 1) * 5 );
            }

            $sql = 'SELECT * FROM products WHERE vendor="'.$_GET['v'].'" LIMIT '.$start.', 5';
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                //if username was found, match the password
                // get row data

                while ($product_row = $result->fetch_assoc()) {
//                var_dump($product_row);
                    echo '<div class="row p_row ">';
                    echo '<a href="http://'.$_SERVER["HTTP_HOST"].'/listings/single-product?i='.$product_row["id"].'" title="Click to view product">';
                    echo '<div class="col-md-3 p_image_tile">';
                    echo '<span class="p_image"><img src="../profile/uploads/'.$product_row["image"].'" alt="'.$product_row["title"].' image"/></span>';
                    echo '</div>';
                    echo '<div class="col-md-7 p_tile">';
                    echo '<b class="p_title">'.$product_row["title"].'</b>';
                    echo '</a>';
                    echo '<br><span class="p_price"><b>Price: $</b>'.$product_row["price"].'</span>';
                    echo '<p class="p_short_desc">'.$product_row["short_description"].'</p>';
                    if($product_row["requires_fe"] == 1){
                        echo '<p class="p_requires_fe"><b>Requires FE</b></p>';
                    }
                    echo 'Vendor: <a href="#" title="'.$product_row["vendor"].'">'.$product_row["vendor"].'</a>';

                    $sql2 = 'SELECT * FROM categories WHERE id='.$product_row["category_id"];
                    $cat_result = $conn->query($sql2);
                    if ($cat_result->num_rows > 0) {
                        while ($cat_row = $cat_result->fetch_assoc()) {
                            echo '<p class="p_category">Category: '.$cat_row["title"].'</p>';
                        }
                    }
                    echo '<p class="p_ships_from">Ships From: Not Specified</p>';

//                    $tags = explode(",",$product_row["meta_tags"]);
//                    echo '<p class="p_title">';
//                    foreach ($tags as $tag){
//                        echo '<a href="#" title="tag">'.$tag.'</a>';
//                    }
                    echo '</div>';
                    echo '<div class="col-md-2 p_tile">';
                    echo '<button id="order_'.$product_row["title"].'" class="order_button btn-primary btn btn-block fullheight">Order Now</button>';
                    echo '</div>';
                    echo '</div>';

                }

            }else{
                echo '<p style="color:grey">User has not uploaded any Product.</p>';
            }
            //mysqli::close();
            ?>
        <div class="col-md-12 text-center">
            <?php
            for($p=1;$p<=$total_pages;$p++){
                echo '<a class="post_nav_link" href="http://'.$_SERVER['HTTP_HOST'].'/vendors/vendor_products?v='.$_GET['v'].'&page='.$p.'" title="page '.$p.'">'.$p.'</a>';
            }
            ?>
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
