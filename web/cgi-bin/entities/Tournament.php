<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

class Tournament extends Entity {

    /**
     * Populates the entity with the information from the database. Requires the entity's ID to be
     * defined before the information can be populated
     */
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

    /**
     * Populates a Tournament entity with the information regarding a specific tournament
     *
     * @param int $tournamentID The ID of the match to lookup
     *
     * @return Tournament A populated tournament entity
     */
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
     * @return Entity The tournament which was created
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
     * @param Team $team The team to add
     * @return Entity A success message
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

    /**
     * Updates a tournament status to ended
     *
     * @return Entity A success message
     */
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

        //Validate all matches within the tournament are over
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

    /**
     * Deletes a tournament if it was in the planning stages
     */
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

    /**
     * Starts a tournament. We generate the matches based off of the teams which are entered into the tournament
     * along with the type of the tournament. In a round robin we generate all matches up front. In a knockout we
     * generate all of the first rounds matches and then set their parents to the next round until we finally have
     * one round left. In the case of Knockout roundrobin we only set the round robin stage of hte tournament and then
     * we generate the knockout stage once the roundrobin stage has ended
     */
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

        if (!isset($this->Tournament_Type) || !is_numeric($this->Tournament_Type)) {
            ApplicationError("Tournament","A tournament requires a valid type before it can begin!");
        }

        /* A knockout tournament in our system acts like a binary tree,
         * where the winner of the root node is the winner of the tournament
         * The leaf nodes represent the first round matches and the root represents
         * the final match to be played, therefore we need some 2^n teams
         * registered to generate a knockout style tournament
         */
        if ($this->Tournament_Type == 0 && !Utils::isPowerOfTwo($numRegistered)) {

            $numRequired = Utils::getNextTwoPower($numRegistered);
            $numRequired -= $numRegistered;

            ApplicationError("Tournament", "You need 2^n teams to perform a knockout style tournament! That means you need {$numRequired} more team(s)!");
        }

        //build the matches based on the tournament type selected
        if ($this->Tournament_Type == 0) {
            $this->createKnockoutMatches($numRegistered, $teams);
        } else if ($this->Tournament_Type == 1) {
            $this->createRoundRobinMatches($teams);
        } else if($this->Tournament_Type == 2) {
            //generate the round robin pages
            $this->createRoundRobinMatches($teams);
            //Create "empty" knockout rounds which will be populated later when the final match of the round robin stage is over
            $this->createKnockoutMatches(Utils::getPreviousTwoPower($numRegistered));
        }

        //set the tournament as active
        $dbh = DataBase::getInstance();
        $sql = "UPDATE Tournaments
                SET Status = 1
                WHERE Tournament_ID = ?";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID]);

    }

    /**
     * Generates the round robin matches using a round-robin array approach
     *
     * @param array $teams The teams to be entered into the tournament
     */
    private function createRoundRobinMatches($teams) {
        $teamsRotated = $teams;

        /*
         * Our algorithm only works on even arrays, therefore if we have an odd sized array we push
         * a null element which will represent a "bye" in which case the match isn't actually played
         * and there is no winner in a bye.
         */
        if (sizeof($teamsRotated)%2!=0) {
            array_push($teamsRotated, null);
            array_push($teams, null);//we also push to our original to maintain consistency
        }

        //determine the middle of the array
        $mid = sizeof($teamsRotated) / 2;
        do {
            /*
             * Pair each team with another team in the array using an outside in approach.
             * I.E a,b,c,d,e,null then a -> null (bye), b->e, c->d
             */
            for ($i = 0; $i < $mid; $i++) {
                //check for a bye match
                if ($teamsRotated[$i] !== null && $teamsRotated[(2 * $mid) - ($i+1)] !== null) {
                    //Create the match
                    Match::create($this->Tournament_ID, null, null, $teamsRotated[$i]['Team_ID'], $teamsRotated[(2 * $mid) - ($i + 1)]['Team_ID']);
                }
            }
            //rotate circular right all elements after index 0 I.E. a,b,c,d,e becomes a,e,b,c,d
            $teamsRotated = Utils::rotateArray($teamsRotated);
        } while($teamsRotated !== $teams);//continue until we have rotated back to our original array
    }

    /**
     * Generates all of the knockout matches which will occur
     *
     * @param $numTeams int The number of teams to create knockout matches for
     * @param null $teams The teams to populate the first round with (null for knockout roundrobin)
     */
    private function createKnockoutMatches($numTeams, $teams = null) {
        //if we are populating the first round with teams then the number of teams should match how many we will populate
        if ($teams !== null && $numTeams !== sizeof($teams)) {
            ApplicationError("Internal Error", "Size of teams doesn't match the amount of matches to create.");
        }
        //Only a true knockout if number > 2, otherwise only one match is made
        if ($numTeams > 2) {
            $matches = [];
            $round = Utils::calculateNumRounds($numTeams);//calculates the number of rounds working in a top down alogrithm
            array_push($matches, Match::create($this->Tournament_ID, null, $round));//create the first "top level" match
            $round--;//decrease the round

            /*
             * continue making matches until we are in the second last round (the round before the leaf matches)
             * We divide the number of teams by 4 to determine the break case because the last round has one match
             * per 2 teams (/2) and each of those matches have one parent for every 2 (/2) so /2/2 = /4
             */
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

            //Generate leaf matches
            for($i = 1; $i < (sizeof($matches) + 1); $i++) {
                //Knockout style we add the teams, knockout roundrobin we leave them blank
                if ($teams !== null) {
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round, $teams[(4*$i)-4]['Team_ID'], $teams[(4 * $i)-3]['Team_ID']);
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round, $teams[(4*$i)-2]['Team_ID'], $teams[(4 * $i)-1]['Team_ID']);
                } else {
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round);
                    Match::create($this->Tournament_ID, $matches[$i-1]->Match_ID, $round);
                }
            }
        } else {
            //edge case where only 2 teams are specified
            if ($teams !== null) {
                Match::create($this->Tournament_ID, null, 1, $teams[0]['Team_ID'], $teams[1]['Team_ID']);
            } else {
                Match::create($this->Tournament_ID, null, 1);
            }
        }
    }

    /**
     * Fetches all matches in the tournament
     *
     * @return Entity An entity containing all of the matches in the tournament
     */
    public function getMatches() {
        $matches = new Entity();
        $dbh = Database::getInstance();
        $sql = "SELECT m.Match_ID, m.Team_A_ID, m.Team_B_ID, a.Team_Name as Team_A_Name, b.Team_Name as Team_B_Name, m.Winning_Team_ID, w.Team_Name as Winning_Team_Name, m.Round
                FROM Matches m
                    LEFT JOIN Teams a ON a.Team_ID = m.Team_A_ID
                    LEFT JOIN Teams b ON b.Team_ID = m.Team_B_ID
                    LEFT JOIN Teams w ON w.Team_ID = m.Winning_Team_ID
                WHERE m.Tournament_ID = ?
                AND m.Status = ?
                ORDER BY Round ASC";
        $sth = $dbh->prepare($sql);
        $sth->execute([$this->Tournament_ID, 0]);
        $matches->addToData(['pregame'=> $sth->fetchAll()]);
        $sth->execute([$this->Tournament_ID, 1]);
        $matches->addToData(['inprogress' => $sth->fetchAll()]);
        $sth->execute([$this->Tournament_ID, 2]);
        $matches->addToData(['over' => $sth->fetchAll()]);

        return $matches;
    }

    /**
     * Fetches all of the tournaments in the system
     *
     * @param bool|false $deleted Show deleted tournaments too
     *
     * @return Entity An entity containing all of the tournaments in the system
     */
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

    /**
     * Fetches all of the teams in a tournament
     *
     * @return Entity An entity containing all of hte teams registered for a specific tournament
     */
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

    /**
     * Determines the standings for the tournament
     *
     * @return Entity An entity containing the teams standings within the tournament
     */
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

    /**
     * Calculates the roundrobin standings
     *
     * @return array An array containing teh standings for the round robin matches
     */
    public function calcRRStats() {
        $dbh = Database::getInstance();
        $teams = $this->getTeams()->Teams;
        $standings = [];

        /**
         * I go over each team in the tournament and calculate their matches won and matches played
         * this information is then added to an array which is sorted later, PHP side
         *
         * TODO: I took the lazy, slower approach by loading it team by team. Ideally all of this work would be done on the database server which is optimized for this
         */
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
                    AND Status = 2
                    ORDER BY Round DESC";
            $sth = $dbh->prepare($sql);
            $sth->execute([$team['Team_ID'], $team['Team_ID'], $this->Tournament_ID]);
            $data->addToData(['Matches_Played' => $sth->fetch()['Matches_Played']]);
            array_push($standings, $data);
        }

        //TODO: This would be faster if it was one query and sorted on the DB server
        //Sort the array based on the number of matches won
        usort($standings, function ($a, $b) {
            return $b->Matches_Won - $a->Matches_Won;
        });
        return $standings;
    }

    /**
     * Calculates the knockout standings for the tournament
     *
     * @return array The standings for the knockout tournament
     */
    public function calcKOStats() {
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