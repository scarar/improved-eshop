<?php

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') === 'true' ? '1' : '0');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Application settings
define('APP_NAME', getenv('APP_NAME') ?: 'Improved E-Shop');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://' . $_SERVER['HTTP_HOST']);
define('APP_ROOT', __DIR__);
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'UTC');
define('APP_CHARSET', 'UTF-8');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Database settings
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'eshop');
define('DB_USER', getenv('DB_USER') ?: 'eshopuser');
define('DB_PASS', getenv('DB_PASS') ?: 'r0m30@)!&0n10n');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Security settings
define('CSRF_EXPIRY', 3600); // 1 hour
define('SESSION_EXPIRY', getenv('SESSION_LIFETIME') ?: 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', getenv('MAX_LOGIN_ATTEMPTS') ?: 5);
define('LOGIN_LOCKOUT_TIME', getenv('LOGIN_LOCKOUT_TIME') ?: 900); // 15 minutes
define('PASSWORD_MIN_LENGTH', getenv('PASSWORD_MIN_LENGTH') ?: 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_MIXED_CASE', true);

// File upload settings
define('UPLOAD_MAX_SIZE', getenv('UPLOAD_MAX_SIZE') ?: 5242880); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', __DIR__ . '/uploads');

// Cache settings
define('CACHE_ENABLED', getenv('CACHE_ENABLED') !== 'false');
define('CACHE_PATH', __DIR__ . '/cache');
define('CACHE_DEFAULT_TTL', getenv('CACHE_TTL') ?: 3600);
define('CACHE_DRIVER', getenv('CACHE_DRIVER') ?: 'file'); // file, redis, memcached

// Redis settings (if used)
define('REDIS_HOST', getenv('REDIS_HOST') ?: '127.0.0.1');
define('REDIS_PORT', getenv('REDIS_PORT') ?: 6379);
define('REDIS_PASSWORD', getenv('REDIS_PASSWORD') ?: null);

// Logging settings
define('LOG_PATH', __DIR__ . '/logs');
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'debug');
define('LOG_DAYS', getenv('LOG_DAYS') ?: 30);

// Bitcoin settings
define('BTC_RPC_HOST', getenv('BTC_RPC_HOST') ?: 'localhost');
define('BTC_RPC_PORT', getenv('BTC_RPC_PORT') ?: 18332);
define('BTC_RPC_USER', getenv('BTC_RPC_USER') ?: 'root');
define('BTC_RPC_PASS', getenv('BTC_RPC_PASS') ?: '1mN%AWP46J?W$CdW');
define('BTC_NETWORK', getenv('BTC_NETWORK') ?: 'testnet');

// Email settings
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.mailtrap.io');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 2525);
define('MAIL_USERNAME', getenv('MAIL_USERNAME'));
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD'));
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: APP_NAME);

// Initialize required directories
$directories = [
    LOG_PATH,
    CACHE_PATH,
    UPLOAD_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Security headers for Tor - no external resources
$csp = [
    "default-src 'none'",
    "script-src 'self'",
    "style-src 'self'",
    "img-src 'self' data:",
    "font-src 'self'",
    "connect-src 'self'",
    "media-src 'none'",
    "object-src 'none'",
    "frame-src 'none'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    "upgrade-insecure-requests"
];

// Headers optimized for Tor hidden service
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: ' . implode('; ', $csp));
header('Onion-Location: ' . getenv('ONION_LOCATION')); // Add your .onion address
header('Alt-Svc: h2="' . getenv('ONION_LOCATION') . ':443"');

// Session configuration optimized for Tor
$session_options = [
    'cookie_httponly' => 1,
    'cookie_secure' => 1,
    'cookie_samesite' => 'Lax', // Less strict for Tor browser compatibility
    'use_strict_mode' => 1,
    'use_only_cookies' => 1,
    'gc_maxlifetime' => SESSION_EXPIRY,
    'sid_length' => 48,
    'sid_bits_per_character' => 6,
    'cache_limiter' => 'nocache',
    'cookie_lifetime' => SESSION_EXPIRY,
    'cookie_domain' => parse_url(getenv('ONION_LOCATION'), PHP_URL_HOST) ?: '',
    'cookie_path' => '/'
];

foreach ($session_options as $key => $value) {
    ini_set("session.$key", $value);
}

session_set_cookie_params([
    'lifetime' => SESSION_EXPIRY,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Clean old files
function clean_old_files($directory, $days) {
    if (!is_dir($directory)) return;
    
    $now = time();
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $now - $file->getMTime() >= $days * 86400) {
            @unlink($file->getRealPath());
        }
    }
}

// Clean old logs and cache files
clean_old_files(LOG_PATH, LOG_DAYS);
clean_old_files(CACHE_PATH, 1); // Clean cache files older than 1 day
