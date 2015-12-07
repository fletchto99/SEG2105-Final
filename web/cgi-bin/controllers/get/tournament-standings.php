<?php
/**
 * Takes a Tournament_ID
 *
 * !!!!Based on tournament type!!!!
 * Returns tournament type plus one of the following cases
 *
 * K:
 * returns all of the remaining teams, indicating which team's they've knocked out
 *
 * RR:
 * returns all of the teams, in order of matches won
 *
 * KRR:
 * Need to figure out what to return here....
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
$standings =  Tournament::getTournament($input->Tournament_ID)->getStandings();
echo $standings->toJSON();