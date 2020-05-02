<?php
include_once 'config.php';

class Database
{
    /**
     * Based on https://websitebeaver.com/php-pdo-prepared-statements-to-prevent-sql-injection
     */
    private $pdo;
    private $dsn = 'mysql:host=localhost;dbname='.DB_NAME.';charset=utf8mb4';
    private $options = [
        PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
    ];

    public function __construct() {
        try {
            $this->pdo = new PDO(
                $this->dsn, 
                DB_USER, 
                DB_PASSWORD,
                $this->options
            );
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $this->pdo = FALSE;
        }
    }

    public function getPdo() {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }
        return FALSE;
    }

    /*
    public function executeQuery($query, $params) {
        return TRUE;
    }
    */

}
