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

    private function populate() {
        if (!isset($this->Person_ID)) {
            ApplicationError('Person', 'Person_ID is not defined!');
        }

        $dbh = Database::getInstance();
        $sql =
            "SELECT p.Person_ID, p.First_Name, p.Last_Name, p.Jersey_Number, p.Avatar, p.Team_ID, r.Role_Name
             FROM Persons p
                INNER JOIN Roles r ON r.Role_ID = p.Role_ID
             WHERE p.Person_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Person_ID]);
        $results = $sth->fetch();
        if (!$results) {
            ApplicationError("Person", "No person found with the id: {$this->Person_ID}");
        }
        $this->data = $results;
    }

    /**
     * Populates a person's entity with the user's info
     *
     * @param $personID
     * @return Person
     */
    public static function getPerson($personID) {
        if (!isset($personID)) {
            ApplicationError('Person', 'PersonID is not defined!');
        }

        $person = new Person();
        $person->Person_ID = $personID;
        $person->populate();
        return $person;
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
                    FROM Logins
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
            self::$user = self::getPerson($results['Person_ID']);
        } else {
            //Repopulate the entity to reflect any changes to the user
            self::$user = self::getPerson(self::$user->Person_ID);
        }

        return self::$user;
    }

    /**
     * Attempts to create a user with the username and password specified in the entity
     *
     * @param $mode Integer The creation mode, 0 for normal, 1 for only player, 2 for organizer
     *
     * @return Person The active session for the user which was created
     */
    public function create($personOnly = false) {
        $dbh = Database::getInstance();

        if (!$personOnly) {
            $sql =
                "SELECT COUNT(*) as count
             FROM Logins
             WHERE Username = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Username]);

            if ($sth->fetch()['count'] > 0) {
                ApplicationError('Authentication', "An account with the username {$this->Username} already exists!");
            }
        } else {
            $user = Person::user();
            if (!$user->hasRole('Organizer')) {
                ApplicationError('Permission', "You must be an organizer to create a player only!");
            }
        }

        if (isset($this->Role_ID)) {
            $user = Person::user();
            if (!$user->hasRole('Organizer')) {
                ApplicationError('Permission', "You must be an organizer to create an account with a specific role!");
            }
        }

        if (!ctype_alnum($this->Username)) {
            ApplicationError('Login', "A username may contain letters and numbers only!");
        }

        if (!ctype_alnum($this->Password)) {
            ApplicationError('Login', "A password may contain letter and numbers only!");
        }

        $sql = "INSERT INTO Persons(First_Name, Last_Name, Jersey_Number, Role_ID)
                VALUES (?,?,?,?)";
        $sth = $dbh->prepare($sql);

        $jerseyNumber = (isset($this->Jersey_Number) ? $this->Jersey_Number : null);
        $roleID = (isset($this->Role_ID) ? $this->Role_ID : 2);

        $sth->execute([$this->First_Name, $this->Last_Name, $jerseyNumber, $roleID]);
        $pid = $dbh->lastInsertId();

        if (!$personOnly) {
            $salt = bin2hex(openssl_random_pseudo_bytes(32));
            $password = hash('sha256', ($this->Password . $salt));
            $sql = "INSERT INTO Logins(Username, Password, Salt, Person_ID)
                Values(?,?,?,?)";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Username, $password, $salt, $pid]);
        }

        self::$user = self::getPerson($pid);
        return self::user();
    }

    public function joinTeam(Team $team) {

        if (isset($this->Team_ID) && is_numeric($this->Team_ID)) {
            ApplicationError('Team', 'You are already part of a team!');
        }

        if (!isset($this->Jersey_Number) || !is_numeric($this->Jersey_Number)) {
            ApplicationError('User', 'You need a jersey number before you can join a team!');
        }

        if (!$team->checkAvailableJerseyNumber($this->Jersey_Number)) {
            ApplicationError("Number", "The number {$this->Jersey_Number} is already taken on {$team->Team_Name}");
        }

        $dbh = Database::getInstance();
        $sql = "SELECT Count(*) as count
                FROM Persons
                WHERE Team_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$team->Team_ID]);
        $results = $sth->fetch();
        if (intval($results['count']) >= Configuration::MAX_TEAM_SIZE) {
            ApplicationError('Team', 'Sorry, this team is full!');
        }

        $sql = "UPDATE Persons
                SET Team_ID=?
                WHERE Person_ID=?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$team->Team_ID, $this->Person_ID]);
        $this->Team_ID = $team->Team_ID;
        return new Entity(['success' => 'Joined team']);
    }

    public function hasRole($role) {
        $sql = "SELECT count(p.Person_ID) as count
                FROM Persons p
                    INNER JOIN Roles r ON r.Role_ID = p.Role_ID
                WHERE p.Person_ID = ?
                  AND r.Role_Name = ?";
        $dbh = Database::getInstance();

        $sth = $dbh ->prepare($sql);
        $sth->execute([$this->Person_ID, $role]);
        $results = $sth->fetch();
        return intval($results['count']) > 0;
    }

    public function updateJerseyNumber($number) {
        if (!$this->hasRole('Player')) {
            ApplicationError("Player", "You must be a player to have a jersey number!");
        }

        if (!is_numeric($number)) {
            ApplicationError("Number", "The jersey number must be a valid integer!");
        }

        $number = intval($number);

        if (isset($this->Team_ID) && is_numeric($this->Team_ID)) {
            $team = Team::getTeam($this->Team_ID);
            if (!$team->checkAvailableJerseyNumber($number)) {
                ApplicationError("Number", "The number {$number} is already taken on {$team->Team_Name}");
            }
        }

        $sql = "UPDATE Persons
                SET Jersey_Number=?
                WHERE Person_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$number, $this->Person_ID]);
        $this->Jersey_Number = $number;
        return new Entity(['Success'=>"Player number changed to {$number}"]);
    }

    public function leaveTeam() {
        if (!$this->hasRole('Player')) {
            ApplicationError("Permission", "You must be a player to leave a team!");
        }
        if (isset($this->Team_ID)) {
            ApplicationError("Team", "You must be part of a team before you can leave it.");
        }
        $team = Team::getTeam($this->Team_ID);
        $tournaments = $team->getTournaments(1);
        if (sizeof($tournaments) > 0) {
            ApplicationError("Team", "Your team is currently in an active tournament! Please wait for the tournament to finish before leaving.");
        }
        $dbh = Database::getInstance();
        if ($team->Captain_ID==$this->Person_ID) {
            $sql = "UPDATE Teams
                    SET Captain_ID = null,
                        Deleted = 1
                    WHERE Team_ID = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID]);
            $sql = "UPDATE Persons
                    SET Team_ID = null
                    WHERE Team_ID = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID]);
        } else {
            $sql = "UPDATE Persons
                    SET Team_ID = null
                    WHERE Person_ID = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Person_ID]);
        }
        $this->Team_ID = null;
    }

    public function statistics() {
        $dbh = Database::getInstance();
        $sql = "SELECT Count(*) as count
                FROM Goals
                WHERE Player_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Person_ID]);
        $results = $sth->fetch();
        $this->addToData(['Goals_Scored'=>$results['count']]);

        $sql = "SELECT Count(*) as count
                FROM Goals
                WHERE Assist_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Person_ID]);
        $results = $sth->fetch();
        $this->addToData(['Assists' => $results['count']]);

        //TODO: Flaw exists here as it only calculates for the current team. Our data structure currently would not support looking up matches won with other teams
        if (isset($this->Team_ID) && is_numeric($this->Team_ID)) {
            $team = Team::getTeam($this->Team_ID);
            $this->addToData(['Matches_With_Team' => ['Won' => $team->matchesWon(), 'Lost' => $team->matchesLost()]]);
            $this->addToData(['Active_Tournaments' => $team->getTournaments(1)]);
        } else {
            $this->addToData(['Matches_With_Team' => ['Won' => 0, 'Lost' => 0]]);
            $this->addToData(['Active_Tournaments' => []]);
        }

        return $this;
    }

}