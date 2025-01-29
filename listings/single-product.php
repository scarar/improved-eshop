<?php
// Check if user is not logged in, redirect him to login page
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == '') {
    header("Location:http://" . $_SERVER['HTTP_HOST'] . "/login.php");
    exit;
} else {
    $db_array = include("../../../../etc/return_db_array.php");
    $conn = @mysqli_connect("localhost", $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);
    if (!$conn) {
        die("ERROR: Unable to connect: " . $conn->connect_error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="all"/>
    <link href="../style.css" rel="stylesheet" media="all"/>
</head>
<body>

<?php
// get main menu
include('../parts/main_menu.php');
?>
<div class="container-fluid text-center main">
    <div style="background: #fbfbfb;padding:20px 10px;text-align: left;" class="col-md-12 main">
        <?php
        //get products from categories
        $pid = $_GET['i'];

        //get quantity
        $sql = 'SELECT * FROM product_meta WHERE product_id=' . $pid . ' AND meta_key=\'quantity\'';
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // if username was found, match the password
            // get row data
            while ($product_row = $result->fetch_assoc()) {
                $quantity = $product_row['meta_value'];
            }
        }
        $sql = 'SELECT * FROM products WHERE id=' . $pid;
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // if username was found, match the password
            // get row data
            while ($product_row = $result->fetch_assoc()) {
                echo '<div class="row " style="margin: 0;padding: 0 10px;">';
                echo '<span class="single_image"><img src="../profile/uploads/' . $product_row["image"] . '" alt="' . $product_row["title"] . ' image"/></span>';
                echo '<h2 class="single_title">' . $product_row["title"] . '</h2>';
                echo 'Vendor: <a href="http://' . $_SERVER['HTTP_HOST'] . '/vendors/vendor_products?v='.$product_row["vendor"].'&page=1" title="' . $product_row["vendor"] . '">' . $product_row["vendor"] . '</a>';
                echo '<br><span class="p_price"><b>Price: </b>' . $product_row["price"] . ' BTC</span>';
                echo '<br><span>Quantity: ' . $quantity . '</span>';
                echo '<p class="p_short_desc">About Item: ' . $product_row["description"] . '</p>';
                if ($product_row["requires_fe"] == 1) {
                    echo '<p class="p_requires_fe"><b style="color:orange">Requires FE</b></p>';
                }else{
                    echo '<p class="p_requires_fe"><b style="color:orange">ESCROW Order</b></p>';
                }
                $sql2 = 'SELECT * FROM categories WHERE id=' . $product_row["category_id"];
                $cat_result = $conn->query($sql2);
                if ($cat_result->num_rows > 0) {
                    while ($cat_row = $cat_result->fetch_assoc()) {
                        echo '<p class="p_category">Category: ' . $cat_row["title"] . '</p>';
                    }
                }
                echo '<p class="p_ships_from">Ships From: Not Specified</p>';

                echo '</div>';

                echo '<div class="row" style="padding: 16px 10px;
border: 1px solid #dcdcdc;
margin: 15px 10px auto !important;">';


                //identifier
                //get current timestamp
                //$time = str_replace('-','',time());
                $ordered_at = date('Y-m-d h:i:s', time());
                $ordered_at = str_replace('-','',$ordered_at);
                $ordered_at = str_replace(':','',$ordered_at);
                $ordered_at = str_replace(' ','',$ordered_at);
                $identifier = $pid . strrev($_SESSION['user']) . 'to' . strrev($product_row["vendor"]).$ordered_at;



                echo '<form id="create_order_form" method="post" action="order.php?identifier=' . $identifier . '">';
                echo '<div class="col-md-12 text-center" style="border-bottom: 1px solid #dcdcdc;padding-bottom: 20px;margin-bottom: 20px;"><h3>Place Order</h3>
                <p style="color:red">Please fill in all required fields and submit your order.</p></div>';
                echo '<div class="col-md-3"><h3 style="margin-top:0px">Quantity</h3></div>';
                echo '<div class="col-md-9">';
                echo '<div style="padding-bottom: 10px">Quantity you choose will be multiplied by the single item price (' . $product_row["price"] . ' BTC)</div>';
                echo '<select id="order_quantity" name="order_quantity" class="form-control" style="width:100%;margin-bottom:10px;">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                 </select>';
                echo '<br>';
                echo '</div>';

                echo '<div class="col-md-3"><h3 style="margin-top:0px">Shipping Address</h3></div>';
                echo '<div class="col-md-9">';
                echo '<div style="background: whitesmoke;
padding: 10px;
border: 1px solid #dcdcdc;
margin-bottom: 10px;
border-radius: 4px;">
                            Addresses are encrypted by us automatically with vendor\'s PGP.
                            <br>
                            Please use this format for the shipping address:
                            <br><br>
                            Your Name<br>
                            Your Street Name<br>
                            City, State<br>
                            Zipcode<br>
                            Country<br><br>';
                echo '<textarea placeholder="Add your address here" style="width:100%;" name="order_shipping_address" ></textarea>';
                echo '</div>';
                echo '<br>';
                echo '</div>';

                echo '<div class="col-md-3"><h3 style="margin-top:0px">Additional Info (optional)</h3></div>';
                echo '<div class="col-md-9" style="margin-bottom: 20px;">';
                echo '<textarea placeholder="Additional Info" style="width:100%;border:1px solid #dcdcdc;padding:5px;-webkit-border-radius: 4px;-moz-border-radius: 4px;border-radius: 4px;" name="order_additional_info" ></textarea>';
                echo '</div>';

                echo '<div class="col-md-3"><h3 style="margin-top:0px">Payment Method</h3></div>';
                echo '<div class="col-md-9">';
                echo '<select class="form-control" name="order_payment_method" style="width:100%">
                        <option value="1">BITCOIN - Generate a unique payment address for your order.</option>
                        <option value="2" disabled="">BITCOIN - Pay with balance (instant)</option>
                        </select>';
                echo '<br>';
                echo '<button style="float:right;min-width:200px;" id="order_' . $product_row["title"] . '" class="order_button btn-primary btn">Place Order</button>';
                echo '</div>';

                echo '<div class="col-md-12" style="margin-top:20px;">
                          <div style="background: whitesmoke;
padding: 10px;
border: 1px solid #dcdcdc;
margin-bottom: 10px;
border-radius: 4px;"">
                            <h3>How does this order work?</h3>
                            Step 1. When you press "Place Order" the order is created and you will be given a Bitcoin address to send the BTC payment to.
                            <br>
                            Step 2. The payment will take some time to process. The vendor will then ship the order to the buyer address.
                            <br>
                            Step 3. The buyer waits for the order to arrive in the mail. The buyer press the "Release Bitcoins" button only when the order has arrived.
                            <br>
                            Step 4. Order is complete. Feedback can be made.
                          </div>
                          </div>';

                echo '<input id="price" type="hidden" name="price" value="' . $product_row["price"] . '" />';
                echo '<input id="identifier" type="hidden" name="identifier" value="' . $identifier . '" />';
                echo '<input id="pid" type="hidden" name="pid" value="' . $pid . '" />';
                echo '<input id="uid" type="hidden" name="uid" value="' . $_SESSION['user'] . '" />';
                echo '<input id="vid" type="hidden" name="vid" value="' . $product_row["vendor"] . '" />';

                echo '</form>';
                echo '</div>';
            }
        }
        //mysqli::close();
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
