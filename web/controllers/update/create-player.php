<?php
/*
 *
 * Inserts a player into the database
 *
 * returns the player's information
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once 'entities/Person.php';
$user = new Person();
$user->fromJSON();
$user->checkKeys(['First_Name', 'Last_Name', 'Jersey_Number']);
$result = $user->create(true);
echo $result->toJSON();