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
     * Populates a person's entity with the user's info
     *
     * @param $personID
     * @return Person
     */
    public static function getUserInfo($personID) {
        if (!isset($personID)) {
            ApplicationError('Person', 'PersonID is not defined!');
        }

        $person = new Person();
        $person->Person_ID = $personID;
        $person->populate();
        return $person;
    }


    private function populate() {
        if (!isset($this->Person_ID)) {
            ApplicationError('Person', 'Person_ID is not defined!');
        }

        $dbh = Database::getInstance();
        $sql =
            "SELECT p.Person_ID, p.First_Name, p.Last_Name, p.Username, p.Jersey_Number, p.Avatar, p.Team_ID r.Role_Name
             FROM Persons
                INNER JOIN Roles r ON r.Role_ID = p.Role_Id
             WHERE p.Person_ID = ?";
        $sth = $dbh->prepare($sql);
        $results = $sth->execute([$this->Person_ID]);
        if (!$results) {
            ApplicationError("Person", "No person found with the id: {$this->Person_ID}");
        }
        $this->data = $results;
    }

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
            $sql = "SELECT Person_ID, Password, Salt
                    FROM Persons p
                    WHERE Username = ? LIMIT 1";
            $dbh = Database::getInstance();
            $sth = $dbh->prepare($sql);
            $sth->execute([$_SERVER['PHP_AUTH_USER']]);
            $results = $sth->fetch();
            if (!$results) {
                ApplicationError("Authentication", "Your user account (${_SERVER['PHP_AUTH_USER']}) does not exist within the application.", 401);
            } else if ($results['Password'] != hash('sha256', ($_SERVER['PHP_AUTH_PW'] . $results['Salt']))) {
                ApplicationError("Authentication", "Invalid password.", 401);
            }
            self::$user = self::getUserInfo($results['Person_ID']);
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
        $sth->execute([$this->Username]);

        if ($sth->fetch()['count'] > 0) {
            ApplicationError('Authentication', "An account with the username {$this->Username} already exists!");
        }
        $salt = bin2hex(openssl_random_pseudo_bytes(32));
        $password = hash('sha256', ($this->Password . $salt));
        $sql = "INSERT INTO Persons(Username, Password, Salt, First_Name, Last_Name, Jersey_Number, Role_ID)
                Values(?,?,?,?,?,?,1)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Username, $password, $salt, $this->First_Name, $this->Last_Name, (isset($this->Jersey_Number) ? $this->Jersey_Number : null)]);
        self::$user = self::getUserInfo($dbh->lastInsertId());
        return self::user();
    }

    public function joinTeam($team) {
        $user = Person::user();

        if (isset($user->Team_ID) && is_int($user->Team_ID)) {
            ApplicationError('Team', 'You are already part of a team!');
        }

        $dbh = Database::getInstance();
        $sql = "UPDATE Persons
                SET Team_ID=?
                WHERE Person_ID=?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$team, $user->Person_ID]);
    }

    public function hasRole($role) {
        $sql = "SELECT count(p.Person_ID) as count
                FROM Persons p
                    INNER JOIN Roles r ON r.Role_ID = p.Role_ID
                WHERE p.Person_ID = ?
                  AND r.Role_Name = ?";
        $dbh = Database::getInstance();

        $sth = $dbh ->prepare($sql);
        $results = $sth->execute([$this->Person_ID, $role]);
        return intval($results['count']) > 0;
    }

    public function updateJerseyNumber($number) {
        if (!$this->hasRole('Player')) {
            ApplicationError("Player", "You must be a player to have a jersey number!");
        }
        $sql = "UPDATE Persons
                SET Jersey_Number=?
                WHERE Person_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$number, $this->Person_ID]);
        return new Entity(['Success'=>"Player number changed to {$number}"]);
    }

}