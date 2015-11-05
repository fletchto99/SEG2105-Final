<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This class contains all of the actions which can be performed on a user within the system
 */
class Person extends Entity {

    private static $user;

    /**
     * Authenticates and retrieve the active user's information
     *
     * @return Person The active user
     */
    public static function user() {
        if (!isset(self::$user)) {
            session_start();
            if (!isset($_SERVER['PHP_AUTH_USER'])) {
                ApplicationError("Authentication", "Authentication credentials not defined!", 401);
            }
            $sql = "SELECT * FROM Persons WHERE Username = ? LIMIT 1";
            $dbh = Database::getInstance();
            $sth = $dbh->prepare($sql);
            $sth->execute([$_SERVER['PHP_AUTH_USER']]);
            $results = $sth->fetch();
            if (!$results) {
                ApplicationError("Authentication", "Your user account (${_SERVER['PHP_AUTH_USER']}) does not exist within the application.", 401);
            } else if ($results['Password'] != hash('sha256', ($_SERVER['PHP_AUTH_PW'] . $results['Salt']))) {
                ApplicationError("Authentication", "Invalid password.", 401);
            }
            self::$user = new Person(['Person_ID' => $results['Person_ID'], 'Username' => $results['Username']]);
        }

        return self::$user;
    }

    /**
     * Attempts to create a user with the username and password specified in the entity
     *
     * @return Person The active session for the user which was created
     */
    public function create() {
        $dbh = Database::getInstance();
        $sql =
            "SELECT COUNT(*) as count
             FROM Persons
             WHERE Username = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->username]);

        if ($sth->fetch()['count'] > 0) {
            ApplicationError('Authentication', "An account with the username {$this->username} already exists!");
        }
        $salt = bin2hex(openssl_random_pseudo_bytes(32));
        echo $this->password;
        echo $salt;
        $password = hash('sha256', ($this->password . $salt));
        $sql = "INSERT INTO Persons(Username, Password, Salt)
                Values(?,?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->username, $password, $salt]);
        $Person_ID = $dbh->lastInsertId();
        self::$user = new Person(['Person_ID' => $Person_ID, 'Username' => $this->username]);
        return self::user();
    }

    /**
     * Check if the person is a player and if so retrieves their player information
     */
    public function asPlayer() {

    }

}