<?php
/**
 * Takes a tournament id
 *
 * Returns the information of the tournament
 */
require_once __DIR__ . '/../../bootstrap.php';
$input = new Entity();
$input->fromJSON();
$input->checkKeys(['Tournament_ID']);
echo Tournament::getTournament($input->Tournament_ID)->toJSON();