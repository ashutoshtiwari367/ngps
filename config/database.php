<?php
/**
 * Database Configuration File
 * School Management System - XAMPP Localhost
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_management');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        // Ports to try (3307 for custom XAMPP ports, 3306 for standard)
        $ports_to_try = [3307, 3306, 3308];
        $last_exception = null;

        foreach ($ports_to_try as $port) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                define('DB_PORT', $port);
                return $pdo;
            } catch (PDOException $e) {
                // If database missing, attempt creation on this active host/port
                try {
                    $dsn_no_db = "mysql:host=" . DB_HOST . ";port=" . $port . ";charset=utf8mb4";
                    $tmp_pdo = new PDO($dsn_no_db, DB_USER, DB_PASS);
                    $tmp_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                    define('DB_PORT', $port);
                    return $pdo;
                } catch (PDOException $ex) {
                    $last_exception = $e;
                }
            }
        }

        if ($pdo === null) {
            die("<div style='font-family: sans-serif; padding: 2rem; background: #fee2e2; color: #991b1b; border-radius: 0.5rem; margin: 2rem;'>
                <h2>Database Connection Error</h2>
                <p>Unable to connect to MySQL database <strong>" . DB_NAME . "</strong> on ports (3307, 3306, 3308).</p>
                <p>Error details: " . htmlspecialchars($last_exception ? $last_exception->getMessage() : 'MySQL server offline') . "</p>
                <hr style='border-color: #fca5a5;'>
                <p><strong>Setup Tip:</strong> Please ensure MySQL server is running in XAMPP Control Panel.</p>
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

