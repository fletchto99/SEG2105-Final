<?php
/**
 * Check/resumes the current user's session
 */

require_once __DIR__ . '/../bootstrap.php';

Authenticate();

$result = new Entity(['success'=>'You have been logged in successfully!']);
echo $result->toJSON();