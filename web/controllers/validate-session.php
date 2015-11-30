<?php
/**
 * Check/resumes the current user's session
 */

require_once __DIR__ . '/../bootstrap.php';

$person = Person::user();
echo $person->toJSON();