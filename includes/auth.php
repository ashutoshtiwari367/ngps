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
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
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
 * Helper to fetch school logo URL
 */
function get_school_logo() {
    $logo = get_school_setting('school_logo', 'assets/images/logo_circle.png');
    return base_url($logo);
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

/**
 * Educational natural sorting SQL for classes (PG -> Nursery -> LKG -> UKG -> 1st to 12th)
 */
function get_class_order_sql($prefix = '') {
    $col = $prefix ? "{$prefix}.class_name" : "class_name";
    $sec = $prefix ? "{$prefix}.section" : "section";
    return "CASE 
        WHEN {$col} IN ('PG', 'Play Group', 'Playgroup') THEN 1
        WHEN {$col} = 'Nursery' THEN 2
        WHEN {$col} = 'LKG' THEN 3
        WHEN {$col} = 'UKG' THEN 4
        WHEN {$col} = 'Class 1' OR {$col} = '1st' THEN 5
        WHEN {$col} = 'Class 2' OR {$col} = '2nd' THEN 6
        WHEN {$col} = 'Class 3' OR {$col} = '3rd' THEN 7
        WHEN {$col} = 'Class 4' OR {$col} = '4th' THEN 8
        WHEN {$col} = 'Class 5' OR {$col} = '5th' THEN 9
        WHEN {$col} = 'Class 6' OR {$col} = '6th' THEN 10
        WHEN {$col} = 'Class 7' OR {$col} = '7th' THEN 11
        WHEN {$col} = 'Class 8' OR {$col} = '8th' THEN 12
        WHEN {$col} = 'Class 9' OR {$col} = '9th' THEN 13
        WHEN {$col} = 'Class 10' OR {$col} = '10th' THEN 14
        WHEN {$col} = 'Class 11' OR {$col} = '11th' THEN 15
        WHEN {$col} = 'Class 12' OR {$col} = '12th' THEN 16
        ELSE 99
    END ASC, {$sec} ASC";
}

/**
 * Fetch all classes in standard school sequence
 */
function get_all_classes($pdo_instance = null) {
    global $pdo;
    $db = $pdo_instance ?: $pdo;
    if (!$db) return [];
    $order = get_class_order_sql();
    return $db->query("SELECT * FROM classes ORDER BY {$order}")->fetchAll();
}

/**
 * Fetch unique/distinct class names in standard school sequence (PG to 12th)
 * No duplicate classes (e.g. returns "Class 10" once, not "10 A", "10 B")
 */
function get_distinct_class_names($pdo_instance = null) {
    global $pdo;
    $db = $pdo_instance ?: $pdo;
    $order = get_class_order_sql();
    $list = [];
    if ($db) {
        $stmt = $db->query("SELECT DISTINCT class_name FROM classes ORDER BY {$order}");
        $list = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    $standard_classes = ['PG', 'Nursery', 'LKG', 'UKG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12'];
    $combined = array_values(array_unique(array_merge($list, $standard_classes)));

    $ranks = [
        'PG' => 1, 'Play Group' => 1, 'Playgroup' => 1,
        'Nursery' => 2,
        'LKG' => 3,
        'UKG' => 4,
        'Class 1' => 5, 'Class 2' => 6, 'Class 3' => 7,
        'Class 4' => 8, 'Class 5' => 9, 'Class 6' => 10,
        'Class 7' => 11, 'Class 8' => 12, 'Class 9' => 13,
        'Class 10' => 14, 'Class 11' => 15, 'Class 12' => 16
    ];

    usort($combined, function($a, $b) use ($ranks) {
        $rA = $ranks[$a] ?? 99;
        $rB = $ranks[$b] ?? 99;
        return $rA - $rB;
    });

    return $combined;
}

/**
 * Find or auto-create class ID by class name and section
 */
function find_or_create_class_id($class_name, $section = 'A', $pdo_instance = null) {
    global $pdo;
    $db = $pdo_instance ?: $pdo;
    $class_name = trim($class_name);
    $section = strtoupper(trim($section ?: 'A'));

    $stmt = $db->prepare("SELECT id FROM classes WHERE class_name = :c AND section = :s LIMIT 1");
    $stmt->execute(['c' => $class_name, 's' => $section]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int)$id;
    }

    // Try case-insensitive match
    $stmt = $db->prepare("SELECT id FROM classes WHERE LOWER(class_name) = LOWER(:c) AND LOWER(section) = LOWER(:s) LIMIT 1");
    $stmt->execute(['c' => $class_name, 's' => $section]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int)$id;
    }

    // Auto-create missing section class record
    $ins = $db->prepare("INSERT INTO classes (class_name, section, room_number, academic_session) VALUES (:c, :s, 'Room 101', '2026-2027')");
    $ins->execute(['c' => $class_name, 's' => $section]);
    return (int)$db->lastInsertId();
}

/**
 * Convert numerical currency amount to words (Indian numbering system)
 */
function number_to_words_indian($number) {
    $number = floatval($number);
    if ($number <= 0) return 'Zero Rupees Only';

    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $num = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($num) {
            $plural = (($counter = count($str)) && $num > 9) ? '' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($num < 21) ? $words[$num] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                : $words[floor($num / 10) * 10] . ' ' . $words[$num % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
        } else {
            $str[] = null;
        }
    }
    $rupees = implode('', array_reverse(array_filter($str)));
    $paise = ($decimal > 0 && isset($words[$decimal])) ? " and " . $words[$decimal] . " Paise" : '';
    $result = trim($rupees) ? trim($rupees) . ' Rupees' . $paise . ' Only' : 'Zero Rupees Only';
    return preg_replace('/\s+/', ' ', $result);
}

