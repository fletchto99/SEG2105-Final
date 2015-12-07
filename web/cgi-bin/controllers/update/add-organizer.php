<?php
/**
 * Takes a Person_ID
 *
 * Sets the player's role to organizer
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Person_ID']);
$user = Person::user();
$result = $user->setAsOrganizer($input->Person_ID);
echo $result->toJSON();