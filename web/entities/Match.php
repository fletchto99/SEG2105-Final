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
        $results = $sth->execute([$this->Match_ID]);
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

}