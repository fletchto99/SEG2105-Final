<?php
/**
 * [Optional] No_Team_assigned
 *
 * Returns the status of the match, the team ID's of the team's participating, and the scores
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();
$input = new Entity();
$input->fromJSON();
if (isset($input -> No_Team_Assigned)) {
    $result = Person::getOTFPlayers($input -> No_Team_Assigned);
} else {
    $result = Person::getOTFPlayers();
}
echo $result->toJSON();