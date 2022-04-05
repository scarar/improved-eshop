<?php
// Check if user is not logged in, redirect him to login page
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']==''){
    if (isset($_SERVER['HTTP_HOST'])) {
        header('Location:http://'.$_SERVER["HTTP_HOST"].'/login');
        exit;
    }
}else{
    $db_array = include("../../../etc/return_db_array.php");
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
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
    <div class="col-md-3 text-center sidebar">
        <?php
        include('parts/sidebar.php');
        ?>
    </div>
    <div style="background: #fbfbfb;padding: 10px;text-align: left;" class="col-md-9 main">
        <h3 style="margin-top: 20px;">Products</h3>
        <?php
        //get total vendor products
        if($_GET['category'] !== '' AND is_numeric($_GET['category'])){
            $sql = 'SELECT * FROM products WHERE category_id='.$_GET['category'];
        }else{
            $sql = 'SELECT * FROM products';
        }

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $total_pages =  ceil($result->num_rows / 5 );
        }

        //get products from categories
        if( $_GET['page'] == '' || $_GET['page'] == 1 || $_GET['page'] == 0 ){
            $start = 0;
        }else{
            $start = ( ($_GET['page'] - 1) * 5 );
        }
        //get products from categories
        if($_GET['category'] !== '' AND is_numeric($_GET['category'])){
            $sql = "SELECT * FROM products WHERE category_id=".$_GET['category']." LIMIT 0, 5";
        }else{
            $sql = 'SELECT * FROM products LIMIT '.$start.', 5';
        }
        $result2 = $conn->query($sql);
        if ($result2->num_rows > 0) {
            //if username was found, match the password
            // get row data

            while ($product_row = $result2->fetch_assoc()) {
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
                echo '<br><span class="p_short_desc">'.$product_row["short_description"].'</span>';
//                if($product_row["requires_fe"] == 1){
//                    echo '<p class="p_requires_fe"><b>Requires FE</b></p>';
//                }
                echo '<br>Vendor: <a href="http://'.$_SERVER['HTTP_HOST'].'/vendors/vendor_products" title="'.$product_row["vendor"].'">'.$product_row["vendor"].'</a>';

                $sql2 = 'SELECT * FROM categories WHERE id='.$product_row["category_id"];
                $cat_result = $conn->query($sql2);
                if ($cat_result->num_rows > 0) {
                    while ($cat_row = $cat_result->fetch_assoc()) {
                        echo '<br><span class="p_category">Category: '.$cat_row["title"].'</span>';
                    }
                }
                echo '<br><span class="p_ships_from">Ships From: Not Specified</span>';

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

            ?>
            <div class="col-md-12 text-center">
                <?php
                for($p=1;$p<=$total_pages;$p++){
                    echo '<a class="post_nav_link" href="http://'.$_SERVER['HTTP_HOST'].'/listings/products?page='.$p.'" title="page '.$p.'">'.$p.'</a>';
                }
                ?>
            </div>
            <?php
            }else{
                $message = '<div id="message" class="alert alert-warning">';
                $message .= 'No products found.';
                $message .= '</div>';

                echo $message;
            }
            ?>

    </div>
</div>
<br/>
<br/>
<footer class="container-fluid text-center">
    <p>Footer Text</p>
</footer>

</body>
</html>
