<?php
/**
 * Takes a tournament name
 * Takes a tournament description
 *
 * Adds the tournament to the database and sets it to the planning phase
 *
 * returns the tournament entity
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();