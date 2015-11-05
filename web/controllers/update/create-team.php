<?php
/**
 * Takes a team name
 * [optional] Takes a team avatar
 *
 * Creates a team with the name, and sets the avatar to the image specified or a default image.
 * Assigns the creator as the Coach/captian of the team
 *
 * Returns the team's information
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();