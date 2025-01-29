<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SESSION['user'] == '') {
    header('Location:http://' . $_SERVER["HTTP_HOST"] . '/login');
    exit;
}

require_once '../vendor/autoload.php';
use Denpa\Bitcoin\Client as BitcoinClient;

/* function fetchWalletBalance($username, $bitcoind, $conn) {
    $balance = 0;
    try {
        $listWallets = $bitcoind->listwallets()->get();
        $isWalletLoaded = in_array($username, $listWallets);

        if (!$isWalletLoaded) {
            $loadWalletResult = $bitcoind->loadwallet($username);
            if (!$loadWalletResult->get()) {
                error_log("Error: Wallet could not be loaded.");
                return 0;
            }
        }

        $wallet = $bitcoind->wallet($username);
        $balanceInfo = $wallet->getbalance();
        if ($balanceInfo) {
            $balance = $balanceInfo->get();
            $updateSql = "UPDATE users SET btc_balance = ? WHERE username = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ds", $balance, $username);
            $stmt->execute();

            if ($stmt->error) {
                error_log("Error updating balance: " . $stmt->error);
                return 0;
            }
            $stmt->close();
        } else {
            error_log("Error: Unable to fetch wallet balance.");
            return 0;
        }
    } catch (Exception $e) {
        error_log("Error in fetchWalletBalance: " . $e->getMessage());
        return 0;
    }
    return $balance;
} */
/* function fetchWalletBalance($username, $bitcoind, $conn) {
    $balance = 0;
    try {
        $listWallets = $bitcoind->listwallets()->get();
        
        // Ensure $listWallets is an array
        if (!is_array($listWallets)) {
            $listWallets = [$listWallets]; // Or handle the conversion appropriately
        }

        $isWalletLoaded = in_array($username, $listWallets);

        if (!$isWalletLoaded) {
            $loadWalletResult = $bitcoind->loadwallet($username);
            if (!$loadWalletResult->get()) {
                error_log("Error: Wallet could not be loaded.");
                return 0;
            }
        }

        $wallet = $bitcoind->wallet($username);
        $balanceInfo = $wallet->getbalance();
        if ($balanceInfo) {
            $balance = $balanceInfo->get();

            // Unload the wallet after use
            $bitcoind->unloadwallet($username);

            // Update the database with the new balance
            $updateSql = "UPDATE users SET btc_balance = ? WHERE username = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ds", $balance, $username);
            $stmt->execute();

            if ($stmt->error) {
                error_log("Error updating balance: " . $stmt->error);
                return 0;
            }
            $stmt->close();
        } else {
            error_log("Error: Unable to fetch wallet balance.");
            return 0;
        }
    } catch (Exception $e) {
        error_log("Error in fetchWalletBalance: " . $e->getMessage());
        return 0;
    }
    return $balance;
} */
function fetchWalletBalance($username, $bitcoind, $conn) {
    $balance = 0;
    try {
        // Check if the wallet is listed
        $listWallets = $bitcoind->listwallets()->get();
        if (!is_array($listWallets)) {
            $listWallets = [$listWallets];
        }

        // Check if the user's wallet is loaded
        $isWalletLoaded = in_array($username, $listWallets);
        
        // Load the wallet if it's not loaded
        if (!$isWalletLoaded) {
            $loadWalletResult = $bitcoind->loadwallet($username);
            if (!$loadWalletResult->get()) {
                error_log("Error: Wallet could not be loaded or does not exist.");
                return 0;
            }
        }

        // Fetch the wallet balance
        $wallet = $bitcoind->wallet($username);
        $balanceInfo = $wallet->getbalance();
        if ($balanceInfo) {
            $balance = $balanceInfo->get();
        } else {
            error_log("Error: Unable to fetch wallet balance.");
            return 0;
        }

        // Update the user's balance in the database
        $updateSql = "UPDATE users SET btc_balance = ? WHERE username = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ds", $balance, $username);
        $stmt->execute();

        if ($stmt->error) {
            error_log("Error updating balance: " . $stmt->error);
            return 0;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in fetchWalletBalance: " . $e->getMessage());
        return 0;
    }
    return $balance;
}


function withdrawBitcoin($username, $amount, $recipientAddress, $bitcoind, $conn) {
    try {
        $wallet = $bitcoind->wallet($username);
        $balance = fetchWalletBalance($username, $bitcoind, $conn);

        if ($balance < $amount) {
            throw new Exception('Insufficient funds. Available: ' . $balance . ' BTC.');
        }

        $transaction = $wallet->sendtoaddress($recipientAddress, $amount, "", "", true); // Automatically subtracts fee
        return $transaction->get();
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

$bitcoind = new BitcoinClient([
    'scheme' => 'http',
    'host' => 'localhost',
    'port' => 18332,
    'user' => 'scarar',
    'password' => 'navy'
]);

$db_array = include("../../../../etc/return_db_array.php");
$conn = mysqli_connect('localhost', $db_array['db_user'], $db_array['db_password'], $db_array['db_name']);

if (!$conn) {
    die("ERROR: Unable to connect: " . mysqli_connect_error());
}

$username = $_SESSION['user'];
$balance = fetchWalletBalance($username, $bitcoind, $conn);
$balance_formatted = number_format($balance, 10, '.', '');
$maxWithdrawableAmount = $balance; // Set to entire balance for max withdrawal

$sql = 'SELECT btc_payment_address FROM users WHERE username = "' . $username . '" LIMIT 1';
$result = mysqli_query($conn, $sql);

$btc_address = "Not found";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $btc_address = $row['btc_payment_address'];
}

$withdrawalMessage = '';
if (isset($_POST['withdraw'])) {
    $recipientAddress = $_POST['withdraw_address'];
    $withdrawAmount = (float)$_POST['amount'];

    if ($_POST['withdraw'] === 'max') {
        $withdrawAmount = $maxWithdrawableAmount;
    }

    $withdrawalMessage = withdrawBitcoin($username, $withdrawAmount, $recipientAddress, $bitcoind, $conn);
}

mysqli_close($conn);

// Your existing HTML and remaining PHP code
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>BTC Address for Payments</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/bootstrap/css/bootstrap.min.css">
    <link href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/style.css" rel="stylesheet" />
</head>
<body>
    <?php include('../parts/main_menu.php'); ?>

    <div class="container-fluid text-center main">
        <div class="col-sm-3 text-center sidebar">
            left sidebar
        </div>
        <div class="col-sm-9 text-left main">
            <h3><span class="glyphicon glyphicon-cog"></span> Set BTC Address</h3>
            <ul class="nav nav-pills">
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/profile/settings">Settings</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/profile/reset-password">Reset Password</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/profile/pin">Reset PIN</a></li>
                <li><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/profile/pgp-2fa">PGP/2FA</a></li>
                <li class="active"><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/profile/btc_address">BTC Payment Address</a></li>
            </ul>
            <br>
            <div class="row">
                <div class="col-md-8">
                    <label for="btc_address">Your BTC Address for payments:</label>
                    <br>
                    <label style="font-weight: normal; font-size: 18px;"><?php echo $btc_address; ?></label>
                    <br>
                </div>
                <div class="col-md-4">
                    <span style="font-size: 14px; color: black; font-weight: normal;">
                    <b>Balance:</b></span>
                    <p><?php echo $balance_formatted; ?> BTC</p>
		    <span style="font-size: 16px; color: black; font-weight: bold;"> 
                    <small>Max Withdrawable:</span><span style="font-size: 16px; color: black; font-weight: normal;"> <?php echo number_format($maxWithdrawableAmount, 10, '.', ''); ?> BTC</small></span>
		    
                </div>
            </div>

            <!-- Withdrawal Form -->
            <div class="row">
                <div class="col-md-12">
                    <h4>Withdraw Bitcoin</h4>
                    <?php if (!empty($withdrawalMessage)) echo "<p>$withdrawalMessage</p>"; ?>
                    <form action="btc_address.php" method="post">
                        <div class="form-group">
                            <label for="amount">Amount (BTC):</label>
                            <input type="text" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="form-group">
                            <label for="withdraw_address">Withdraw To Address:</label>
                            <input type="text" class="form-control" id="withdraw_address" name="withdraw_address" required>
                        </div>
                        <button type="submit" name="withdraw" value="withdraw" class="btn btn-primary">Withdraw</button>
<!--                        <button type="submit" name="withdraw" value="max" class="btn btn-secondary">Send Max Amount</button>     -->
			<button type="button" id="sendMaxAmount" class="btn btn-secondary">Send Max Amount</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="container-fluid text-center">
        <p>Footer Text</p>
    </footer>
    <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("sendMaxAmount").addEventListener("click", function () {
                // Set the Amount (BTC) input field to the Max Withdrawable amount
                var maxAmount = <?php echo json_encode($maxWithdrawableAmount); ?>;
                document.getElementById("amount").value = maxAmount;
            });
        });
    </script> -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("sendMaxAmount").addEventListener("click", function () {
            // Retrieve the max withdrawable amount from the server-side PHP variable
            var maxAmount = <?php echo json_encode($maxWithdrawableAmount); ?>;
            // Set the value of the 'amount' input field to the max withdrawable amount
            document.getElementById("amount").value = maxAmount;
            // You might want to trigger any other necessary actions here, like form validation
        });
    });
</script>
</body>
</html>
