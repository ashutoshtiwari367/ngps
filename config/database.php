<?php
/**
 * Database Configuration File
 * School Management System - XAMPP Localhost
 */

// Determine Environment (Localhost vs Live Server Setup)
$http_host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
$is_local = false;

if (
    in_array($http_host, ['localhost', '127.0.0.1', '::1']) ||
    strpos($http_host, 'localhost:') === 0 ||
    strpos($http_host, '127.0.0.1:') === 0 ||
    (empty($http_host) && php_sapi_name() === 'cli' && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
) {
    $is_local = true;
}

if ($is_local) {
    // ----------------------------------------------------
    // Localhost Environment (XAMPP)
    // ----------------------------------------------------
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'school_management');
} else {
    // ----------------------------------------------------
    // Live Server / Production Setup
    // ----------------------------------------------------
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u447123054_npgs');
    define('DB_PASS', 'u447123054_Npgs');
    define('DB_NAME', 'u447123054_npgs');
}

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Ports to try (3307 for custom XAMPP ports, 3306 for standard)
        $ports_to_try = [3307, 3306, 3308];
        $last_exception = null;

        // 1. Try connecting with specific ports
        foreach ($ports_to_try as $port) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                if (!defined('DB_PORT')) {
                    define('DB_PORT', $port);
                }
                return $pdo;
            } catch (PDOException $e) {
                // If local root and database missing, attempt creation
                if (DB_USER === 'root') {
                    try {
                        $dsn_no_db = "mysql:host=" . DB_HOST . ";port=" . $port . ";charset=utf8mb4";
                        $tmp_pdo = new PDO($dsn_no_db, DB_USER, DB_PASS);
                        $tmp_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
                        if (!defined('DB_PORT')) {
                            define('DB_PORT', $port);
                        }
                        return $pdo;
                    } catch (PDOException $ex) {
                        $last_exception = $e;
                    }
                } else {
                    $last_exception = $e;
                }
            }
        }

        // 2. Try default connection without explicit port (standard for live Linux/Hostinger unix socket)
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            if (!defined('DB_PORT')) {
                define('DB_PORT', 3306);
            }
            return $pdo;
        } catch (PDOException $e) {
            $last_exception = $e;
        }

        if ($pdo === null) {
            die("<div style='font-family: sans-serif; padding: 2rem; background: #fee2e2; color: #991b1b; border-radius: 0.5rem; margin: 2rem;'>
                <h2>Database Connection Error</h2>
                <p>Unable to connect to MySQL database <strong>" . DB_NAME . "</strong> on host <strong>" . DB_HOST . "</strong>.</p>
                <p>Error details: " . htmlspecialchars($last_exception ? $last_exception->getMessage() : 'MySQL server offline') . "</p>
                <hr style='border-color: #fca5a5;'>
                <p><strong>Setup Tip:</strong> Please verify your database credentials and ensure the MySQL server is running.</p>
            </div>");
        }
    }
    return $pdo;
}

// Global PDO instance
$pdo = getDBConnection();

// Run migrations and table checks dynamically
require_once __DIR__ . '/migrate.php';
run_migrations($pdo);

