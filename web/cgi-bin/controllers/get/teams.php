<?php
/**
 * Takes no parameters
 *
 * Returns all of the teams in the system
 */

require_once __DIR__ . '/../../bootstrap.php';

echo Team::getTeams()->toJSON();