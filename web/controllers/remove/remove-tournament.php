<?php
/**
 * Takes: Tournament_ID
 *
 * Checks if the current user is an organizer and then deletes the tournament (Only if it is in the planning phase!)
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();
$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
$tournament = Tournament::getTournament($input->Tournament_ID);
$tournament->delete();