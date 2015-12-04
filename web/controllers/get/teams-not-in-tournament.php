<?php
/**
 * Takes a Tournament_ID
 *
 * Returns all of the teams that are not registered in that tournament
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
$tournament = Tournament::getTournament($input->Tournament_ID);
echo Team::getTeamsNotInTournament($tournament)->toJSON();