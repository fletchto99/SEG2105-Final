<?php
/*
 * Takes match ID
 * Takes player ID
 *
 * Uses player id to determine team and then adds a goal to that player
 * for the specific tournament that the match is in (only if the match is in progress)
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();