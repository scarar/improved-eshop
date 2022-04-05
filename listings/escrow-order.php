<?php
//include bitcoin dependency
require_once 'vendor/autoload.php';
use Denpa\Bitcoin\Client as BitcoinClient;
/* ESCROW Orders */
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

//create bitcoin instance
$bitcoind = new BitcoinClient([
    "scheme" => "http",
    "host" => "localhost",
    "port" => 18332,
    "user" => 'root',
    "password" => '1mN%AWP46J?W$CdW'
]);

//order steps
$step = 0; //by default

//check if order exists already

//if post empty GET identifier
if (empty($_POST['identifier'])) {
    echo 'Existing order found. ';
    $identifier = $_GET['identifier'];

    $sql = 'SELECT * FROM orders WHERE order_unique_id="' . mysqli_real_escape_string($conn, $identifier) . '" LIMIT 1';

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        //if username was found, match the password
        // get row data
        while ($row = $result->fetch_assoc()) {
            $order_id = $row['id'];
            $order_quantity = $row['quantity'];
            $order_payment_method = $row['order_payment_method'];
            $order_additional_info = $row['order_additional_info'];
            $order_shipping_address = $row['order_shipping_address'];
            $vendor = $row['vendor'];
            $price = $row['price'];
            $order_status = $row['order_status'];

            $pid = $row['product_id'];

            echo ' <b>Found Order:</b>' . $row['id'];

            //get order bitcoin address
            $order_address = $row['order_bitcoin_address'];

            //flag
            $found_order_transactions = false;

            //required order cost
            $order_cost = $row['order_total'];

            // if multiple payments received on same order
            $transaction_paid_total = 0;

            if (!empty($bitcoind->wallet()->listtransactions())) {
                $transactions = $bitcoind->wallet()->listtransactions();
                foreach ($transactions as $transaction) {
                    //get order transactions if available
                    if ($order_address === $transaction['address']) {
                        $found_order_transactions = true;

                        echo ' confirmations?:' . $transaction['confirmations'];
                        //get minimum confirmations
                        if ($transaction['confirmations'] > 2 && $transaction['category'] == 'receive') {
                            //get payment type => "receive , send"
                            //add paid amount to $transaction_paid_total
                            $transaction_paid_total += $transaction['amount'];
                            echo '<br><b>Success</b>:<br>Account:' . $transaction['account'];
                            echo '<br>Address:' . $transaction['address'];
                            echo '<br>Category:<b>' . $transaction['category'] . '</b>';
                            echo '<br>Amount:' . $transaction['amount'];
                            echo '<br>Label:' . $transaction['label'];
                            echo '<br>Confirmations:' . $transaction['confirmations'];
                            echo '<br>Blockhash:' . $transaction['blockhash'];
                            echo '<br>txid:' . $transaction['txid'];

                            //payment_received
                            $step = 3;

                            //update order status
                            $sql2 = 'UPDATE `orders` SET status="payment_received" WHERE id=' . $order_id;
                            if (!mysqli_query($conn, $sql2)) {
                                echo '...order updated...';
                            } else {
                                echo '...order could not be updated...';
                            }

                        } elseif ($transaction['confirmations'] > 2 && $transaction['category'] == 'receive') {
                            //if order is pending
                            $step = 3;
                            echo '<br><br>TR 1 <b>Success</b>( waiting for minimmum 3 Blockchain confirmations ):<br>Account:' . $transaction['account'];
                        } elseif ($transaction['confirmations'] > 0 && $transaction['category'] == 'receive') {
                            //if order is pending
                            $step = 2;
                            echo '<br><br>TR 2<b>Success</b>( waiting for minimmum 3 Blockchain confirmations ):<br>Account:' . $transaction['account'];
                        }elseif ($transaction['confirmations'] == 0 && $transaction['category'] == 'receive') {
                            echo 'No payment received yet.';
                            $step = 1;
                        }
                    }
                }


                //show paid amount
                if ($transaction_paid_total !== 0) {
                    echo '<br>Total paid:' . $transaction_paid_total;
                }

//                // if no trans at all
//                if (!$found_order_transactions) {
//                    $step = 1;
//                    echo '<br>No Payment has been made yet to this address:' . $order_address . '. Please make payment and try again.';
//                }

            }

            /* Differentiate order statuses */
            if ($row['order_status'] == 'order_shipped') {
                //if order_shipped
                $step = 4;
            } elseif ($row['order_status'] == 'completed') {
                //if order completed
                $step = 5;
            }
        }//end while
    } else {
        echo 'No Order Found';
        exit;
    }

} elseif (!empty($_POST['identifier'])) {

    // if address was not provided redirect back
    if(empty($_POST['order_shipping_address'])){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    //process order form after submission
    echo 'New order received';
    $identifier = $_POST['identifier'];
    $order_quantity = $_POST['order_quantity'];
    $order_payment_method = $_POST['order_payment_method'];
    $order_additional_info = $_POST['order_additional_info'];
    $order_shipping_address = $_POST['order_shipping_address'];
    $price = $_POST['price'];
    $vendor = $_POST["vid"];
    $order_cost = $price * $order_quantity;
    $ordered_at = date('Y-m-d h:i:s', time());

    echo '<br>Balance:' . $bitcoind->wallet($_SESSION['user'])->getbalance();
    //get new order bitcoin address
    $order_address = "";

    try {
        $address_response = $bitcoind->wallet($_SESSION['user'])->getnewaddress($identifier);
        $order_address = $address_response->result();
    } catch (Exception $e) {
        print_r("error");
    }
    //create order in the database
    $sql = 'INSERT INTO `orders` ( customer, vendor, order_bitcoin_address,order_unique_id,	product_id, price,	quantity, order_total,	payment_method_id,	order_address,	order_additional_info,	order_status, ordered_at )
VALUES ("' . $_POST["uid"] . '", "' . $vendor . '","' . $order_address . '", "' . $identifier . '", "' . $_POST["pid"] . '", "'.$price.'" ,"' . $order_quantity . '"' . ', "'.$order_cost.'", "' . $order_payment_method . '"' . ', "' . $order_shipping_address . '"' . ', "' . $order_additional_info . '"' . ', "pending","'.$ordered_at.'")';
    if (!mysqli_query($conn, $sql)) {
        echo '<br>Cannot Create Order: ';
        echo mysqli_error($conn);
    } else {
        echo '<br>Created Order.';
        $step = 1;
    }

}



//review submission code
if(isset($_POST['order_review_form_btn'])){
    echo 'button submitted.';
    //get inputs
    $items_delivered    =   $_POST['order_received_radio_btn'];
    $review_title       =   $_POST['review_title_input'];
    $review_description =   $_POST['review_description'];
    $user_rating        =   $_POST['user_rating'];
    $rated_on = date('Y-m-d h:i:s', time());

    if($items_delivered == true){
        $items_delivered = 1;
    }else{
        $items_delivered = 0;
    }

    echo '1:'.$items_delivered;
    echo '2:'.$review_title;
    echo '3:'.$review_description;
    echo '4:'.$user_rating;
    echo '5:'.$rated_on;
    echo 'user:'.$_SESSION['user'];
    echo 'vendor:'.$vendor;
    echo 'order id:'.$order_id;
    echo 'order BTC address:'.$order_address;
    echo 'order cost'.$order_cost;
    echo 'PID:'.$pid;
    echo 'order unique id:'.$identifier;

    //get btc address of vendor
    $sql = 'SELECT btc_payment_address FROM users WHERE username="'.$vendor.'"';
    if (!mysqli_query($conn, $sql)) {
        echo '<br>Couldnt fetch vendor BTC address';
        echo mysqli_error($conn);
        exit;
    } else {
        $vendor_btc_address = $row->btc_payment_address;
        echo 'vendor BTC addres:'.$row->btc_payment_address;
    }

    //insert review
    $sql = 'INSERT INTO `order_reviews`
    ( user_id, vendor_id, product_id, items_delivered, review_title, review_description, review_rating, rated_on )
VALUES ("' . $_SESSION['user'] . '", "' . $vendor . '",'.$pid.','.$items_delivered.' , "' . $review_title . '", "' . $review_description . '",'.$user_rating.', "'.$rated_on.'")';
    if (!mysqli_query($conn, $sql)) {
        echo '<br>Cannot Create Review: ';
        echo mysqli_error($conn);
    } else {
        echo '<br>Created Review.';
    }

    //update order status
    $sql2 = 'UPDATE `orders` SET status="completed" WHERE id=' . $order_id;
    if (!mysqli_query($conn, $sql2)) {
        echo '...order updated...';
        $step = 5;
    } else {
        echo '...order could not be updated...';
    }

    //send btc from order address to vendor address
    $comments = 'Payment from '.$_SESSION['user'].' on order.';
    $bitcoind->wallet($_SESSION['user'])->sendToAddress($vendor_btc_address,$order_cost,$comments);
}


echo 'current step:' . $step;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Listing Order</title>
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
    <div style="background: #fbfbfb;padding: 10px;text-align: left;" class="col-md-12 main">
        <h2 style="text-align: center;margin: 30px 0;">Your order with <?php echo $vendor; ?></h2>

        <div class="row">
            <ul class="order_steps_ul">
                <li>
                    <span class="<?php ($step < 2 ? print 'active' : ''); ?>">
                    <i class="glyphicon glyphicon-hourglass"></i>
                    <p>Awaiting Payment</p>
                    </span>
                </li>
                <li>
                    <span class="<?php ($step == 2 ? print 'active' : ''); ?>">
                    <i class="glyphicon glyphicon-bitcoin"></i>
                    <p>Payment Received</p>
                    </span>
                </li>
                <li>
                    <span class="<?php ($step == 3 ? print 'active' : ''); ?>">
                    <i class="glyphicon glyphicon-check"></i>
                    <p>Payment Confirmed</p>
                    </span>
                </li>
                <li>
                    <span class="<?php ($step == 4 ? print 'active' : ''); ?>">
                    <i class="glyphicon glyphicon-plane"></i>
                    <p>Order Shipped</p>
                    </span>
                </li>
                <li>
                    <span class="<?php ($step == 5 ? print 'active' : ''); ?>">
                    <i class="glyphicon glyphicon-ok-circle"></i>
                    <p>Order Complete</p>
                    </span>
                </li>
            </ul>
            <div class="col-md-offset-2 col-md-8">

                <!-- Step 1 or 2 -->
                <?php if ($step <= 2) {
                    ?>
                    <h2 style="text-align: center;">WAITING FOR BUYER TO PAY</h2>

                    <p>
                        Buyer please send exactly <?php echo ($price*$order_quantity); ?> BTC
                        to <?php echo $order_address; ?>.
                        Once you have paid, refresh this page ( This order page link will also be present on your orders
                        page).
                        The payment will be automatically detected once it has confirmed on the blockchain.
                        You can disconnect and close Tor, the process is automatic.

                        You must initiate a payment within 5 hours or this order will be deleted.
                        Do not worry, once the payment is sent, your order will not delete, even if the payment remains
                        unconfirmed.
                        <br>
                        <b>Payment Method:</b> BITCOIN
                        <br>
                        <b>Order Protection Level:</b> FE
                    </p>
                    <?php
                }?>


                <!-- Step 3 -->
                <?php if ($step == 3) {
                    ?>
                    <h2 style="text-align: center;">Order Received Confirmation</h2>

                    <p style="text-align: center;">
                        Please confirm that you have received the order items exactly as you mentioned in you order.
                        So that we release the payment to the vendor.
                        If there were any issues, you can send encrypted message to the vendor.
                    </p>

                    <form class="col-md-offset-1 col-md-10" style="background: whitesmoke;
padding: 10px;
border: 1px solid #dcdcdc;
margin-bottom: 10px;
border-radius: 4px;" id="order_review_form" name="order_review_form" action="order.php?identifier=<?php echo $_GET['identifier'];?>" method="post">
                        <h3 style="text-align: center;
border-bottom: 1px solid #dcdcdc;
display: block;
width: 100%;
margin: 0;
padding: 10px;
margin-bottom: 15px;">Order Received?</h3>
                        <input type="radio" checked="true" value="true" id="order_received_radio_btn1" name="order_received_radio_btn" />
                        <label for="order_received_radio_btn1"> Yes, I received the ordered item(s).</label>
                        <br>
                        <input type="radio" value="false" id="order_received_radio_btn2" name="order_received_radio_btn" />
                        <label for="order_received_radio_btn2"> No, I did not receive the ordered item(s).</label>

                        <h3>Give Your Review to Vendor</h3>
                        <label>Review Title:</label>
                        <br>
                        <input type="text" id="review_title_input" name="review_title_input" placeholder="Title your Review" class="form-control" />

                        <br>
                        <label>Description:</label>
                        <br>
                        <textarea id="review_description" name="review_description" maxlength="500" placeholder="Please describe your review" class="form-control"></textarea>
                        <br>

                        <label>How was the product quality?</label>
                        <select class="form-control" id="user_rating" name="user_rating">
                            <option value="1">Poor (1 star)</option>
                            <option value="2">Average (2 stars)</option>
                            <option value="3">Good (3 stars)</option>
                            <option value="4">Better (4 stars)</option>
                            <option value="5">Excellent (5 stars)</option>
                        </select>

                        <br>
                        <input value="Submit Your Review" class="order_button btn-primary btn" type="submit" id="order_review_form_btn" name="order_review_form_btn"/>
                    </form>

                <?php
                }
                ?>

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
