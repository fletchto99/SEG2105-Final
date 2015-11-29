<?php
/**
 * Takes a Team_ID
 *
 * Returns the team's information (logo, name, coachID)
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID']);
echo Team::getTeam($input->Team_ID)->toJSON();