<?php
/**
 * [optional] Takes a person_ID
 * Takes a  number
 *
 * updates the players number
 *
 * returns the new player entity
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Jersey_Number']);
$user = Person::user();
$user->updateJerseyNumber($input->Jersey_Number);