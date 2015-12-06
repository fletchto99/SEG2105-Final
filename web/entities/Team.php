<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This class contains all of the actions which can be performed on a Team within the system
 */
class Team extends Entity {

    /**
     * Populates the entity with the information from the database. Requires the entity's ID to be
     * defined before the information can be populated
     */
    private function populate() {
        if (!isset($this->Team_ID)) {
            ApplicationError('Team', 'Team_ID is not defined!');
        }

        $dbh = Database::getInstance();
        $sql =
            "SELECT *
            FROM Teams
            WHERE Team_ID=?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_ID]);
        $results = $sth->fetch();
        if (!$results) {
            ApplicationError("Team", "No team found with the id: {$this->Team_ID}");
        }
        $this->data = $results;
    }

    /**
     * Populates a teams's entity with the teams's info
     *
     * @param int $teamID The ID of the team
     *
     * @return Team The populated person entity
     */
    public static function getTeam($teamID) {
        if (!isset($teamID)) {
            ApplicationError('Team', 'teamID is not defined!');
        }

        $team = new Team();
        $team->Team_ID = $teamID;
        $team->populate();
        return $team;
    }

    /**
     * Creates a team and sets the captain id, unless otherwise specified
     *
     * @return Team The team which was created
     */
    public function create() {
        $user = Person::user();

        if (isset($user->Team_ID) && is_numeric($user->Team_ID)) {
           ApplicationError('Team', 'Unable to create team, user is already a member of a team!');
        }

        if (isset($this->Captain_ID) && !$user->hasRole('Organizer')) {
            ApplicationError("Team", "You must be an organizer to assign a specific captain", 403);
        } else if (isset($this->Captain_ID)) {
            $user = Person::getPerson($this->Captain_ID);
        }

        if ($user->Team_ID != null) {
            ApplicationError("Team", "You must not be part of a team to join a team!");
        }

        if (!isset($user->Jersey_Number) || !is_numeric($user->Jersey_Number)) {
            ApplicationError('User', 'You need a jersey number before you can create a team!');
        }


        $avatar = isset($this->Team_Avatar) ? $this->Team_Avatar : null;

        $dbh = Database::getInstance();
        $sql = "INSERT INTO Teams(Team_Name, Captain_ID, Team_Avatar)
                Values(?,?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_Name, $user->Person_ID, $avatar]);

        $teamID = $dbh->lastInsertId();
        $user->joinTeam(self::getTeam($teamID));

        return Team::getTeam($teamID);
    }

    /**
     * Updates the team's avatar with the avatar provided
     *
     * @param String $Team_Avatar The new team avatar
     * @return Entity A success message
     */
    public function updateAvatar($Team_Avatar) {
        $user = Person::user();
        if ($user->Person_ID != $this->Captain_ID && !$user->hasRole('Organizer')) {
            ApplicationError("Team", "You must be the team captain or an event organizer to rename a team!", 403);
        }
        $sql = "UPDATE Teams
                SET Team_Avatar=?
                WHERE Team_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$Team_Avatar, $this->Team_ID]);
        return new Entity(['success'=>"Team avatar updated to {$this->Team_Avatar}"]);
    }

    /**
     * Updates the team's name with the name provided
     *
     * @param String $Team_Name The new team name
     *
     * @return Entity A success message
     */
    public function updateName($Team_Name) {
        $user = Person::user();
        if ($user->Person_ID != $this->Captain_ID && !$user->hasRole('Organizer')) {
            ApplicationError("Team", "You must be the team captain or an event organizer to rename a team!", 403);
        }
        $sql = "UPDATE Teams
                SET Team_Name=?
                WHERE Team_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$Team_Name, $this->Team_ID]);
        $this->Team_Name = $Team_Name;
        return new Entity(['success'=>"Team name updated to {$this->Team_Name}"]);
    }

    /**
     * Fetches all of the tournaments the team is in
     *
     * @param int|null $status The status of the tournament
     *
     * @return array An array of all of the tournaments for the specific team
     */
    public function getTournaments($status = null) {
        $dbh = Database::getInstance();
        $sql = "SELECT t.Tournament_ID
                FROM Tournaments t
                    INNER JOIN TournamentTeams tt ON tt.Tournament_ID = t.Tournament_ID
                WHERE
                    tt.Team_ID = ?
                    AND t.Deleted = 0";
        if ($status != null) {
            $sql .= " AND t.Status = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $status]);
            return $sth->fetchAll();
        } else {
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID]);
            return $sth->fetchAll();
        }
    }

    /**
     * Fetches all of the matches the team has won
     *
     * @return int A count of all of the matches won
     */
    public function matchesWon() {
        $dbh = Database::getInstance();
        $sql = "SELECT COUNT(*) as count
                FROM Matches
                WHERE Winning_Team_ID=?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_ID]);
        $results = $sth->fetch();
        return $results['count'];
    }

    /**
     * Fetches all of the matches the team has lost
     *
     * @return int A count of all of the matches won
     */
    public function matchesLost() {
        $dbh = Database::getInstance();
        $sql = "SELECT COUNT(*) as count
                FROM Matches
                WHERE Winning_Team_ID != ?
                AND (Team_A_ID = ? OR Team_B_ID = ?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_ID, $this->Team_ID, $this->Team_ID]);
        $results = $sth->fetch();

        return $results['count'];
    }

    /**
     * Fetches all of the players on the team, along with their information
     *
     * @return Entity An entity containing all of hte players on the team
     */
    public function getPlayers() {
        $players = new Entity(['Team_ID'=>$this->Team_ID]);
        $dbh = Database::getInstance();
        $sql = "SELECT Person_ID, First_Name, Last_Name, Jersey_Number
                FROM Persons
                WHERE Team_ID=?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_ID]);
        $results = $sth->fetchAll();
        $players->addToData(['Players'=>$results]);

        return $players;
    }

    /**
     * Retrieves the players standings
     *
     * @param Tournament|null $tournament The tournament to limit the standings to
     *
     * @return Entity An entity containing all of the teams standings
     */
    public function getRankings($tournament = null) {
        $results = new Entity();
        $results->addToData(['Team_ID'=>$this->Team_ID]);
        $dbh = Database::getInstance();

        if (isset($tournament)) {
            $sql = "SELECT Player_ID, max(Goals_Scored) as Goals_Scored, First_Name, Last_Name, Jersey_Number
                    FROM (
                        SELECT g.Player_ID, count(*) as Goals_Scored, p.First_Name, p.Last_Name, p.Jersey_Number
                        FROM Goals g
                            INNER JOIN Persons p on p.Person_ID = g.Player_ID
                            INNER JOIN Matches m on g.Match_ID = m.Match_ID
                            INNER JOIN Tournaments t on t.Tournament_ID = m.Tournament_ID
                        WHERE g.Team_ID = ?
                            AND g.Player_ID IS NOT NULL
                            AND t.Tournament_ID = ?
                        GROUP BY g.Player_ID
                        HAVING count(*) > 0
                        UNION
                        SELECT Person_ID as Player_ID, 0 as Goals_Scored, First_Name, Last_Name, Jersey_Number
                        FROM Persons
                        WHERE Team_ID = ?
                            AND Person_ID IS NOT NULL
                    ) as Results
                    GROUP BY Player_ID";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $tournament->Tournament_ID, $this->Team_ID]);
            $results->addToData(['standings'=>$sth->fetchAll()]);
        } else {
            $sql = "SELECT Player_ID, max(Goals_Scored) as Goals_Scored, First_Name, Last_Name, Jersey_Number
                    FROM (
                        SELECT g.Player_ID, count(*) as Goals_Scored, p.First_Name, p.Last_Name, p.Jersey_Number
                        FROM Goals g
                            INNER JOIN Persons p on p.Person_ID = g.Player_ID
                        WHERE g.Team_ID = ?
                            AND g.Player_ID IS NOT NULL
                        GROUP BY g.Player_ID
                        HAVING count(*) > 0
                        UNION
                        SELECT Person_ID as Player_ID, 0 as Goals_Scored, First_Name, Last_Name, Jersey_Number
                        FROM Persons
                        WHERE Team_ID = ?
                            AND Person_ID IS NOT NULL
                    ) as Results
                    GROUP BY Player_ID";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $this->Team_ID]);
            $results->addToData(['standings' => $sth->fetchAll()]);
        }
        return $results;
    }

    /**
     * Checks if a jersey number is available on the team
     *
     * @param int $number The number to check
     *
     * @return bool True if the number is available; otherwise false
     */
    public function checkAvailableJerseyNumber($number) {
        $players = $this->getPlayers();
        $playersIter = $players->each()['Players'];
        foreach ($playersIter as $player) {
            if (isset($player['Jersey_Number']) && is_numeric($player['Jersey_Number']) && $number == intval($player['Jersey_Number'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fetches all of the teams in the system
     *
     * @return Entity An entity containing all of the teams
     */
    public static function getTeams() {
        $dbh = Database::getInstance();
        $result = new Entity();
        $sql = "SELECT t.Team_ID, t.Team_Name, p.First_Name as Captain_First_Name, p.Last_Name as Captain_Last_Name
                FROM Teams t
                    INNER JOIN Persons p ON p.Person_ID = t.Captain_ID
                WHERE t.Deleted = 0";
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $result->addToData(['Teams'=>$sth->fetchAll()]);
        return $result;
    }

    /**
     * Fetches all of the teams not in a specific tournament
     *
     * @param Tournament $tournament The tournament to check
     *
     * @return Entity An entity containing all of the teams not in the tournament
     */
    public static function getTeamsNotInTournament($tournament) {
        $dbh = Database::getInstance();
        $result = new Entity();
        $sql = "SELECT DISTINCT t.Team_ID, t.Team_Name
                FROM Teams t
                    LEFT JOIN TournamentTeams tt on tt.Team_ID = t.Team_ID AND tt.tournament_ID = ?
                WHERE t.Deleted = 0
                    AND tt.Tournament_ID IS NULL";
        $sth = $dbh->prepare($sql);
        $sth->execute([$tournament->Tournament_ID]);
        $result->addToData(['Teams' => $sth->fetchAll()]);

        return $result;
    }

}