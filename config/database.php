<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

declare(strict_types=1);

require_once __DIR__ . '/config.php';


define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'raritybyadel_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    '3306');

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('Database connection failed: ' . $e->getMessage());
                }

                error_log('Database connection failed: ' . $e->getMessage());
                die('Database connection failed.');
            }
        }

        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}