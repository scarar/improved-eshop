<?php
require_once 'config.php';

// Check if the script is running on the correct port
$server_port = $_SERVER['SERVER_PORT'] ?? null;
if ($server_port != TOR_HIDDEN_SERVICE_PORT) {
    die("Warning: Server is running on port $server_port but should be on port " . TOR_HIDDEN_SERVICE_PORT);
}

// Check if the script is accessible via localhost
$server_addr = $_SERVER['SERVER_ADDR'] ?? null;
if ($server_addr !== '127.0.0.1' && $server_addr !== TOR_HIDDEN_SERVICE_HOST) {
    die("Warning: Server address $server_addr should be " . TOR_HIDDEN_SERVICE_HOST);
}

// Check Tor SOCKS proxy
$fp = @fsockopen('127.0.0.1', 9050, $errno, $errstr, 1);
if (!$fp) {
    die("Warning: Tor SOCKS proxy not accessible: $errstr ($errno)");
} else {
    fclose($fp);
}

// Check if we can connect to our own hidden service
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => ONION_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_PROXY => "socks5h://127.0.0.1:9050",
    CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_HEADER => true
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($error) {
    die("Warning: Could not connect to hidden service: $error");
}

// Check if the hidden service directory exists and has the right permissions
$hidden_service_dir = '/var/lib/tor/ai-test';
if (!file_exists($hidden_service_dir)) {
    die("Warning: Hidden service directory does not exist: $hidden_service_dir");
}

// All checks passed
echo "Success! Tor hidden service is properly configured:\n";
echo "- Server running on port " . TOR_HIDDEN_SERVICE_PORT . "\n";
echo "- Tor SOCKS proxy accessible\n";
echo "- Hidden service responding\n";
echo "- Hidden service directory exists\n";

// Additional information
echo "\nServer Information:\n";
echo "- Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "- Server Protocol: " . ($_SERVER['SERVER_PROTOCOL'] ?? 'Unknown') . "\n";
echo "- Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "- Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "\n";
echo "- Server Address: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n";
echo "- Remote Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

// Check if we're being accessed through Tor
$is_tor = isset($_SERVER['HTTP_X_TOR']) || 
          (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1');
echo "- Accessed via Tor: " . ($is_tor ? 'Yes' : 'No') . "\n";

// Hidden service information
if (file_exists("$hidden_service_dir/hostname")) {
    echo "- Onion Address: " . file_get_contents("$hidden_service_dir/hostname") . "\n";
}