<?php
/**
 *  Details     :  Database Singleton used to connect an retrieve a connection to the database using PDO
 *  Author(s)   :  Matt Langlois
 *
 */

require_once "bootstrap.php";

class Database extends PDO {

    /*
     * The instance of the connection to the database
     */
    private static $instance;

    /**
     * Retrieves the instance of the database connection
     *
     * @return Database The connected instance to the database
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Database();
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::$instance;
    }

    /**
     * Creates an instance of a connection to the database
     */
    public function __construct() {
        $dsn = "mysql:host=".Configuration::DATABASE_HOST.";dbname=".Configuration::DATABASE_NAME;
        self::connect($dsn, Configuration::DATABASE_USER, Configuration::DATABASE_PASSWORD);
    }

    /*
     * Connect to database and return PDO instance
     */
    private function connect($dsn, $user, $pass) {
        try {
            parent::__construct($dsn, $user, $pass);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            ApplicationError("database", $e->getMessage());
        }
    }
}