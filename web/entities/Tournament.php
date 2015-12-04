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
            ApplicationError('Permissions', 'You do not have the required role to create a tournament', 403);
        }

        if (isset($user->Team_ID) && is_numeric($user->Team_ID)) {
            ApplicationError('Team', 'Unable to create team, user is already a member of a team!');
        }

        if (!is_numeric($this->Tournament_Type) || $this->Tournament_Type < 0 || $this->Tournament_Type > 2) {
            ApplicationError("Tournament", "The tournament must be of value 0 (Knock Out), 1 (Round Robin) or 2(Knock out round robin)!");
        }

        $dbh = Database::getInstance();
        $sql = "INSERT INTO Tournaments(Tournament_Name, Tournament_Organizer_ID, Tournament_Type)
                Values(?,?,?)";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_Name, $user->Person_ID, $this->Tournament_Type]);
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
            ApplicationError("Team", "You must be the team captain to join a tournament!", 403);
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
            ApplicationError("Tournament", "You are not a tournament organizer!", 403);
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
            ApplicationError("Permissions", "You must be an organizer to delete a tournament!", 403);
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

    public function begin() {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Permissions", "You must be an organizer to start a tournament!", 403);
        }
        if ($this->Status != 0) {
            ApplicationError("Tournament", "A tournament must be in the planning phase to be started!");
        }

        $teams = $this->getTeams()->Teams;
        $numRegistered = sizeof($teams);

        if ($numRegistered < 2) {
            ApplicationError("Tournament", "You need at least 2 teams registered before the tournament can begin!");
        }

        if (!$this->Tournament_Type) {
            ApplicationError("Tournament","A tournament requires a valid type before it can begin!");
        }

        if (($this->Tournament_Type == 0 || $this->Tournament_Type == 2) && !Utils::isPowerOfTwo($numRegistered)) {

            $numRequired = Utils::getNextPowerSquared($numRegistered);
            $numRequired -= $numRegistered;

            ApplicationError("Tournament", "You n^2 teams to perform a knockout style tournament! That means you need {$numRequired} more team(s)!");
        }
        if ($this->Tournament_Type == 0) {
            $this->createKnockoutMatches($numRegistered, $teams);
        } else if ($this->Tournament_Type == 1) {
            $this->createRoundRobinMatches($teams);
        } else if($this->Tournament_Type) {
            $this->createRoundRobinMatches($teams);
            $this->createKnockoutMatches(Utils::getPreviousPowerSquared($numRegistered));
        }

        $dbh = DataBase::getInstance();
        $sql = "UPDATE Tournaments
                SET Status = 1
                WHERE Tournament_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);

    }

    private function createRoundRobinMatches($teams) {
        $matches = [];
        $teamsRotated = $teams;
        if (sizeof($teamsRotated)%2!=0) {
            array_push($teamsRotated, null);
            array_push($teams, null);
        }
        $mid = sizeof($teamsRotated) / 2;
        do {
            for ($i = 0; $i < $mid; $i++) {
                if ($teamsRotated[$i] !== null && $teamsRotated[$i+$mid] !== null) {
                    array_push($matches, ['Team_A_ID' => $teamsRotated[$i]['Team_ID'], 'Team_B_ID' => $teamsRotated[$i + $mid]['Team_ID']]);
                }
            }
            $teamsRotated = Utils::rotateArray($teamsRotated);
        } while($teamsRotated !== $teams);
        foreach($matches as $match) {
            Match::create($this->Tournament_ID, null, null, $match['Team_A_ID'], $match['Team_B_ID']);
        }
    }

    private function createKnockoutMatches($numTeams, $teams = null) {
        if ($teams !== null && $numTeams !== sizeof($teams)) {
            ApplicationError("Internal Error", "Size of teams doesn't match the amount of matches to create.");
        }
        if ($numTeams > 2) {
            $matches = [];
            $round = Utils::calculateNumRounds($numTeams);
            array_push($matches, Match::create($this->Tournament_ID, null, $round));
            $round--;
            while (sizeof($matches) !== ($numTeams / 4)) {
                $tmpMatches = [];
                foreach ($matches as $match) {
                    //Each match must have 2 child matches to determine 2 teams to play
                    array_push($matches, Match::create($this->Tournament_ID, $match->Match_ID, $round));
                    array_push($matches, Match::create($this->Tournament_ID, $match->Match_ID, $round));
                }
                $matches = $tmpMatches;
                $round--;
            }
            for($i = 1; $i < (sizeof($matches) + 1); $i++) {
                if ($teams !== null) {
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round, $teams[(4*$i)-4]['Team_ID'], $teams[(4 * $i)-3]['Team_ID']);
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round, $teams[(4*$i)-2]['Team_ID'], $teams[(4 * $i)-1]['Team_ID']);
                } else {
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round);
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round);
                }
            }
        } else {
            if ($teams !== null) {
                Match::create($this->Tournament_ID, null, 1, $teams[0]['Team_ID'], $teams[1]['Team_ID']);
            } else {
                Match::create($this->Tournament_ID, null, 1);
            }
        }
    }

    public function getMatches() {
        $matches = new Entity();
        $dbh = Database::getInstance();
        $sql = "SELECT Match_ID
                FROM Matches
                WHERE Tournament_ID = ?
                AND Status = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID, 0]);
        $matches->addToData(['pregame'=> $sth->fetchAll()]);
        $sth->execute([$this->Tournament_ID, 1]);
        $matches->addToData(['inprogress' => $sth->fetchAll()]);
        $sth->execute([$this->Tournament_ID, 2]);
        $matches->addToData(['over' => $sth->fetchAll()]);

        return $matches;
    }

    public static function getTournaments($deleted = false) {
        $results = new Entity();
        $dbh = Database::getInstance();
        $sql = "SELECT Tournament_ID, Tournament_Name, Tournament_Type
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
        $sql = "SELECT t.Team_ID, t.Team_Name, p.First_Name as Captain_First_Name, p.Last_Name as Captain_Last_Name
                FROM TournamentTeams tt
                    INNER JOIN Teams t ON tt.Team_ID = t.Team_ID
                    INNER JOIN Persons p ON t.Captain_ID = p.Person_ID
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
        $result = new Entity(['Tournament_ID'=>$this->Tournament_ID, 'Tournament_Type'=>$this->Tournament_Type]);
        $dbh = Database::getInstance();
        if ($this->Tournament_Type == 0) {
            $result -> addToData(['knockout_matches'=>$this->calcKOStats()]);
        } else if ($this->Tournament_Type == 1) {
            $result->addToData(['roundrobin_matches' => $this->calcRRStats()]);
        } else if ($this->Tournament_Type == 2) {
            $result->addToData(['knockout_matches' => $this->calcKOStats()]);
            $result->addToData(['roundrobin_matches' => $this->calcRRStats()]);
        }
        return $result;
    }

    private function calcRRStats() {
        $dbh = Database::getInstance();
        $teams = $this->getTeams()->Teams;
        $standings = [];
        foreach ($teams as $team) {

            $data = new Entity();
            $data->addToData(['Team_ID' => $team['Team_ID']]);
            $data->addToData(['Team_Name' => $team['Team_Name']]);

            $sql = "SELECT COUNT(*) as Matches_Won
                    FROM Matches
                    WHERE Winning_Team_ID = ?
                    AND Tournament_ID = ?
                    AND Round IS NULL
                    ORDER BY Round DESC";
            $sth = $dbh->prepare($sql);
            $sth->execute([$team['Team_ID'], $this->Tournament_ID]);
            $data->addToData(['Matches_Won' => $sth->fetch()['Matches_Won']]);

            $sql = "SELECT COUNT(*) as Matches_Played
                    FROM Matches
                    WHERE (Team_A_ID = ? OR Team_B_ID = ?)
                    AND Tournament_ID = ?
                    AND Round IS NULL
                    ORDER BY Round DESC";
            $sth = $dbh->prepare($sql);
            $sth->execute([$team['Team_ID'], $team['Team_ID'], $this->Tournament_ID]);
            $data->addToData(['Matches_Played' => $sth->fetch()['Matches_Played']]);
            array_push($standings, $data);
        }

        usort($standings, function ($a, $b) {
            return $b->Matches_Won - $a->Matches_Won;
        });
        return $standings;
    }

    private function calcKOStats() {
        $dbh = Database::getInstance();
        $sql = "SELECT m.Match_ID, m.Winning_Team_ID, m.Team_A_ID, m.Team_B_ID, m.Round, Team_A.Team_Name as Team_A_Name, Team_B.Team_Name as Team_B_Name, Winning_Team.Team_Name as Winning_Team_Name
                    FROM Matches m
                        INNER JOIN Teams Team_A ON Team_A.Team_ID = m.Team_A_ID
                        INNER JOIN Teams Team_B ON Team_B.Team_ID = m.Team_B_ID
                        INNER JOIN Teams Winning_Team ON Winning_Team.Team_ID = m.Winning_Team_ID
                    WHERE Tournament_ID = ?
                        AND Round IS NOT NULL
                    ORDER BY Round DESC";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);
        return $sth->fetchAll();
    }

}