<?php
/**
 * Takes a Person ID
 *
 * Returns all of the player information including their total goals scored, tournaments participated in and their team
 */

require_once __DIR__ . '/../../bootstrap.php';

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Player_ID']);
$person = Person::getPerson($input->Player_ID);
$result = $person->statistics();
echo $result->toJSON();