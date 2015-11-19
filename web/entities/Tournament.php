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
        $sth->execute([$this->Tournament_ID]);
        $results = $sth->fetch();
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

        if (isset($user->Team_ID) && is_numeric($user->Team_ID)) {
            ApplicationError('Team', 'Unable to create team, user is already a member of a team!');
        }

        $dbh = Database::getInstance();
        $sql = "INSERT INTO Tournaments(Tournament_Name, Tournament_Organizer_ID)
                Values(?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_Name, $user->Person_ID]);
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

        if ($team->Captain_ID != $user->Person_ID && !$user->hasRole('Organizer')) {
            ApplicationError("Team", "You must be the team captain to join a tournament!");
        }
        if ($this->Deleted == 1) {
            ApplicationError("Tournament", "The tournament has been canceled! Unable to join.");
        }
        if ($this->Status != 0) {
            ApplicationError("Tournament", "The tournament has already begun! Unable to join.");
        }

        if (intval($team->Deleted) === 1) {
            ApplicationError("Tournament", "Only active teams can join tournaments!");
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
        $sth->execute([$this->Tournament_ID]);
        $result = $sth->fetch();
        if (intval($result['count']) > 0) {
            ApplicationError("Tournament", "All matches must be complete before the tournament can end!");
        }

        $sql = "UPDATE Tournaments
                SET Status = 2
                WHERE Tournament_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
        return new Entity(['success'=>'Tournament successfully ended!']);
    }

    public function delete() {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Permissions", "You must be an organizer to delete a tournament!");
        }
        if ($this->Status != 0) {
            ApplicationError("Tournament", "A tournament must be in the planning phase to be deleted!");
        }

        $dbh = Database::getInstance();
        $sql = "UPDATE Tournaments
                SET Deleted = 1
                WHERE Tournament_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
    }

    public function begin($Tournament_Type) {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Permissions", "You must be an organizer to start a tournament!");
        }
        if ($this->Status != 0) {
            ApplicationError("Tournament", "A tournament must be in the planning phase to be started!");
        }

        if (!is_numeric($Tournament_Type) || (intval($Tournament_Type) < 0 || intval($Tournament_Type) > 2)) {
            ApplicationError("Tournament", "The tournament must be of value 0 (Knock Out), 1 (Round Robin) or 2(Knock out round robin)!");
        }
        $teams = $this->getTeams()->Teams;
        $numRegistered = sizeof($teams);

        if ($numRegistered < 2) {
            ApplicationError("Tournament", "You need at least 2 teams registered before the tournament can begin!");
        }

        if (($Tournament_Type == 0 || $Tournament_Type == 2) && ($numRegistered & ($numRegistered - 1) != 0)) {

            $numRequired = $numRegistered - 1;//Subtract 1 to handle the case that we are already a power of 2
            $numRequired |= $numRequired >> 1;//shift all bits to 1
            $numRequired |= $numRequired >> 2;//shift all bits to 1
            $numRequired |= $numRequired >> 4;//shift all bits to 1
            $numRequired |= $numRequired >> 8;//shift all bits to 1
            $numRequired |= $numRequired >> 16;//Support up to 32bit integer or 0x8000_0000
            $numRequired -= ($numRegistered-1);//Subtract our original

            ApplicationError("Tournament", "You n^2 teams to perform a knockout style tournament! That means you need {$numRequired} more teams!");
        }
        if ($Tournament_Type == 0) {
            if ($numRegistered > 2) {
                $matches = [];
                array_push($matches, Match::create($this->Tournament_ID));
                while (sizeof($matches) !== ($numRegistered / 4)) {
                    $tmpMatches = [];
                    foreach ($matches as $match) {
                        array_push($matches, Match::create($this->Tournament_ID, $match->Match_ID));
                        array_push($matches, Match::create($this->Tournament_ID, $match->Match_ID));
                    }
                    $matches = $tmpMatches;
                }
                for($i = 0; $i < sizeof($matches); $i++) {
                    Match::create($this->Tournament_ID, $matches[$i]->Match_ID, $teams[2*$i]->Team_ID, $teams[(2 * $i)+1]->Team_ID);
                }
            } else {
                Match::create($this->Tournament_ID, null, $teams[0]->Team_ID, $teams[1]->Team_ID);
            }
        } else if ($Tournament_Type == 1) {
            $matches = [];
            $teamsRotated = $teams;
            if (sizeof($teamsRotated)%2!=0) {
                array_push($teamsRotated, null);
            }
            $mid = sizeof($teamsRotated) / 2;
            do {
                for ($i = 0; $i < $mid; $i++) {
                    if ($teamsRotated[$i] !== null && $teamsRotated[$i+$mid] !== null) {
                        array_push($matches, ['Team_A_ID' => $teamsRotated[$i]->Team_ID, 'Team_B_ID' => $teamsRotated[$i + $mid]->Team_ID]);
                    }
                }
                $teamsRotated = Utils::rotateArray($teamsRotated);
            } while($teamsRotated !== $teams);
            foreach($matches as $match) {
                Match::create($this->Tournamnet_ID, null, $match['Team_A_ID'], $match['Team_B_ID']);
            }
        } else if($Tournament_Type) {
            //TODO: Implement krr tournament type
            ApplicationError("Matches", "This tournament type is not yet implemented! Sorry!");
        }

    }

    public function getMatches() {
        $matches = new Entity();
        $dbh = Database::getInstance();
        $sql = "SELECT Match_ID
                FROM Matches
                WHERE Tournament_ID = ?
                AND Status = 0";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
        $results = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
        $matches->addToData(['pregame'=>$results]);

        $sql = "SELECT Match_ID
                FROM Matches
                WHERE Tournament_ID = ?
                AND Status = 1";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
        $results = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
        $matches->addToData(['inprogress' => $results]);

        $sql = "SELECT Match_ID
                FROM Matches
                WHERE Tournament_ID = ?
                AND Status = 2";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
        $results = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
        $matches->addToData(['end' => $results]);

        return $matches;
    }

    public static function getTournaments($deleted = false) {
        $results = new Entity();
        $dbh = Database::getInstance();
        $sql = "SELECT Tournament_ID
                FROM Tournaments
                WHERE Status = ?";
        if (!$deleted) {
            $sql .= " AND Deleted = 0";
        }
        $sth = $dbh->prepare($sql);
        $sth->execute([0]);
        $results->addToData(['pregame'=> $sth->fetchAll()]);
        $sth->execute([1]);
        $results->addToData(['inprogress' => $sth->fetchAll()]);
        $sth->execute([2]);
        $results->addToData(['over' => $sth->fetchAll()]);
        return $results;
    }

    public function getTeams() {
        $dbh = Database::getInstance();
        $result = new Entity();
        $sql = "SELECT t.*
                FROM TournamentTeams tt
                    INNER JOIN Teams t ON tt.Team_ID = t.Team_ID
                WHERE tt.Tournament_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
        $result->addToData(['Tournament'=>$this->Tournament_ID, 'Teams'=>$sth->fetchAll()]);
        return $result;
    }

    public function getStandings() {
        if (intval($this->Status) === 0) {
            ApplicationError("Tournament", "A tournament must be started before the standings can be retrieved");
        }
        $result = new Entity(['Tournament_ID'=>$this->Tournament_ID, 'Tournament_Type'=>$this->Tournamnet_Type]);

        if ($this->Tournamnet_Type == 1) {
            //TODO: Implement
        }
        return $result;
    }

}