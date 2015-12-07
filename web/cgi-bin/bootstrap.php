<?php
/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This file defines system constants and sets up the application
 *                 This file is automatically included in all other files
 *                 within this directory and within sub directories.
 */


/*
 *Application parameters
 */
define('APP_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

/*
 * Add APP_ROOT to include path to maintain consistency across systems
 */
set_include_path(get_include_path() . PATH_SEPARATOR . APP_ROOT);

require_once 'configuration.php';
require_once 'entities/Person.php';
require_once 'entities/Match.php';
require_once 'entities/Team.php';
require_once 'entities/Tournament.php';
require_once 'lib/Utils.php';

/**
 * Sends an error message to the client and prevents further execution
 *
 * @param String $type The type/title of the error
 * @param String $message The user friendly error message
 * @param Integer $responseCode The HTTP response code to send back
 */
function ApplicationError($type, $message, $responseCode = 500) {
    http_response_code($responseCode);
    echo json_encode(['error' => [
        'type'    => $type,
        'message' => $message
    ]]);

    exit();
}

/**
 * Ensure the user has been authorized by the system by validating their account
 */
function Authenticate() {
    Person::user();
}

/**
 * Kills a user session
 *
 * @return bool True if the user was logged out successfully; otherwise false
 */
function logout() {
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_unset();
        return session_destroy();
    }
    return false;
}

/**
 * Redirect all fatal errors through our error handler, easier for debugging server side issues through the client side (for development)
 */
function fatalHandler() {
    $error = error_get_last();
    if ($error["type"] == E_ERROR) {
        ApplicationError('Internal', $error["message"]);
    }
}

/**
 * Redirect all exceptions through our exception handler, easier for debugging server side issues through the client side (for development)
 */
function exceptionHandler(Exception $error) {
    ApplicationError('Internal', get_class($error) . ': ' . $error->getMessage());
}

/**
 * Redirect all errors through our error handler, easier for debugging server side issues through the client side (for development)
 */
function errorHandler($num, $str, $file, $line) {
    exceptionHandler(new ErrorException($str, 0, $num, $file, $line));
}

//Register our handlers created above
register_shutdown_function("fatalHandler");
set_error_handler("errorHandler");
set_exception_handler("exceptionHandler");

//Report all errors
error_reporting(E_ALL);

//Prevent errors from being echoed on the secreen
ini_set("display_errors", "off");