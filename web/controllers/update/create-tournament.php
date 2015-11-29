<?php
/**
 * Takes a Tournament_Name
 * Takes a Tournament_Type (id, 0[K], 1[RR], 2[KRR])
 *
 * Adds the tournament to the database and sets it to the planning phase
 *
 * returns the tournament entity
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$tournament = new Tournament();
$tournament->fromJSON();
$tournament->checkKeys(['Tournament_Name']);
$result = $tournament->create();
echo $result->toJSON();