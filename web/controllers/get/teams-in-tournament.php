<?php
/**
 * Takes a Tournament_ID
 *
 * Returns all of the teams who have entered the tournament
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
$teams = Tournament::getTournament($input->Tournament_ID)->getTeams();
echo $teams->toJSON();