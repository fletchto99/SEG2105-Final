<?php
/**
 * Logs the current user out
 */

require_once __DIR__ . '/../bootstrap.php';

if (!logout()) {
    ApplicationError('Logout', 'You are not logged in!');
}

$result = new Entity(['success'=>'You have been logged out successfully!']);
echo $result->toJSON();