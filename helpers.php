<?php

/**
 * Helper functions for common tasks
 */

/**
 * Format currency values
 */
function format_currency($amount, $currency = 'USD') {
    $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, $currency);
}

/**
 * Format dates in the user's timezone
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Generate a random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Clean file paths to prevent directory traversal
 */
function clean_path($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('/\/+/', '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');
    $absolutes = [];
    
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    
    return implode('/', $absolutes);
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types = ['jpg', 'jpeg', 'png'], $max_size = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => false, 'error' => 'No file uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'File size exceeds limit'];
        default:
            return ['valid' => false, 'error' => 'Unknown upload error'];
    }

    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File size exceeds limit'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_types)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }

    // Additional MIME type check
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'
    ];

    if (!isset($allowed_mimes[$ext]) || $allowed_mimes[$ext] !== $mime_type) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }

    return ['valid' => true];
}

/**
 * Secure file upload
 */
function secure_file_upload($file, $destination, $allowed_types = ['jpg', 'jpeg', 'png'], $max_size = 5242880) {
    $validation = validate_file_upload($file, $allowed_types, $max_size);
    if (!$validation['valid']) {
        return $validation;
    }

    $destination = clean_path($destination);
    $filename = generate_random_string(16) . '.' . 
                strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filepath = $destination . '/' . $filename;

    if (!is_dir($destination)) {
        if (!mkdir($destination, 0755, true)) {
            return ['valid' => false, 'error' => 'Failed to create upload directory'];
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['valid' => false, 'error' => 'Failed to save uploaded file'];
    }

    return [
        'valid' => true,
        'filename' => $filename,
        'filepath' => $filepath
    ];
}

/**
 * Pagination helper
 */
function paginate($total_items, $items_per_page = 10, $current_page = 1) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page - 1,
        'next_page' => $current_page + 1
    ];
}

/**
 * Generate pagination HTML
 */
function generate_pagination_html($pagination, $base_url) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    $html .= '<li class="page-item ' . (!$pagination['has_previous'] ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $base_url . '?page=' . $pagination['previous_page'] . 
            '" ' . (!$pagination['has_previous'] ? 'tabindex="-1" aria-disabled="true"' : '') . '>';
    $html .= '<span aria-hidden="true">&laquo;</span></a></li>';

    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $html .= '<li class="page-item ' . ($i == $pagination['current_page'] ? 'active' : '') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
    }

    // Next button
    $html .= '<li class="page-item ' . (!$pagination['has_next'] ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $base_url . '?page=' . $pagination['next_page'] . 
            '" ' . (!$pagination['has_next'] ? 'tabindex="-1" aria-disabled="true"' : '') . '>';
    $html .= '<span aria-hidden="true">&raquo;</span></a></li>';

    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Flash messages
 */
function set_flash_message($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

function get_flash_messages() {
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }

    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    
    return $messages;
}

/**
 * Display flash messages HTML
 */
function display_flash_messages() {
    $messages = get_flash_messages();
    if (empty($messages)) {
        return '';
    }

    $html = '';
    foreach ($messages as $msg) {
        $class = match($msg['type']) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-info'
        };
        
        $html .= sprintf(
            '<div class="alert %s alert-dismissible fade show" role="alert">%s
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>',
            $class,
            htmlspecialchars($msg['message'])
        );
    }
    
    return $html;
}

/**
 * Logging helper
 */
function log_activity($type, $message, $data = []) {
    $log_file = __DIR__ . '/logs/activity.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? 'guest',
        'data' => $data
    ];

    $log_line = json_encode($log_entry) . PHP_EOL;
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

/**
 * Cache helper
 */
function cache_get($key, $default = null) {
    $cache_file = __DIR__ . '/cache/' . md5($key);
    
    if (!file_exists($cache_file)) {
        return $default;
    }

    $data = file_get_contents($cache_file);
    $cached = json_decode($data, true);
    
    if (!$cached || !isset($cached['expiry']) || !isset($cached['data'])) {
        return $default;
    }

    if (time() > $cached['expiry']) {
        unlink($cache_file);
        return $default;
    }

    return $cached['data'];
}

function cache_set($key, $value, $ttl = 3600) {
    $cache_dir = __DIR__ . '/cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $cache_file = $cache_dir . '/' . md5($key);
    $data = [
        'expiry' => time() + $ttl,
        'data' => $value
    ];

    file_put_contents($cache_file, json_encode($data), LOCK_EX);
}

/**
 * Array helpers
 */
function array_get($array, $key, $default = null) {
    if (!is_array($array)) {
        return $default;
    }

    if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }

    return $array;
}

function array_set(&$array, $key, $value) {
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);
    while (count($keys) > 1) {
        $key = array_shift($keys);
        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }
        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;
    return $array;
}

/**
 * String helpers
 */
function str_limit($value, $limit = 100, $end = '...') {
    if (mb_strlen($value) <= $limit) {
        return $value;
    }
    return rtrim(mb_substr($value, 0, $limit)) . $end;
}

function str_slug($title, $separator = '-') {
    $title = preg_replace('!['.preg_quote($separator).']+!u', $separator, $title);
    $title = mb_strtolower(trim(strip_tags($title)));
    $title = preg_replace('/[^a-z0-9]/', $separator, $title);
    return trim($title, $separator);
}