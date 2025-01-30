<?php

/**
 * Helper functions for Tor integration
 */

/**
 * Check if the current request is coming through Tor
 */
function is_tor_request() {
    return isset($_SERVER['HTTP_X_TOR']) || 
           (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1');
}

/**
 * Get client's Tor exit node IP (if available)
 */
function get_tor_exit_node() {
    return $_SERVER['HTTP_X_TOR'] ?? null;
}

/**
 * Connect to Tor control port
 */
function connect_tor_control() {
    $fp = fsockopen(
        "127.0.0.1", 
        getenv('TOR_CONTROL_PORT') ?: 9051, 
        $errno, 
        $errstr, 
        30
    );
    
    if (!$fp) {
        error_log("Could not connect to Tor control port: $errstr ($errno)");
        return false;
    }
    
    // Authenticate with the control port
    $auth = sprintf('AUTHENTICATE "%s"\r\n', getenv('TOR_CONTROL_PASSWORD'));
    fwrite($fp, $auth);
    $response = fgets($fp);
    
    if (strpos($response, '250') !== 0) {
        error_log("Tor control authentication failed: $response");
        fclose($fp);
        return false;
    }
    
    return $fp;
}

/**
 * Create a new Tor circuit
 */
function new_tor_circuit() {
    $fp = connect_tor_control();
    if (!$fp) return false;
    
    fwrite($fp, "SIGNAL NEWNYM\r\n");
    $response = fgets($fp);
    fclose($fp);
    
    return strpos($response, '250') === 0;
}

/**
 * Get current Tor circuit information
 */
function get_tor_circuit_info() {
    $fp = connect_tor_control();
    if (!$fp) return false;
    
    fwrite($fp, "GETINFO circuit-status\r\n");
    $response = '';
    while ($line = fgets($fp)) {
        if ($line == ".\r\n") break;
        $response .= $line;
    }
    fclose($fp);
    
    return $response;
}

/**
 * Make HTTP request through Tor
 */
function tor_request($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_PROXY => getenv('TOR_PROXY') ?: '127.0.0.1:9050',
        CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Tor request error: $error");
        return false;
    }
    
    return [
        'body' => $response,
        'info' => $info
    ];
}

/**
 * Validate .onion address
 */
function validate_onion_address($address) {
    // V3 .onion addresses are 56 characters long (including .onion)
    return (bool) preg_match('/^[a-z2-7]{56}\.onion$/', $address);
}

/**
 * Get server's .onion address
 */
function get_onion_address() {
    $onion = getenv('ONION_LOCATION');
    if (!$onion) {
        error_log('ONION_LOCATION environment variable not set');
        return false;
    }
    
    $host = parse_url($onion, PHP_URL_HOST);
    if (!validate_onion_address($host)) {
        error_log('Invalid .onion address in ONION_LOCATION');
        return false;
    }
    
    return $onion;
}

/**
 * Check if Tor is properly configured
 */
function check_tor_config() {
    $issues = [];
    
    // Check Tor SOCKS proxy
    $fp = @fsockopen(
        "127.0.0.1", 
        getenv('TOR_PROXY') ? explode(':', getenv('TOR_PROXY'))[1] : 9050, 
        $errno, 
        $errstr, 
        1
    );
    
    if (!$fp) {
        $issues[] = "Tor SOCKS proxy not accessible: $errstr ($errno)";
    } else {
        fclose($fp);
    }
    
    // Check Tor control port
    if (!connect_tor_control()) {
        $issues[] = "Could not connect to Tor control port";
    }
    
    // Check .onion address
    if (!get_onion_address()) {
        $issues[] = "Invalid or missing .onion address";
    }
    
    // Test Tor connection
    $test = tor_request('https://check.torproject.org/api/ip');
    if (!$test || !$test['body']) {
        $issues[] = "Could not connect through Tor";
    }
    
    return $issues;
}

/**
 * Get client's guard relays
 */
function get_guard_relays() {
    $fp = connect_tor_control();
    if (!$fp) return false;
    
    fwrite($fp, "GETINFO entry-guards\r\n");
    $response = '';
    while ($line = fgets($fp)) {
        if ($line == ".\r\n") break;
        $response .= $line;
    }
    fclose($fp);
    
    return $response;
}

/**
 * Rate limiting specifically for Tor
 */
function tor_rate_limit($key, $max = 5, $window = 300) {
    $cache_key = 'tor_rate_' . $key . '_' . get_tor_exit_node();
    $attempts = cache_get($cache_key, 0);
    
    if ($attempts >= $max) {
        return false;
    }
    
    cache_set($cache_key, $attempts + 1, $window);
    return true;
}

/**
 * Clear rate limiting for a specific key
 */
function clear_tor_rate_limit($key) {
    $cache_key = 'tor_rate_' . $key . '_' . get_tor_exit_node();
    cache_set($cache_key, 0, 1);
}

/**
 * Log Tor-specific events
 */
function log_tor_event($type, $message, $data = []) {
    $data['tor_exit'] = get_tor_exit_node();
    log_activity('tor_' . $type, $message, $data);
}