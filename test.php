<?php
//$guid="c614bdf8-074c-4b8e-9fdb-b1c726cb4b9f";
//$firstpassword="TestBitcoin2018";
//$json_url = "http://localhost:3001/api/v2/create";
//
//$json_data = file_get_contents($json_url);
//
//var_dump($json_data);

require_once 'vendor/autoload.php'
//// Initialize Bitcoin connection/object
//$bitcoin = new Bitcoin('username','password');
// Optionally, you can specify a host and port.
$bitcoind = new BitcoinClient('http://root:1mN%AWP46J?W$CdW@localhost:8332/'); //for test net 18333
// Defaults are:
//	host = localhost
//	port = 8332
//	proto = http

// If you wish to make an SSL connection you can set an optional CA certificate or leave blank
// This will set the protocol to HTTPS and some CURL flags
//$bitcoind->wallet()->setSSL('/full/path/to/mycertificate.cert');

// Make calls to bitcoind as methods for your object. Responses are returned as an array.
// Examples:

//$bitcoind->wallet()->sendtoaddress('0123010230123','1.0','Romeo');
//$bitcoind->wallet()->sendtoaddress('3M1eJsLqUrHHuLobC5JJ7v1gQY3YVWCWBA',10.00,'Romeo Paid');
//$bitcoind->wallet()->getrawtransaction('0e3e2357e806b6cdb1f70b54c3a3a17b6714ee1f0e68bebb44a74b1efd512098',1);
//$bitcoind->wallet()->getblock('000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f');

//$wallet = $bitcoind->wallet()->getwalletinfo();
//var_dump($wallet);
$bitcoind->wallet()->sendtoaddress('2N8JA1sRedzLRDYzJN5r13oztyiGADnDv5s','0.02','NEW ORDER payment');
//$bitcoind->wallet()->generate('101');
//echo 'setaccount:'.$bitcoind->wallet()->setaccount('2NDpNkbjumiGAZVXBgxmp36J4WXq32hJsUf');
echo '<br>Balance:'.$bitcoind->wallet()->getbalance();

// The HTTP status code can be found in $this->status and will either be a valid HTTP status code
// or will be 0 if cURL was unable to connect.
// Example:
echo '<br>Status:'.$bitcoind->wallet()->status;

//check if amount was received
// -> if more than two transtactions were found, add them and get the total amount
// -> & if amount was received, was it complete? or less than the cost
/* Steps */
//create new order address if order was New*
//get Order transaction address first, using demo address here
//$bitcoind->wallet()->getaccountaddress();
$order_address = '2N8JA1sRedzLRDYzJN5r13oztyiGADnDv5s';

//flag
$found_order_transactions = false;

//required order cost
$order_cost = 0.02;

// if multiple payments received on same order
$transaction_paid_total = 0;

if(!empty($bitcoind->wallet()->listtransactions())){
    $transactions = $bitcoind->wallet()->listtransactions();
    foreach($transactions as $transaction){
        //get order transactions if available
        if($order_address === $transaction['address']){
            $found_order_transactions = true;
            //get minimum confirmations
            if($transaction['confirmations'] > 3){
                //get payment type => "receive , send"
                if($transaction['category'] == 'receive'){

                    //add paid amount to $transaction_paid_total
                    $transaction_paid_total += $transaction['amount'];
                    echo '<br><br>TR <b>Success</b>:<br>Account:'.$transaction['account'];
                    echo '<br>Address:'.$transaction['address'];
                    echo '<br>Category:<b>'.$transaction['category'].'</b>';
                    echo '<br>Amount:'.$transaction['amount'];
                    echo '<br>Label:'.$transaction['label'];
                    echo '<br>Confirmations:'.$transaction['confirmations'];
                    echo '<br>Blockhash:'.$transaction['blockhash'];
                    echo '<br>txid:'.$transaction['txid'];
                }

            }else{
                echo '<br><br>TR <b>Success</b>( waiting for minimmum 3 Blockchain confirmations ):<br>Account:'.$transaction['account'];
            }
        }
    }


    //show paid amount
    if($transaction_paid_total !== 0){
        echo '<br>Total paid:'.$transaction_paid_total;
    }

    // if no trans at all
    if(!$found_order_transactions){
        echo '<br>No Payment has been made yet to this address:'.$order_address.'. Please make payment and try again.';
    }


}




//echo 'sending coins:'.$bitcoind->wallet()->sendtoaddress('2NDpNkbjumiGAZVXBgxmp36J4WXq32hJsUf',0.01,'Sending from romeo account');
// The full response (not usually needed) is stored in $this->response
// while the raw JSON is stored in $this->raw_response

// When a call fails for any reason, it will return FALSE and put the error message in $this->error
// Example:
    if(!empty($bitcoind->wallet()->error)){
        echo 'Error:'.$bitcoind->wallet()->error;
    }



?>
