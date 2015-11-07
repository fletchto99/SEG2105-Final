<?php
/**
 * Takes a Team ID
 *
 * Adds that player to the team roster
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID']);
$person = Person::user();
$team = Team::getTeam($input->Team_ID);
$person->joinTeam($team);