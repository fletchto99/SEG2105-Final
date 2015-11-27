<?php
/**
 * Logs the current user out
 */

require_once __DIR__ . '/../bootstrap.php';

if (session_status() == PHP_SESSION_NONE) {
    ApplicationError('Logout', 'You are not logged in!');
}
session_unset();
session_destroy();

$result = new Entity(['success'=>'You have been logged out successfully!']);
echo $result->toJSON();