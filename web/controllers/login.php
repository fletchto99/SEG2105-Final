<?php
/**
 * Validates login using basic authentication
 */

require_once __DIR__ . '/../bootstrap.php';

Person::user();

$result = new Entity(['success'=>'You have been logged in successfully!']);
echo $result->toJSON();