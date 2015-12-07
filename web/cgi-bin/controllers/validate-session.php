<?php
/**
 * Check/resumes the current user's session, can be used to determine if a session is already active to prevent having to login again
 */

require_once __DIR__ . '/../bootstrap.php';

echo Person::user()->toJSON();