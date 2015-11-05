<?php
/**
 * Takes a tournament ID
 * Takes a tournament type (knockout [k], roundrobin[rr], knockout-roundrobin[krr]
 *
 * Sets that tournament to active and generates a list of matches that are to be played
 * for the chosen tournament type
 *
 * returns the list of matches to be played
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();