<?php
/**
 * Authentication & Helper Utilities
 * School Management System
 */

// Enable output buffering to prevent "headers already sent" warnings
if (ob_get_level() == 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Determine dynamic base URL for links
 */
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Compute root folder relative to server script name
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script_name, '/admin/') !== false) {
        $root = substr($script_name, 0, strpos($script_name, '/admin/'));
    } elseif (strpos($script_name, '/teacher/') !== false) {
        $root = substr($script_name, 0, strpos($script_name, '/teacher/'));
    } elseif (strpos($script_name, '/student/') !== false) {
        $root = substr($script_name, 0, strpos($script_name, '/student/'));
    } else {
        $dir = dirname($script_name);
        $root = ($dir === '/' || $dir === '\\') ? '' : $dir;
    }
    
    $clean_path = ltrim($path, '/');
    return rtrim($root, '/') . ($clean_path ? '/' . $clean_path : '');
}

/**
 * Check if current user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Role Check Helpers
 */
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function is_teacher() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'teacher';
}

function is_student() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'student';
}

function is_accountant() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'accountant';
}

function get_logged_student_id() {
    return $_SESSION['student_id_pk'] ?? null;
}

function get_logged_teacher_id() {
    return $_SESSION['teacher_id_pk'] ?? null;
}

/**
 * Require login for protected routes
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please login to access the portal.');
        header('Location: ' . base_url('login.php'));
        exit();
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Access denied. Administrator privileges required.');
        if (is_accountant()) {
            header('Location: ' . base_url('admin/accounts/index.php'));
        } elseif (is_teacher()) {
            header('Location: ' . base_url('teacher/dashboard.php'));
        } else {
            header('Location: ' . base_url('student/dashboard.php'));
        }
        exit();
    }
}

function require_accountant_or_admin() {
    require_login();
    if (!is_accountant() && !is_admin()) {
        set_flash('error', 'Access denied. Accounts department privileges required.');
        if (is_teacher()) {
            header('Location: ' . base_url('teacher/dashboard.php'));
        } elseif (is_student()) {
            header('Location: ' . base_url('student/dashboard.php'));
        } else {
            header('Location: ' . base_url('login.php'));
        }
        exit();
    }
}

function require_teacher() {
    require_login();
    if (!is_teacher() && !is_admin()) {
        set_flash('error', 'Access denied. Teacher privileges required.');
        if (is_accountant()) {
            header('Location: ' . base_url('admin/accounts/index.php'));
        } elseif (is_student()) {
            header('Location: ' . base_url('student/dashboard.php'));
        } else {
            header('Location: ' . base_url('login.php'));
        }
        exit();
    }
}

function require_student() {
    require_login();
    if (!is_student() && !is_admin()) {
        set_flash('error', 'Access denied. Student privileges required.');
        if (is_accountant()) {
            header('Location: ' . base_url('admin/accounts/index.php'));
        } elseif (is_teacher()) {
            header('Location: ' . base_url('teacher/dashboard.php'));
        } else {
            header('Location: ' . base_url('login.php'));
        }
        exit();
    }
}

/**
 * Helper to fetch school setting dynamically
 */
function get_school_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM school_settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute(['k' => $key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}


/**
 * Sanitize HTML output to prevent XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash alert message
 */
function set_flash($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'text' => $message
    ];
}

/**
 * Retrieve & clear flash message
 */
function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * CSRF Protection
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Helper to render CSRF input field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
