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
        $results = $sth->execute([$this->Team_ID]);
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

        if (isset($user->Team_ID) && is_int($user->Team_ID)) {
           ApplicationError('Team', 'Unable to create team, user is already a member of a team!');
        }

        $dbh = Database::getInstance();
        $sql = "INSERT INTO Teams(Team_Name, Captain_ID)
                Values(?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Team_Name]);

        $teamID = $dbh->lastInsertId();
        $user->joinTeam($teamID);

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

}