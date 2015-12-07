<?php
/**
 * Takes a Tournament_ID
 * Takes a Team_ID
 *
 * Checks if the tournament is in the planning phase (i.e. not started) and adds that team to the tournament roster
 *
 * returns some form of a success message
 */


require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID', 'Team_ID']);
$tournament = Tournament::getTournament($input->Tournament_ID);
$team = Team::getTeam($input->Team_ID);
$results = $tournament->addTeam($team);
echo $results->toJSON();