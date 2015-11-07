<?php
/**
 * Takes a matchID
 *
 * Update's sets the match as over.
 *
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Match_ID']);
$match = Match::getMatch($input->Match_ID);
$match->end();