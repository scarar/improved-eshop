<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use Denpa\Bitcoin\Client as BitcoinClient;

echo "Bitcoin Core Connection Check\n";
echo "===========================\n\n";

try {
    // Create Bitcoin client
    $client = new BitcoinClient([
        'scheme'   => 'http',
        'host'     => BTC_RPC_HOST,
        'port'     => BTC_RPC_PORT,
        'user'     => BTC_RPC_USER,
        'password' => BTC_RPC_PASS,
        'timeout'  => 30,
        'verify'   => false
    ]);

    // Test connection and get basic info
    $networkInfo = $client->getnetworkinfo();
    $blockchainInfo = $client->getblockchaininfo();
    $walletInfo = $client->getwalletinfo();
    $peerInfo = $client->getpeerinfo();
    $mempoolInfo = $client->getmempoolinfo();

    // Display Bitcoin Core information
    echo "Bitcoin Core Status:\n";
    echo "-------------------\n";
    echo "Version: {$networkInfo['version']}\n";
    echo "Protocol Version: {$networkInfo['protocolversion']}\n";
    echo "Chain: {$blockchainInfo['chain']}\n";
    echo "Blocks: {$blockchainInfo['blocks']}\n";
    echo "Headers: {$blockchainInfo['headers']}\n";
    echo "Verification Progress: " . round($blockchainInfo['verificationprogress'] * 100, 2) . "%\n";
    echo "Difficulty: {$blockchainInfo['difficulty']}\n";
    echo "Network: {$networkInfo['networkactive']? 'Active' : 'Inactive'}\n";
    echo "Connections: {$networkInfo['connections']}\n";
    echo "\n";

    // Check if node is synced
    $isSynced = $blockchainInfo['blocks'] == $blockchainInfo['headers'] && 
                $blockchainInfo['verificationprogress'] > 0.9999;
    echo "Sync Status: " . ($isSynced ? "Fully Synced" : "Syncing") . "\n";
    if (!$isSynced) {
        echo "Warning: Node is not fully synced. Some operations may not work correctly.\n";
    }
    echo "\n";

    // Check wallet status
    echo "Wallet Status:\n";
    echo "--------------\n";
    echo "Balance: {$walletInfo['balance']} BTC\n";
    echo "Unconfirmed Balance: {$walletInfo['unconfirmed_balance']} BTC\n";
    echo "Immature Balance: {$walletInfo['immature_balance']} BTC\n";
    echo "Transactions: {$walletInfo['txcount']}\n";
    echo "\n";

    // Check Tor connectivity
    echo "Tor Integration:\n";
    echo "---------------\n";
    $torEnabled = false;
    foreach ($networkInfo['networks'] as $network) {
        if ($network['name'] === 'onion') {
            $torEnabled = $network['reachable'];
            break;
        }
    }
    echo "Tor Enabled: " . ($torEnabled ? "Yes" : "No") . "\n";
    if ($torEnabled) {
        echo "Onion Peers: " . count(array_filter($peerInfo, function($peer) {
            return strpos($peer['addr'], '.onion') !== false;
        })) . "\n";
    }
    echo "\n";

    // Check mempool status
    echo "Mempool Status:\n";
    echo "---------------\n";
    echo "Size: {$mempoolInfo['size']} transactions\n";
    echo "Memory Usage: " . round($mempoolInfo['usage'] / 1024 / 1024, 2) . " MB\n";
    echo "Max Memory: " . round($mempoolInfo['maxmempool'] / 1024 / 1024, 2) . " MB\n";
    echo "\n";

    // Check fee estimates
    echo "Fee Estimates (sat/vB):\n";
    echo "----------------------\n";
    $targets = [1, 6, 144]; // Next block, ~1 hour, ~24 hours
    foreach ($targets as $target) {
        $estimate = $client->estimatesmartfee($target);
        if (isset($estimate['feerate'])) {
            echo "{$target} block" . ($target > 1 ? 's' : '') . ": " . 
                 round($estimate['feerate'] * 100000, 2) . "\n";
        }
    }
    echo "\n";

    // Perform a test wallet operation
    echo "Testing Wallet Operations:\n";
    echo "-----------------------\n";
    try {
        $newAddress = $client->getnewaddress("test", "bech32");
        echo "Generated Test Address: $newAddress\n";
        $addressInfo = $client->getaddressinfo($newAddress);
        echo "Address Type: {$addressInfo['type']}\n";
        echo "Script Type: {$addressInfo['script_type']}\n";
    } catch (Exception $e) {
        echo "Warning: Could not perform wallet operations: {$e->getMessage()}\n";
    }
    echo "\n";

    // Overall status
    echo "Overall Status:\n";
    echo "--------------\n";
    $issues = [];
    if (!$isSynced) $issues[] = "Node not fully synced";
    if (!$torEnabled) $issues[] = "Tor not enabled";
    if ($networkInfo['connections'] < 8) $issues[] = "Low peer count";
    if ($walletInfo['balance'] < 0) $issues[] = "Negative balance";

    if (empty($issues)) {
        echo "✓ All checks passed! Bitcoin Core is properly configured and running.\n";
    } else {
        echo "⚠ Issues found:\n";
        foreach ($issues as $issue) {
            echo "  - $issue\n";
        }
    }

} catch (Exception $e) {
    echo "Error: Could not connect to Bitcoin Core\n";
    echo "Details: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check if Bitcoin Core is running\n";
    echo "2. Verify RPC credentials in config.php\n";
    echo "3. Check if port " . BTC_RPC_PORT . " is accessible\n";
    echo "4. Check Bitcoin Core logs for errors\n";
    exit(1);
}