<?php
/**
 *  Author(s)   :  Matt Langlois
 *  Details     :  This file defines system constants and sets up the application
 *                 This file is automatically included in all other files
 *                 within this directory and within sub directories.
 */


/* Application parameters */
define('APP_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

/* Add APP_ROOT to include path */
set_include_path(get_include_path() . PATH_SEPARATOR . APP_ROOT);

require_once 'configuration.php';
require_once 'entities/Person.php';

/**
 * Sends an error message to the client
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
 * Ensure the user has been authorized by the system
 */
function Authenticate() {
    Person::user();
}

function fatalHandler() {
    $error = error_get_last();
    if ($error["type"] == E_ERROR) {
        ApplicationError('Internal', $error["message"] .
            "\n\nFrom: " . $error['file'] . ', line: ' . $error["line"]);
    }
}

function exceptionHandler(Exception $error) {
    ApplicationError('Internal', get_class($error) . ': ' . $error->getMessage() .
        "\n\nFrom: " . $error->getFile() . ', line: ' . $error->getLine());
}

function errorHandler($num, $str, $file, $line) {
    exceptionHandler(new ErrorException($str, 0, $num, $file, $line));
}

register_shutdown_function("fatalHandler");
set_error_handler("errorHandler");
set_exception_handler("exceptionHandler");

error_reporting(E_ALL);
ini_set("display_errors", "off");