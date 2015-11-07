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
        $results = $sth->fetchAll();
        if (!$results) {
            ApplicationError("Match", "No match found with the id: {$this->Match_ID}");
        }
        $this->data = $results;
    }

    public static function getMatch($matchID) {
        if (!isset($tournamentID)) {
            ApplicationError('Match', 'matchID is not defined!');
        }

        $match = new Match();
        $match->Match_ID = $matchID;
        $match->populate();
        return $match;
    }

    public function addGoal($player, $assister=null) {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to add goals!");
        }
        if ($this->Status != 1) {
            ApplicationError("Match", "A match must be in progress to add goals!");
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
            ApplicationError("Match", "You must be a tournament organizer to begin a match!");
        }
        $tournament = Tournament::getTournament($this->Tournament_ID);

        if ($tournament->Status != 1) {
            ApplicationError("Match", "A tournament must be in progress before a match can begin!");
        }

        if ($this->Status != 0) {
            ApplicationError("Match", "A match must be in the pre-game phase to be started!");
        }

        $dbh = Database::getInstance();
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
            ApplicationError("Match", "You must be a tournament organizer to begin a match!");
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
            ApplicationError('Match', "The match can't end in a tie! This is a tournament!");
        }

        $sql = "UPDATE Matches
                SET Status = 2
                WHERE Match_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Match_ID]);

        return new Entity(['success' => 'Match ended!']);
    }

}