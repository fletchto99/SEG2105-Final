<?php
/**
 * Takes: Tournament id
 *
 * Checks if the current user is an organizer and then deletes the tournament (Only if it is in the planning phase!)
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();