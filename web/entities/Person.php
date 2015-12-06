<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This class contains all of the actions which can be performed on a user within the system
 */
class Person extends Entity {

    //The user's instance
    private static $user;

    /**
     * Populates the entity with the information from the database. Requires the entity's ID to be
     * defined before the information can be populated
     */
    private function populate() {
        if (!isset($this->Person_ID)) {
            ApplicationError('Person', 'Person_ID is not defined!');
        }

        $dbh = Database::getInstance();
        $sql =
            "SELECT p.Person_ID, p.First_Name, p.Last_Name, p.Jersey_Number, p.Person_Avatar, p.Team_ID, r.Role_Name
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
     * @param int $personID The ID of the person
     * @return Person The populated person entity
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
        //singleton functionality such that only one instance can exist
        if (!isset(self::$user)) {
            //Begin/resume the user's session
            session_start();

            //Check if they are already authenticated, or are sending authentication headers
            if (!isset($_SESSION['auth']) && !isset($_SERVER['PHP_AUTH_USER'])) {
                ApplicationError("Authentication", "Authentication credentials not defined!", 401);
            }

            //check the auth header sent by the client
            if(isset($_SERVER['PHP_AUTH_USER'])) {

                //check for an empty username
                if (empty($_SERVER['PHP_AUTH_USER'])) {
                    ApplicationError("Authentication", "Username cannot be blank!", 401);
                }

                //Set their session username
                $_SESSION['auth'] = $_SERVER['PHP_AUTH_USER'];
            }

            //Compare their login to the information contained in the DB
            $sql = "SELECT Person_ID, Password, Salt
                    FROM Logins
                    WHERE Username = ? LIMIT 1";
            $dbh = Database::getInstance();
            $sth = $dbh->prepare($sql);
            $sth->execute([$_SESSION['auth']]);
            $results = $sth->fetch();

            //Invalid account
            if (!$results) {
                //kill any session they created
                logout();
                ApplicationError("Authentication", "Your user account (${_SERVER['PHP_AUTH_USER']}) does not exist within the application.", 401);
            } else if (isset($_SERVER['PHP_AUTH_USER']) && $results['Password'] != hash('sha256', ($_SERVER['PHP_AUTH_PW'] . $results['Salt']))) {
                //Invalid password
                logout();
                ApplicationError("Authentication", "Invalid password.", 401);
            }
            //valid account, retrieve their information and set it as their active user
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
     * @param bool $personOnly Determine if only the person information should be populated or if login data should also be processed
     *
     * @return Person The active session for the user which was created
     */
    public function create($personOnly = false) {
        $dbh = Database::getInstance();

        if (empty($this->First_Name) || empty($this->Last_Name)) {
            ApplicationError('Login', "First name and last name must be set!");
        }

        //Person only allows
        if (!$personOnly) {
            if (!ctype_alnum($this->Username)) {
                ApplicationError('Login', "A username may contain letters and numbers only!");
            }

            //validate password is alphanumeric only
            // TODO: Should be upgraded to support special characters, but for simplicity we limit to alphanumeric for now
            if (!ctype_alnum($this->Password)) {
                ApplicationError('Login', "A password may contain letter and numbers only!");
            }

            //validate username & password
            if (strlen($this->Username) < 5 || strlen($this->Password) < 5) {
                ApplicationError('Login', "Username and password must have at least 5 characters!");
            }

            //check to ensure the username is free
            $sql = "SELECT COUNT(*) as count
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
                ApplicationError('Permission', "You must be an organizer to create a player only!", 403);
            }
        }

        // if an organizer account is being created, ensure it is being created by another organizer
        if (isset($this->Role_ID)) {
            $user = Person::user();
            if (!$user->hasRole('Organizer')) {
                ApplicationError('Permission', "You must be an organizer to create an account with a specific role!", 403);
            }
        }

        $sql = "INSERT INTO Persons(First_Name, Last_Name, Jersey_Number, Person_Avatar, Role_ID)
                VALUES (?,?,?,?,?)";
        $sth = $dbh->prepare($sql);

        //check for jersey number or set it to null
        $jerseyNumber = (isset($this->Jersey_Number) ? $this->Jersey_Number : null);
        $avatar = (isset($this->Person_Avatar) ? $this->Person_Avatar : null);
        $roleID = (isset($this->Role_ID) ? $this->Role_ID : 2);

        $sth->execute([$this->First_Name, $this->Last_Name, $jerseyNumber, $avatar, $roleID]);
        $pid = $dbh->lastInsertId();

        if (!$personOnly) {
            /*
             * Some security, for now we just hash their password using a secure
             * 256-bit salt in it's hex equivalent, could be more secure but this is fine for a school project
             */
            $salt = bin2hex(openssl_random_pseudo_bytes(32));
            $password = hash('sha256', ($this->Password . $salt));
            $sql = "INSERT INTO Logins(Username, Password, Salt, Person_ID)
                Values(?,?,?,?)";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Username, $password, $salt, $pid]);
            logout(); //clear any previous session which shouldn't have existed
            session_start(); //start their new session
            $_SESSION['auth'] = $this->Username; //Set their session username variable
        }

        self::$user = self::getPerson($pid);
        return self::user();
    }

    /**
     * Fetches all On the Fly players created by tournament organizers (not associated with a login)
     *
     * @param bool|false $no_team_assigned Only fetch players who are not assigned to a team
     *
     * @return Entity The Persons who were created by tournament organizers
     */
    public static function getOTFPlayers($no_team_assigned = false) {
        $user = Person::user();
        if ($user->Role_Name != 'Organizer') {
            ApplicationError("Permissions", "You must be a tournament organizer to view all manual players");
        }

        $dbh = DataBase::getInstance();
        $sql = "SELECT p.Person_ID, p.First_Name, p.Last_Name
                FROM Persons p
                    LEFT JOIN Logins l ON p.Person_ID = l.Person_ID
                WHERE Login_ID IS NULL";
        if ($no_team_assigned) {
            $sql .= " AND p.Team_ID IS NULL";
        }
        $sth = $dbh->prepare($sql);
        $sth->execute();

        return new Entity(['Players' => $sth->fetchAll()]);
    }

    /**
     * Adds the current user to the team
     *
     * @param Team $team the team to join
     * @return Entity Simple success message
     */
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

        if ($this->Role_Name == 'Organizer') {
            ApplicationError("Team", "Organizers can't join teams!");
        }

        $dbh = Database::getInstance();
        $sql = "SELECT Count(*) as count
                FROM Persons
                WHERE Team_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$team->Team_ID]);
        $results = $sth->fetch();

        //validate the team has room for more players
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

    /**
     * Checks if the user is assigned a specific role
     *
     * @param String $role The role to check that the user has
     * @return bool True if the user has the role; otherwise false
     */
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

    /**
     * Update's a users jersey number
     *
     * @param int $number The number to give the user
     *
     * @return Entity A success message
     */
    public function updateJerseyNumber($number) {
        if (!$this->hasRole('Player')) {
            ApplicationError("Player", "You must be a player to have a jersey number!", 403);
        }

        if (!is_numeric($number)) {
            ApplicationError("Number", "The jersey number must be a valid integer!");
        }

        $number = intval($number);

        if ($number == $this->Jersey_Number) {
            ApplicationError("Number", "{$number} is already your jersey number!");
        }

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
        return new Entity(['Success'=>"Jersey number changed to {$number}", 'Number' => $this->Jersey_Number]);
    }

    /**
     * Removes the user form their current team
     */
    public function leaveTeam() {
        if (!$this->hasRole('Player')) {
            ApplicationError("Permission", "You must be a player to leave a team!", 403);
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

        //Handle the edge case where the team captain decides to leave. We need to remove all players on the team
        //TODO: Alternatively the captain role can be handed off to another user... Perhaps ask who should be come captain when the captain decides to leave the team
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

    /**
     * Retrieves all of the statistics for an individual player
     *
     * @return $this The person entity with updated statistics
     */
    public function statistics() {
        $dbh = Database::getInstance();

        //count their goals
        $sql = "SELECT Count(*) as count
                FROM Goals
                WHERE Player_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Person_ID]);
        $results = $sth->fetch();
        $this->addToData(['Goals_Scored'=>$results['count']]);

        //count their assists
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

    /**
     * Updates a users avatar. TODO: Actually implements this properly, $_FILE in the future? Needs some way to upload a picture...
     *
     * @param String $Person_Avatar The avatar of the user
     *
     * @return Entity a success message
     */
    public function updateAvatar($Person_Avatar) {
        $avatar = intval($Person_Avatar);

        if ($Person_Avatar == $this->$Person_Avatar) {
            ApplicationError("Avatar", "{$avatar} is already your avatar!");
        }

        $sql = "UPDATE Persons
                SET Person_Avatar=?
                WHERE Person_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$Person_Avatar, $this->Person_ID]);
        $this->Person_Avatar = $Person_Avatar;
        return new Entity(['Success'=>"Player avatar changed to {$Person_Avatar}"]);
    }

    /**
     * Updates a person's role to set them as an organizer
     *
     * @param Person $person The person to set as an organizer
     * @return Entity A success message
     */
    public function setAsOrganizer($person) {
        $user = Person::user();
        if ($user->Role_Name != 'Organizer') {
            ApplicationError("Permissions","Only organizers can set other's roles to organizer","403");
        }
        if ($person-> Team_ID != null) {
            ApplicationError("Organizer", "To set someone as an organizer, the must first leave their team!");
        }

        $sql = "UPDATE Persons
                SET Role_ID=1,
                    Jersey_Number=NULL
                WHERE Person_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$person->Person_ID]);

        return new Entity(['Success' => "Player now an organizer!"]);
    }

}