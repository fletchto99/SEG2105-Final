<?php
/**
 * Takes a Team_Name
 * [optional] Takes a Captain_ID and assigns them as the team captain
 * [optional] Takes a Team_Avatar (int id) and sets it as the teams avatar
 *
 * Creates a team with the name, and sets the avatar to the image specified or a default image.
 * Assigns the creator as the Coach/captain of the team
 *
 * Returns the team's information
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();
$team = new Team();
$team->fromJSON();
$team->checkKeys(['Team_Name']);
$result = $team->create();
echo $result->toJSON();