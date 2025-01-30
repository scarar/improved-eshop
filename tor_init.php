<?php
// Force traffic through Tor hidden service in production
if (APP_ENV === 'production' && !isset($_SERVER['HTTP_X_TOR']) && 
    $_SERVER['SERVER_PORT'] != TOR_HIDDEN_SERVICE_PORT) {
    header('Location: ' . ONION_URL);
    exit;
}

// Set security headers for Tor
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Onion-Location: ' . ONION_URL);

// Strict CSP for Tor
$csp = "default-src 'none'; " .
       "script-src 'self'; " .
       "style-src 'self'; " .
       "img-src 'self' data:; " .
       "font-src 'self'; " .
       "form-action 'self'; " .
       "frame-ancestors 'none'; " .
       "base-uri 'self'";
header("Content-Security-Policy: $csp");

// Start secure session
session_set_cookie_params([
    'lifetime' => SESSION_EXPIRY,
    'path' => '/',
    'domain' => parse_url(ONION_URL, PHP_URL_HOST),
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'  // Less strict for Tor Browser compatibility
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
