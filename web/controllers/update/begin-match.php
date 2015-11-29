<?php
/**
 * Takes a Match_ID
 *
 * Update's sets the match as in-progress if it has not started, or finished.
 *
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();
$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Match_ID']);
$match = Match::getMatch($input->Match_ID);
$match->begin();