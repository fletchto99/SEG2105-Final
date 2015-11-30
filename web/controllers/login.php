<?php
/**
 * Validates login using basic authentication
 */

require_once __DIR__ . '/../bootstrap.php';

logout(); //Destroy any old sessions
Authenticate();
$person = Person::user();
echo $person->toJSON();