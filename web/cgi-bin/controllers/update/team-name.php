<?php
/**
 * Takes a Team_ID
 * Takes a Team_Name
 *
 * update the team name
 *
 * returns the new team entity representing the team
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID', 'Team_Name']);
$team = Team::getTeam($input->Team_ID);
$result = $team->updateName($input->Team_Name);
echo $result->toJSON();