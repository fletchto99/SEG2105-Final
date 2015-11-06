<?php
/**
 * Takes a tournament ID
 * Takes a team ID
 *
 * Checks if the tournament is in the planning phase (i.e. not started) and adds that team to the tournament roster
 *
 * returns some form of a success message
 */


require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->$this->fromJSON();
$input->checkKeys(['Tournament_ID', 'Team_ID']);
$tournament = new Tournament($input->Tournamnet_ID);
$results = $tournament->addTeam(Team::getTeamInfo($input->Team_ID));
echo $results -> toJSON();