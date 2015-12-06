<?php

require_once 'bootstrap.php';
require_once 'lib/Database.php';
require_once 'lib/Entity.php';

/**
 *  Details     :  A class to manage Match entities
 *  Author(s)   :   Matt Langlois
 *
 */
class Match extends Entity {

    /**
     * Populates the entity with the information from the database. Requires the entity's ID to be
     * defined before the information can be populated
     */
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

    /**
     * Populates a Match entity with the information regarding a specific match
     *
     * @param int $matchID The ID of the match to lookup
     * @return Match A populated match entity
     */
    public static function getMatch($matchID) {
        if (!isset($matchID)) {
            ApplicationError('Match', 'Match_ID is not defined!');
        }

        //create an instance of an entity to populate
        $match = new Match();
        $match->Match_ID = $matchID;
        $match->populate();
        return $match;
    }

    /**
     * Creates a match and stores it in the database
     *
     * @param int $tournamentID The ID of the parent tournament
     * @param null|int $nextMatchID The ID of the next match, if any. Used in KO tournament style
     * @param null|int $round The round which the match exists in.
     * @param null|int $teamAID The id of the first competing team
     * @param null|int $teamBID The id of the second competing team
     *
     * @return Match The newly created match entity
     */
    public static function create($tournamentID, $nextMatchID = null, $round = null, $teamAID = null, $teamBID = null) {
        $user = Person::user();

        //Validate user role
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to create a match!", 403);
        }

        //Ensure the tournamentID is a number
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

    /**
     * Adds a goal to a match
     *
     * @param Person $player The player who scored the goal
     * @param null|Person $assister The player who assisted in scoring the goal, null for no assister
     */
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

        if (isset($assister) && $player->Person_ID == $assister->Person_ID) {
            ApplicationError("Goal", "The assister can't also be the goal scorer!");
        }

        $sql = "INSERT INTO Goals(Match_ID, Player_ID, Assist_ID, Team_ID)
                VALUES (?,?,?,?)";
        $dbh = Database::getInstance();
        $sth = $dbh->prepare($sql);

        $assist_ID = $assister != null ? $assister->Person_ID : null;
        $sth->execute([$this->Match_ID, $player->Person_ID, $assist_ID, $player->Team_ID]);
    }

    /**
     * Marks a match as under way.
     *
     * @return Entity The result of starting the match, as an entity so it may be converted to JSON
     */
    public function begin() {
        $user = Person::user();
        if (!$user->hasRole('Organizer')) {
            ApplicationError("Match", "You must be a tournament organizer to begin a match!", 403);
        }

        //Retrieve the parent tournament
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

        //Validate both teams previous games are over (for the specific tournament)
        $sql = "SELECT Count(*) as active
                FROM Matches
                WHERE Status = 1
                AND Tournament_ID = ?
                AND (Team_A_ID = ? OR Team_B_ID = ?)";
        $sth = $dbh->prepare($sql);

        //Determine if Team_A is busy
        $sth->execute([$this->Tournament_ID, $this->Team_A_ID, $this->Team_A_ID]);
        $teamAActive = intval($sth->fetch()['active']) > 0;

        //Determine if Team_B is busy
        $sth->execute([$this->Tournament_ID, $this->Team_B_ID, $this->Team_B_ID]);
        $teamBActive = intval($sth->fetch()['active']) > 0;

        //Check both teams
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

    /**
     * Marks a match as over. If the match was the last roundrobin one of a Knockout Roundrobin tournament, then the knockout phase is populated
     * with the top teams of the Round Robin phase. Note: A logical flaw exists here, if all of the teams tied in the round robin stage then only the first 2^n
     * teams will be taken instead of determining who should actually move on.
     *
     * @return Entity A success message
     */
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

        //Determine if the match is a tie. One team must win to advance
        $sth->execute([$this->Match_ID, $this->Team_A_ID]);
        $goals_A = $sth->fetch();
        $sth->execute([$this->Match_ID, $this->Team_B_ID]);
        $goals_B = $sth->fetch();

        if ($goals_A['count'] == $goals_B['count']) {
            ApplicationError('Match', "The match can't end in a tie! This is a tournament, one team must win!");
        }

        //Determine the team with the most goals and set them as the winnder
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

            //Determine the amount of Roundrobin matches which have not yet finished, if 0 then we can move on to knockout stage
            $sql = "SELECT COUNT(*) as count
                    FROM Matches
                    WHERE Tournament_ID=?
                        AND Round IS NULL
                        AND Status <> 2";
            $sth = $dbh->prepare($sql);
            $sth->execute([$tournament->Tournament_ID]);
            $results = $sth->fetch();

            //Determine if the knockout matches have been populated, if not then we must populate them
            $sql = "SELECT COUNT(*) as count
                    FROM Matches
                    WHERE Tournament_ID=?
                        AND Round=1
                        AND Team_A_ID is null
                        AND Team_B_ID is null";
            $sth = $dbh->prepare($sql);
            $sth->execute([$tournament->Tournament_ID]);
            $results2 = $sth->fetch();

            //Check our results from above
            if (intval($results['count']) < 1 && intval($results2['count']) > 0) {

                //Determine the top teams of the round robin phase
                $rrStandings = $tournament->calcRRStats();

                //Retrieve the ID's of the first round of the knockout phase
                $sql = "SELECT Match_ID
                        FROM Matches
                        WHERE Round=1
                            AND Tournament_ID=?";
                $sth = $dbh->prepare($sql);
                $sth->execute([$tournament->Tournament_ID]);
                $resultSet = $sth->fetchAll();

                $offset = 0;
                /*
                 * For each ID in round one of the knockout phase, set the Team_A and Team_B to be a team from the
                 * Round robin phase, in the order they won. TODO: This is where the logic flaw exists, if every team
                 * in the roundrobin phase tiead then only the 2^n teams will be taken to move on
                 */
                foreach($resultSet as $result) {
                    $sql = "UPDATE Matches
                            SET Team_A_ID = ?,
                                Team_B_ID = ?
                            WHERE Match_ID = ?";
                    $sth = $dbh->prepare($sql);
                    $sth->execute([$rrStandings[$offset]->Team_ID,$rrStandings[$offset+1]->Team_ID,$result['Match_ID']]);
                    $offset+= 2;
                }
            }
        }

        return new Entity(['success' => 'Match ended!']);
    }

    /**
     * @return Match $this Populates the match entity with all of the scores
     */
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