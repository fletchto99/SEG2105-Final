<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

class Match extends Entity {

    private function populate() {
        if (!isset($this->Match_ID)) {
            ApplicationError('Match', 'Match_ID is not defined!');
        }

        $dbh = Database::getInstance();
        $sql =
            "SELECT *
            FROM Matches
            WHERE Match_ID=?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Match_ID]);
        $results = $sth->fetch();
        if (!$results) {
            ApplicationError("Match", "No match found with the id: {$this->Match_ID}");
        }
        $this->data = $results;
    }

    public static function getMatch($matchID) {
        if (!isset($matchID)) {
            ApplicationError('Match', 'Match_ID is not defined!');
        }

        $match = new Match();
        $match->Match_ID = $matchID;
        $match->populate();
        return $match;
    }

    public static function create($tournamentID, $nextMatchID = null, $round = null, $teamAID = null, $teamBID = null) {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to create a match!", 403);
        }
        if (!is_numeric($tournamentID)) {
            ApplicationError("Match", "A valid tournament is required to make a match!");
        }
        $sql = "INSERT INTO Matches(Tournament_ID, Team_A_ID, Team_B_ID, Next_Match_ID, Round)
                VALUES (?,?,?,?,?)";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);

        $sth->execute([$tournamentID, $teamAID, $teamBID, $nextMatchID, $round]);
        return self::getMatch($dbh->lastInsertId());
    }

    public function addGoal($player, $assister=null) {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to add goals!", 403);
        }
        if ($this->Status != 1) {
            ApplicationError("Match", "A match must be in progress to add goals!");
        }
        if (!isset($player->Team_ID) || $player->Team_ID === null || ($player->Team_ID != $this->Team_A_ID && $player->Team_ID != $this->Team_B_ID)) {
            ApplicationError("Goal", "Only players who are participating in the match can score goals!");
        }
        $sql = "INSERT INTO Goals(Match_ID, Player_ID, Assist_ID, Team_ID)
                VALUES (?,?,?,?)";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);

        $assist_ID = $assister != null ? $assister->Person_ID : null;
        $sth->execute([$this->Match_ID, $player->Person_ID, $assist_ID, $player->Team_ID]);
    }

    public function begin() {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to begin a match!", 403);
        }
        $tournament = Tournament::getTournament($this->Tournament_ID);

        if ($tournament->Status != 1) {
            ApplicationError("Match", "A tournament must be in progress before a match can begin!");
        }

        if ($this->Status != 0) {
            ApplicationError("Match", "A match must be in the pre-game phase to be started!");
        }

        if(!isset($this->Team_A_ID) || !isset($this->Team_B_ID) || !is_numeric($this->Team_A_ID) || !is_numeric($this->Team_B_ID)) {
            ApplicationError("Match", "Both teams must be set before a match can begin!");
        }
        $dbh = Database::getInstance();

        $sql = "SELECT Count(*) as active
                FROM Matches
                WHERE Status = 1
                AND Tournament_ID = ?
                AND (Team_A_ID = ? OR Team_B_ID = ?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID, $this->Team_A_ID, $this->Team_A_ID]);
        $teamAActive = intval($sth->fetch()['active']) > 0;
        $sth->execute([$this->Tournament_ID, $this->Team_B_ID, $this->Team_B_ID]);
        $teamBActive = intval($sth->fetch()['active']) > 0;
        if ($teamAActive || $teamBActive) {
            ApplicationError("Match", "Please ensure both teams previous matches are over before beginning a new one");
        }


        $sql = "UPDATE Matches
                SET Status = 1
                WHERE Match_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Match_ID]);
        return new Entity(['success' => 'Match started!']);
    }

    public function end() {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to begin a match!", 403);
        }
        if ($this->Status != 1) {
            ApplicationError("Match", "A match must be in-progress to be finalized!");
        }

        $dbh = Database::getInstance();
        $sql = "SELECT count(*) as count
                FROM Goals
                WHERE Match_ID = ?
                AND Team_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Match_ID, $this->Team_A_ID]);
        $goals_A = $sth->fetch();
        $sth->execute([$this->Match_ID, $this->Team_B_ID]);
        $goals_B = $sth->fetch();

        if ($goals_A['count'] == $goals_B['count']) {
            ApplicationError('Match', "The match can't end in a tie! This is a tournament, one team must win!");
        }

        $winningTeamID = $goals_A > $goals_B ? $this->Team_A_ID : $this->Team_B_ID;

        $sql = "UPDATE Matches
                SET Status = 2,
                    Winning_Team_ID= ?
                WHERE Match_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$winningTeamID, $this->Match_ID]);

        if ($this->Next_Match_ID !== null) {
            $nextMatch = Match::getMatch($this->Next_Match_ID);
            $sql = "UPDATE Matches
                SET ".((isset($nextMatch->Team_A_ID) && is_numeric($nextMatch->Team_A_ID)) ? "Team_B_ID" : "Team_A_ID")." = ?
                WHERE Match_ID = ?";
            $sth = $dbh->prepare($sql);
            $sth->execute([$winningTeamID, $nextMatch->Match_ID]);
        }

        //Check for knockout round robin, and check if round robin stage is over
        $tournament = Tournament::getTournament($this->Tournament_ID);
        if ($tournament->Tournament_Type == 2) {
            $sql = "SELECT COUNT(*) as count
                    FROM Matches
                    WHERE Tournament_ID=?
                        AND Round=null";
            $sth = $dbh->prepare($sql);
            $sth->execute([$tournament->Torunament_ID]);
            $results = $sth->fetch();
            if (intval($results['count']) < 1) {
                ApplicationError("Implementation", "The knockout part is not yet implemented :(");
            }
        }

        return new Entity(['success' => 'Match ended!']);
    }

    public function withScores() {
        $dbh = Database::getInstance();
        $sql = "SELECT count(*) as count
                FROM Goals
                WHERE Match_ID = ?
                AND Team_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Match_ID, $this->Team_A_ID]);
        $goals_A = $sth->fetch();
        $sth->execute([$this->Match_ID, $this->Team_B_ID]);
        $goals_B = $sth->fetch();
        $this->addToData(['Team_A_Score'=>$goals_A['count']]);
        $this->addToData(['Team_B_Score'=>$goals_B['count']]);
        return $this;
    }

}