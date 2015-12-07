<?php
/**
 * Takes a Person_Avatar (int id)
 *
 * Returns a success message
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Person_Avatar']);
$person = Person::user();
$result = $person->updateAvatar($input->Person_Avatar);
echo $result->toJSON();