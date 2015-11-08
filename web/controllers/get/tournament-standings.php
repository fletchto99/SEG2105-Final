<?php
/**
 * Takes a tournamentID
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