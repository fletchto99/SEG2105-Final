<?php
/**
 * Takes a match ID
 *
 * Returns the status of the match, the team ID's of the team's participating, and the scores
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Match_ID']);
$match = Match::getMatch($input->Match_ID);
echo $match->withScores();