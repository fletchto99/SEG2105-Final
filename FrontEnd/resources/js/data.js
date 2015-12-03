/**
 *  Author(s)           :  Kurt Bruneau, Matt Langlois
 *  File Created        :  May 2014
 *  File Updated        :  December 2015
 *  Details             :  Functions in this file handle ajax data requests
 *
 */

$(function () {
    'use strict';

    // Create a namespace for ajax data related functions
    Keeper.data = {};

    /**
     * A generic function for making JSON requests
     *
     * @param {String} url The url to make a request from
     * @param {Array} data An array of parameters to pass in the request
     * @param {Array} headers An array of headers to set on the request
     * @returns {Deferred} A deferred object that triggers after a response
     */
    Keeper.data.request = function (url, data, headers) {
        return $.ajax({
            url: url,
            type: 'POST',
            contentType: 'application/json',
            dataType: 'text',
            data: JSON.stringify(data),
            beforeSend: function (xhr) {
                if (headers !== undefined) {
                    headers.forEach(function (header) {
                        if (header !== undefined && header.type && header.value) {
                            xhr.setRequestHeader (header.type, header.value);
                        }
                    });
                }
            }
        }).then(function (data) {
            if (!data) {
                return;
            }
            try {
                return JSON.parse(data);
            } catch (e) {
                console.log('Warning: Server did not give a JSON response');
                return data;
            }
        }, function (jqXHR) {
            try {
                return (JSON.parse(jqXHR.responseText)).error;
            } catch (e) {
                console.log('Warning: Server did not give a JSON response');
                return jqXHR.responseText;
            }
        });
    };

    /**
     * Function to receive data from server using ajax POST
     * Use like this:
     * Keeper.data.get('users', post_parameters_object, hideLoadScreen).done(callback);
     *
     * @param {String} entity_name The name of the controller to lookup data on
     * @param {Array} parameters An array of parameters to pass to the controller
     * @returns {Deferred} Called once the controller returns some information
     */
    Keeper.data.get = function (entity_name, parameters) {

        if (!parameters) {
            parameters = {};
        }

        var url = ['../backend/controllers/get/', entity_name, '.php'].join('');
        return Keeper.data.request(url, parameters);
    };

    // Queue updates and deletes to prevent strange race conditions
    var _requestQueue = [];

    /**
     * Calls an update controller
     *
     * @param {String} entity_name The controller's name
     * @param {String} parameters The parameters to pass to the controller
     * @returns {{go: Function, done: Function}} Called once the controller returns some information
     */
    Keeper.data.update = function (entity_name, parameters) {
        if (!parameters) {
            parameters = {};
        }

        // The URL to POST
        var url = ['../backend/controllers/update/', entity_name, '.php'].join('');

        // This will run when the request has completed
        var on_complete_callback = null;
        var doComplete = function (data) {
            // Call the .done()
            if (on_complete_callback !== null) {
                on_complete_callback(data);
            }
            // Execute the next request in the queue
            if (_requestQueue.length) {
                _requestQueue.shift().go();
            }
        };

        // Return an object for method chaining
        var promise = {
            go: function () {
                // Remove this from the queue
                var index = _requestQueue.indexOf(promise);
                if (index !== -1) {
                    _requestQueue.splice(index, 1);
                }

                Keeper.data.request(url, parameters).then(doComplete);
                return promise;
            }, done: function (callback) {
                on_complete_callback = callback;
            }
        };

        // Add this one to the queue
        _requestQueue.push(promise);
        if (_requestQueue.length === 1) {
            _requestQueue[0].go();
        }

        return promise;
    };

    /**
     * Calls a delete controller
     *
     * @param {String} entity_name The controller's name
     * @param {String} parameters The parameters to pass to the controller
     * @returns {{go: Function, done: Function}} Called upon successful removal
     */
    Keeper.data.remove = function (entity_name, parameters) {

        if (!parameters) {
            parameters = {};
        }

        // The URL to POST
        var url = ['../backend/controllers/remove/', entity_name, '.php'].join('');

        // This will run when the request has completed
        var on_complete_callback = null;
        var doComplete = function (data) {
            // Call the .done()
            if (on_complete_callback !== null) {
                on_complete_callback(data);
            }
            // Execute the next request in the queue
            if (_requestQueue.length) {
                _requestQueue.shift().go();
            }
        };

        // Return an object for method chaining
        var promise = {
            go: function () {

                Keeper.data.request(url, parameters).then(doComplete);
                return promise;
            }, done: function (callback) {
                on_complete_callback = callback;
            }
        };

        // Add this one to the queue
        _requestQueue.push(promise);
        if (_requestQueue.length === 1) {
            _requestQueue.shift().go();
        }

        return promise;
    };

    /**
     * Authenticates the user with the server and generates their session
     *
     * @returns {{done: Function}} Called once the user has been successfully authenticated with the system
     */
    Keeper.data.login = function (username, password) {
        var headers = [{
            type: 'Authorization',
            value: "Basic " + btoa(username + ":" + password)
        }];
        return Keeper.data.request('../backend/controllers/validate-session.php', {}, headers);
    };

    Keeper.data.isAuthenticated = function () {
        return Keeper.data.request('../backend/controllers/validate-session.php');
    };

    Keeper.data.logout = function () {
        return Keeper.data.request('../backend/controllers/logout.php');
    }

});