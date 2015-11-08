<?php
/**
 * Takes a teamID
 *
 * Returns all of the player's in the team
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID']);
$team = Team::getTeam($input->Team_ID);
$result = $team->getPlayers();
echo $result->toJSON();