<?php

/**
 * Bitcoin Core helper functions for mainnet
 */

use Denpa\Bitcoin\Client as BitcoinClient;

/**
 * Get Bitcoin RPC client instance
 */
function get_bitcoin_client() {
    static $client = null;
    
    if ($client === null) {
        try {
            $client = new BitcoinClient([
                'scheme' => 'http',
                'host' => BTC_RPC_HOST,
                'port' => BTC_RPC_PORT,
                'user' => BTC_RPC_USER,
                'password' => BTC_RPC_PASS,
                'timeout' => 30,
                'verify' => false // Since we're using localhost
            ]);
            
            // Test connection
            $client->getblockchaininfo();
        } catch (Exception $e) {
            error_log("Bitcoin RPC connection error: " . $e->getMessage());
            return false;
        }
    }
    
    return $client;
}

/**
 * Create new Bitcoin address for user
 */
function create_user_bitcoin_address($username) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        // Create wallet if it doesn't exist
        try {
            $client->createwallet($username);
        } catch (Exception $e) {
            // Wallet might already exist
            if (strpos($e->getMessage(), 'Database already exists') === false) {
                throw $e;
            }
        }

        // Load wallet and generate address
        $wallet = $client->wallet($username);
        $address = $wallet->getnewaddress("", "bech32");
        
        return $address;
    } catch (Exception $e) {
        error_log("Error creating Bitcoin address: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's Bitcoin balance
 */
function get_user_balance($username) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        $wallet = $client->wallet($username);
        $balance = $wallet->getbalance();
        
        return $balance;
    } catch (Exception $e) {
        error_log("Error getting balance: " . $e->getMessage());
        return false;
    }
}

/**
 * Send Bitcoin transaction
 */
function send_bitcoin($username, $address, $amount, $fee_rate = null) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        $wallet = $client->wallet($username);
        
        // Validate address
        if (!$wallet->validateaddress($address)['isvalid']) {
            throw new Exception("Invalid Bitcoin address");
        }

        $options = [];
        if ($fee_rate !== null) {
            $options['fee_rate'] = $fee_rate;
        }

        // Create and send transaction
        $txid = $wallet->sendtoaddress($address, $amount, "", "", false, true, null, "unset", false, $fee_rate);
        
        return $txid;
    } catch (Exception $e) {
        error_log("Error sending Bitcoin: " . $e->getMessage());
        return false;
    }
}

/**
 * Get transaction details
 */
function get_transaction_details($txid) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        $tx = $client->gettransaction($txid);
        return $tx;
    } catch (Exception $e) {
        error_log("Error getting transaction: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current network fee estimate
 */
function get_fee_estimate($blocks = 6) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        $fee = $client->estimatesmartfee($blocks);
        return $fee['feerate'] ?? false;
    } catch (Exception $e) {
        error_log("Error getting fee estimate: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if address has received payment
 */
function check_address_payment($address, $expected_amount) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        // Get transactions receiving to this address
        $received = $client->listreceivedbyaddress(0, true, true, $address);
        
        foreach ($received as $recv) {
            if ($recv['address'] === $address && $recv['amount'] >= $expected_amount) {
                return [
                    'confirmed' => $recv['confirmations'] >= 1,
                    'amount' => $recv['amount'],
                    'txids' => $recv['txids']
                ];
            }
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error checking payment: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current Bitcoin price
 */
function get_bitcoin_price($currency = 'USD') {
    try {
        // Use memcached/redis if available for caching
        $cache_key = "btc_price_$currency";
        $cached = cache_get($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Make request through Tor
        $response = tor_request("https://api.kraken.com/0/public/Ticker?pair=XBT$currency");
        if (!$response) return false;

        $data = json_decode($response['body'], true);
        if (!$data || !isset($data['result'])) return false;

        $pair = "XBT$currency";
        $price = $data['result']["X$pair"]['c'][0] ?? false;
        
        if ($price) {
            cache_set($cache_key, $price, 300); // Cache for 5 minutes
        }
        
        return $price;
    } catch (Exception $e) {
        error_log("Error getting Bitcoin price: " . $e->getMessage());
        return false;
    }
}

/**
 * Convert BTC to fiat
 */
function btc_to_fiat($btc_amount, $currency = 'USD') {
    $price = get_bitcoin_price($currency);
    if (!$price) return false;
    
    return $btc_amount * $price;
}

/**
 * Convert fiat to BTC
 */
function fiat_to_btc($fiat_amount, $currency = 'USD') {
    $price = get_bitcoin_price($currency);
    if (!$price) return false;
    
    return $fiat_amount / $price;
}

/**
 * Format Bitcoin amount
 */
function format_btc($amount) {
    return sprintf('%.8f', $amount);
}

/**
 * Validate Bitcoin address
 */
function validate_bitcoin_address($address) {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        $result = $client->validateaddress($address);
        return $result['isvalid'] ?? false;
    } catch (Exception $e) {
        error_log("Error validating address: " . $e->getMessage());
        return false;
    }
}

/**
 * Get network status
 */
function get_network_status() {
    try {
        $client = get_bitcoin_client();
        if (!$client) return false;

        $info = $client->getnetworkinfo();
        $blockchain = $client->getblockchaininfo();
        
        return [
            'version' => $info['version'],
            'connections' => $info['connections'],
            'blocks' => $blockchain['blocks'],
            'headers' => $blockchain['headers'],
            'verification_progress' => $blockchain['verificationprogress'],
            'difficulty' => $blockchain['difficulty'],
            'chain' => $blockchain['chain'],
            'warnings' => $blockchain['warnings'] ?? null
        ];
    } catch (Exception $e) {
        error_log("Error getting network status: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if node is synced
 */
function is_node_synced() {
    $status = get_network_status();
    if (!$status) return false;
    
    return $status['blocks'] == $status['headers'] && 
           $status['verification_progress'] > 0.9999;
}