<?php
/**
 * Takes a Team ID
 * [Optional] Takes a player ID
 *
 * Adds that player to the team roster
 */

require_once __DIR__ . '/../../bootstrap.php';
authenticate();

$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Team_ID']);
$person = Person::user();
$team = Team::getTeam($input->Team_ID);
if (isset($input->Player_ID)) {
    if (!$person->hasRole('Organizer')) {
        ApplicationError("Permissions", "You must be an organizer to add someone else to a team!");
    }
    $target = Person::getPerson($input->Player_ID);
    $result = $target->joinTeam($team);
} else {
    $result = $person->joinTeam($team);
}
echo $result->toJSON();