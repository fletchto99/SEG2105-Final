<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

class Tournament extends Entity {

    private function populate() {
        if (!isset($this->Tournament_ID)) {
            ApplicationError('Tournament', 'Tournament_ID is not defined!');
        }

        $dbh = Database::getInstance();
        $sql =
            "SELECT *
            FROM Tournaments
            WHERE Tournament_ID=?";
        $sth = $dbh->prepare($sql);
        $results = $sth->execute([$this->Tournament_ID]);
        if (!$results) {
            ApplicationError("Tournament", "No tournament found with the id: {$this->Tournament_ID}");
        }
        $this->data = $results;
    }

    public static function getTournament($tournamentID) {
        if (!isset($tournamentID)) {
            ApplicationError('Tournament', 'Tournament_ID is not defined!');
        }

        $tournament = new Tournament();
        $tournament->Tournament_ID = $tournamentID;
        $tournament->populate();
        return $tournament;
    }

    /**
     * Creates a tournament
     *
     * @return Entity
     */
    public function create() {
        $user = Person::user();

        if (!$user->hasRole('Organizer')) {
            ApplicationError('Permissions', 'You do not have the required role to create a tournament');
        }

        if (isset($user->Team_ID) && is_int($user->Team_ID)) {
            ApplicationError('Team', 'Unable to create team, user is already a member of a team!');
        }

        $dbh = Database::getInstance();
        $sql = "INSERT INTO Tournaments(Tournamnet_Name, Tournamnet_Organizer_ID)
                Values(?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournamnet_Name, $user->Person_ID]);
        return self::getTournament($dbh->lastInsertId());
    }

    /**
     * Adds a team to the tournament
     *
     * @param $team
     * @return Entity
     */
    public function addTeam($team) {
        $user = Person::user();

        if ($team->Captain_ID != $user->Person_ID) {
            ApplicationError("Team", "You must be the team captain to join a tournament!");
        }

        if ($this->Status != 0) {
            ApplicationError("Tournament", "The tournament has already begun! Unable to join.");
        }

        $dbh = Database::getInstance();
        $sql = "INSERT INTO TournamentTeams(Tournament_ID, Team_ID)
                Values(?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID, $team->Team_ID]);

        return new Entity(['Success' => 'Your team has joined the tournament.']);
    }

    public function end() {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Tournament", "You are not a tournament organizer!");
        }
        if ($this->Status != 1) {
            ApplicationError("Tournament", "The tournament is not in progress!");
        }

        $sql = "SELECT count(*) as count
                FROM Matches
                WHERE Tournament_ID = ?
                  AND Status <> 2";

        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$this->Tournament_ID]);
        if (intval($result['count']) > 0) {
            ApplicationError("Tournament", "All matches must be complete before the tournament can end!");
        }
    }


}