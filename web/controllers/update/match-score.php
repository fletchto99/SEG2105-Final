<?php
/*
 * Takes match ID
 * Takes player ID
 *
 * Uses player id to determine team and then adds a goal to that player
 * for the specific tournament that the match is in (only if the match is in progress)
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Player_ID', 'Match_ID']);
$match = Match::getMatch($input->Match_ID);
$player = Person::getPerson($input->Player_ID);
$assistPlayer = isset($input->Assist_ID) ? Person::getPerson($input->Assist_ID) : null;
$match->addGoal($player, $assistPlayer);