<?php
/**
 * Sam's Fruit Wines - Central Database Connection
 * Uses PDO with prepared statements for security against SQL injection.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'sams_winery_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDbConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error internally and show generic error in production
            error_log("Database connection failed: " . $e->getMessage());
            die(json_encode([
                'status'  => 'error',
                'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.'
            ]));
        }
    }

    return $pdo;
}
