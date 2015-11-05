<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This class contains all of the actions which can be performed on a Team within the system
 */
class Team extends Entity {

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

    public function updateName() {

    }

}