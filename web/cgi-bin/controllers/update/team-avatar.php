<?php
/**
 * Takes a Team_Avatar (integer)
 * Takes a Team_ID
 *
 * Saves the file to the logo directory
 *
 * Returns the link to access the file
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID', 'Team_Avatar']);
$team = Team::getTeam($input->Team_ID);
$result = $team->updateAvatar($input->Team_Avatar);
echo $result->toJSON();