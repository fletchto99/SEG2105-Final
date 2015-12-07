<?php
/**
 * No required parameters
 * [optional] Takes a deleted parameter, if true it will include deleted tournaments
 *
 * Returns the id's of all of the tournaments (open, current, and past)
 */
require_once __DIR__ . '/../../bootstrap.php';
$input = new Entity();
$input->fromJSON();
if (isset($input->Deleted)) {
    echo Tournament::getTournaments(true)->toJSON();
} else {
    echo Tournament::getTournaments()->toJSON();
}
