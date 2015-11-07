<?php
/**
 * Takes a tournament ID
 *
 * Returns the matches to occur for the specific tournament
 */

require_once __DIR__ . '/../../bootstrap.php';
$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
$tournament = Tournament::getTournament($input->Tournament_ID);
$results = $tournament->getMatches();
echo $results->toJSON();