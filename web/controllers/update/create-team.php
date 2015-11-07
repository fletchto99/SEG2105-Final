<?php
/**
 * Takes a team name
 * [optional] Takes a captain ID and assigns them as the team captain
 * [optional] Takes a team avatar image path
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