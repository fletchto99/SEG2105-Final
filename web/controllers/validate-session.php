<?php
/**
 * Check/resumes the current user's session
 */

require_once __DIR__ . '/../bootstrap.php';

echo Person::user()->toJSON();