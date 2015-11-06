<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

class Tournament extends Entity {

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

        return new Entity(['success'=>'Team successfully created!']);
    }

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


}