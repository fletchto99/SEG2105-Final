<?php
/**
 * Takes a Tournament_ID
 *
 * Update's sets the tournament as over.
 *
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
$tournament = Tournament::getTournament($input->Tournament_ID);
$result = $tournament->end();
echo $result->toJSON();