<?php
/**
 * Takes a Username
 * Takes a Password
 * Takes a First_Name
 * Takes a Last_Name
 * [Optional] Takes a Jersey_Number
 *
 * Inserts a user into the database and opens a session for them
 *
 * returns the user's information
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once 'entities/Person.php';
$user = new Person();
$user->fromJSON();
$user->checkKeys(['Username', 'Password', 'First_Name', 'Last_Name']);
$result = $user->create();
echo $result->toJSON();