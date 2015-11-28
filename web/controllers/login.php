<?php
/**
 * Validates login using basic authentication
 */

require_once __DIR__ . '/../bootstrap.php';

logout(); //Destroy any old sessions
Authenticate();

$result = new Entity(['success'=>'You have been logged in successfully!']);
echo $result->toJSON();