<?php
/**
 * Logs the current user out
 */

require_once __DIR__ . '/../bootstrap.php';

session_start(); //resume any session that may have been active, or create a new one. Just to ensure the session is killed
if (!logout()) {
    ApplicationError('Logout', 'You are not logged in!');
}

$result = new Entity(['success'=>'You have been logged out successfully!']);
echo $result->toJSON();