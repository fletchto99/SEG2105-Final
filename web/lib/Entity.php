<?php
/**
 *  Details     :  A class to manage database interactions and data I/O
 *  Author(s)   :  Will Thompson, Kurt Bruneau, Matt Langlois
 *
 */

require_once __DIR__ . "/../bootstrap.php";
require_once 'Database.php';

class Entity implements JsonSerializable {

    /*
     * An associative array containing all of the data of the entity
     */
    protected $data;

    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Populate data from JSON
     *
     * @return $this The entity populated from the JSON data
     */
    public function fromJSON() {
        $isJSONRequest = strpos($_SERVER["CONTENT_TYPE"], 'application/json') !== false;
        if (!$isJSONRequest) {
            ApplicationError('Bad Request', 'Request is not a JSON request');
        }

        $this->data = json_decode(file_get_contents('php://input'), true); // True is so we get it as an array instead of as stdClass
        return $this;
    }

    /**
     * Serialize entity as JSON
     *
     * @return string The entity in a web safe serialized string
     */
    public function toJSON() {
        return json_encode($this->data);
    }

    /**
     * Ensures the entity contains all of the required fields.
     *
     * @param array $expected_keys The required keys for the entity
     */
    public function checkKeys($expected_keys) {
        $error_keys = [];

        foreach ($expected_keys as $key) {
            if (!isset($this->data[$key])) {
                $error_keys[] = $key;
            }
        }

        if ($error_keys) {
            ApplicationError("query", "Expected: " . implode($error_keys, ', '));
        }
    }

    /**
     * Calculates the size of the entity
     *
     * @return int The amount of fields within the entity
     */
    function count() {
        return count($this->data);
    }

    /**
     * Fetches an iterable set created by the data of the entity
     *
     * @return array The data contained within the entity
     */
    function each() {
        return $this->data;
    }

    /**
     * Adds data to an entity
     *
     * @param array $row The row to add to the entity
     * @return $this|null The entity
     */
    function addToData($row) {
        if (!isset($row)) {
            return null;
        }
        foreach ($row as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /*
     * Set a value in the entity
     */
    public function __set($key, $value) {
        $this->data[$key] = $value;
    }

    /*
     * Get a value in the entity
     */
    public function __get($key) {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        $trace = debug_backtrace();
        ApplicationError("entity",
            "Attempted to access undefined member '" . $key .
            "' in '" . $trace[0]['file'] . "' on line " . $trace[0]['line']
        );

        return null;
    }

    /*
     * Checks if a key contains a value within the entity
     */
    public function __isset($key) {
        if ($this->data === null) {
            return false;
        }
        return array_key_exists($key, $this->data) ? isset($this->data[$key]) : false;
    }

    /**
     * Returns the data of the entity
     *
     * @return array The entity's data
     */
    public function jsonSerialize() {
        return $this->data;
    }
}