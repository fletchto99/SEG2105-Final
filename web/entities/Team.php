<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This class contains all of the actions which can be performed on a Team within the system
 */
class Team extends Entity {

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

    public static function getTeam($teamID) {
        if (!isset($teamID)) {
            ApplicationError('Team', 'teamID is not defined!');
        }

        $team = new Team();
        $team->Team_ID = $teamID;
        $team->populate();
        return $team;
    }

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
            var_dump($user);
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

        return new Entity(['success'=>'Team successfully created!']);
    }

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

    public function getRankings($tournament = null) {
        $results = new Entity();
        $results->addToData(['Team_ID'=>$this->Team_ID]);
        $dbh = Database::getInstance();

        if (isset($tournament)) {
            $sql = "SELECT g.Player_ID, count(*) as Goals_Scored, p.First_Name, p.Last_Name
                    FROM Goals g
                        INNER JOIN Matches m on g.Match_ID = m.Match_ID
                        INNER JOIN Tournaments t on t.Tournament_ID = m.Tournament_ID
                        INNER JOIN Persons p on p.Person_ID = g.Player_ID
                    WHERE g.Team_ID = ?
                        AND g.Player_ID IS NOT NULL
                        AND t.Tournament_ID = ?
                    GROUP BY g.Player_ID
                    HAVING count(*) > 0
                    UNION
                    SELECT Person_ID as Player_ID, 0 as Goals_Scored, First_Name, Last_Name
                    FROM Persons
                    WHERE Team_ID = ?";

            $sql = "SELECT Player_ID, max(Goals_Scored) as Goals_Scored, First_Name, Last_Name
                    FROM (
                        SELECT g.Player_ID, count(*) as Goals_Scored, p.First_Name, p.Last_Name
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
                        SELECT Person_ID as Player_ID, 0 as Goals_Scored, First_Name, Last_Name
                        FROM Persons
                        WHERE Team_ID = ?
                            AND Person_ID IS NOT NULL
                    ) as Results";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $tournament->Tournament_ID, $this->Team_ID]);
            $results->addToData(['standings'=>$sth->fetchAll()]);
        } else {
            $sql = "SELECT Player_ID, max(Goals_Scored) as Goals_Scored, First_Name, Last_Name
                    FROM (
                        SELECT g.Player_ID, count(*) as Goals_Scored, p.First_Name, p.Last_Name
                        FROM Goals g
                            INNER JOIN Persons p on p.Person_ID = g.Player_ID
                        WHERE g.Team_ID = ?
                            AND g.Player_ID IS NOT NULL
                        GROUP BY g.Player_ID
                        HAVING count(*) > 0
                        UNION
                        SELECT Person_ID as Player_ID, 0 as Goals_Scored, First_Name, Last_Name
                        FROM Persons
                        WHERE Team_ID = ?
                            AND Person_ID IS NOT NULL
                    ) as Results";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $this->Team_ID]);
            $results->addToData(['standings' => $sth->fetchAll()]);
        }
        return $results;
    }

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