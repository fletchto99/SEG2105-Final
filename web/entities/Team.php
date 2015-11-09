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
            ApplicationError("Team", "You must be an organizer to assign a specific captain");
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


        $dbh = Database::getInstance();
        $sql = "INSERT INTO Teams(Team_Name, Captain_ID)
                Values(?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_Name, $user->Person_ID]);

        $teamID = $dbh->lastInsertId();
        $user->joinTeam(self::getTeam($teamID));

        return new Entity(['success'=>'Team successfully created!']);
    }

    public function updateAvatar() {

    }

    public function updateName($Team_Name) {
        $user = Person::user();
        if ($user->Person_ID != $this->Captain_ID || !$user->hasRole('Organizer')) {
            ApplicationError("Team", "You must be the team captain or an event organizer to rename a team!");
        }
        $sql = "UPDATE Teams
                SET Team_Name=?
                WHERE Team_ID=?";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $sth->execute([$Team_Name, $this->Team_ID]);
        return new Entity(['success'=>"Team name updated to {$this->Team_Name}"]);
    }

    public function getTournaments($status = null) {
        $dbh = Database::getInstance();
        $sql = "SELECT *
                FROM Tournaments t
                    INNER JOIN TournamentTeams tt ON tt.Team_ID = t.Team_ID
                WHERE
                    t.Deleted = 0 AND
                    tt.Deleted = 0";
        if ($status != null) {
            $sql .= "AND t.Status = ?";
            $sth = $dbh->prepare($sql);
            return $sth->execute([$status]);
        } else {
            $sth = $dbh->prepare($sql);
            return $sth->execute();
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

        //TODO: Flawed logic, will only return players which have scores goals, should put players who haven't scored on the bottom, sorted by name or something
        if (isset($tournament)) {
            $sql = "SELECT g.Player_ID, count(g.*) as count
                    FROM Goals g
                        INNER JOIN Matches m on g.Match_ID = m.Match_ID
                        INNER JOIN Tournaments t on t.Tournament_ID = m.Tournament_ID
                    WHERE g.Team_ID = ? AND
                        t.Tournament_ID = ?
                    GROUP BY Player_ID
                    ORDER BY count DESC";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $tournament->Tournament_ID]);
            $results->addToData(['standings'=>$sth->fetchAll(PDO::FETCH_COLUMN, 0)]);
        } else {
            $sql = "SELECT Player_ID, count(*) as count
                    FROM Goals
                    WHERE Team_ID = ?
                    GROUP BY Player_ID
                    ORDER BY count DESC";
            $sth = $dbh->prepare($sql);
            $sth->execute([$this->Team_ID, $tournament->Tournament_ID]);
            $results->addToData(['standings' => $sth->fetchAll(PDO::FETCH_COLUMN, 0)]);
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

}