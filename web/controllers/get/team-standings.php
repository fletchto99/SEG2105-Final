<?php
/**
 * Takes a Team_ID
 * [optional] Takes a Tournament_ID
 *
 * returns all of the players, in order of goals scored along with the amount of goals they have scored.
 * In the case of a tournamentID being specified the goals scored is limited to the tournament
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID']);
if (isset($input->Tournament_ID)) {
    $tournament = Tournament::getTournament($input->Tournament_ID);
    echo Team::getTeam($input->Team_ID)->getRankings($tournament);
} else {
    echo Team::getTeam($input->Team_ID)->getRankings();
}
