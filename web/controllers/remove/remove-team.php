<?php
/**
 * Takes a team id
 *
 * Checks if the current user is the coach or an organizer, if so it removes all players from the team (making them a free agent) and marks the team as deleted
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();