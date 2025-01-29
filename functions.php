<?php
require_once 'config.php';

/**
 * Database Functions
 */
function get_db_connection() {
    static $db = null;
    if ($db === null) {
        global $config;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $db = new PDO(
                "mysql:host=localhost;dbname=" . $config['db_name'] . ";charset=utf8mb4",
                $config['db_user'],
                $config['db_password'],
                $options
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die("A system error has occurred. Please try again later.");
        }
    }
    return $db;
}

function execute_query($sql, $params = [], $fetch_mode = PDO::FETCH_ASSOC) {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($fetch_mode);
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        throw new Exception("Database error occurred");
    }
}

/**
 * Security Functions
 */
function secure_password($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

function verify_password($password, $hash) {
    if (password_needs_rehash($hash, PASSWORD_ARGON2ID)) {
        // Log that password needs rehashing - implement during user's next login
        error_log("Password hash needs rehashing for user");
    }
    return password_verify($password, $hash);
}

function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        time() - $_SESSION['csrf_token_time'] > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token) && 
           isset($_SESSION['csrf_token_time']) && 
           time() - $_SESSION['csrf_token_time'] <= 3600;
}

/**
 * Session and Authentication Functions
 */
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 3600) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

function check_auth() {
    start_secure_session();
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
    // Check if IP changed during session
    if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        logout_user();
        redirect('login.php?error=security_check_failed');
    }
}

function login_user($user) {
    start_secure_session();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = $user['username'];
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['last_login'] = time();
    session_regenerate_id(true);
}

function logout_user() {
    start_secure_session();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
}

/**
 * User Management Functions
 */
function get_user($user_id) {
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT id, username, email, created_at, last_login, 2fa_enabled FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return false;
    }
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           strlen($email) <= 254 && 
           preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
}

/**
 * Response Functions
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response($data, $status = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect($url, $with_message = null) {
    if ($with_message) {
        $_SESSION['flash_message'] = $with_message;
    }
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = APP_URL . '/' . ltrim($url, '/');
    }
    header("Location: $url");
    exit;
}

/**
 * Utility Functions
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function validate_input($data, $rules) {
    $errors = [];
    foreach ($rules as $field => $rule) {
        if (!isset($data[$field])) {
            $errors[$field] = "Field is required";
            continue;
        }
        
        $value = $data[$field];
        
        if (strpos($rule, 'required') !== false && empty($value)) {
            $errors[$field] = "Field is required";
        }
        
        if (strpos($rule, 'email') !== false && !validate_email($value)) {
            $errors[$field] = "Invalid email format";
        }
        
        if (preg_match('/min:(\d+)/', $rule, $matches)) {
            $min = $matches[1];
            if (strlen($value) < $min) {
                $errors[$field] = "Minimum length is $min characters";
            }
        }
        
        if (preg_match('/max:(\d+)/', $rule, $matches)) {
            $max = $matches[1];
            if (strlen($value) > $max) {
                $errors[$field] = "Maximum length is $max characters";
            }
        }
    }
    return $errors;
}