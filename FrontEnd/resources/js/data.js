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

        var url = [Keeper.ROOT_URL, 'get/', entity_name, '.php'].join('');
        return Keeper.data.request(url, parameters);
    };

    /**
     * Calls an update controller
     *
     * @param {String} entity_name The controller's name
     * @param {String} parameters The parameters to pass to the controller
     * @returns {Deferred} Called once the controller returns some information
     */
    Keeper.data.update = function (entity_name, parameters) {
        if (!parameters) {
            parameters = {};
        }

        var url = [Keeper.ROOT_URL, 'update/', entity_name, '.php'].join('');
        return Keeper.data.request(url, parameters);
    };

    /**
     * Calls a delete controller
     *
     * @param {String} entity_name The controller's name
     * @param {String} parameters The parameters to pass to the controller
     * @returns {Deferred} Called once the controller returns some information
     */
    Keeper.data.remove = function (entity_name, parameters) {
        if (!parameters) {
            parameters = {};
        }

        var url = [Keeper.ROOT_URL, 'remove/', entity_name, '.php'].join('');
        return Keeper.data.request(url, parameters);
    };

    /**
     * Authenticates the user with the server and generates their session
     *
     * @returns {Deferred} Called once the controller returns some information
     */
    Keeper.data.login = function (username, password) {
        var headers = [{
            type: 'Authorization',
            value: "Basic " + btoa(username + ":" + password)
        }];
        return Keeper.data.request(Keeper.ROOT_URL + '/validate-session.php', {}, headers);
    };

    /**
     * Determines if the user is authenticated with the server, and returns their session if they are
     *
     * @returns {Deferred} Called once the controller returns some information (authenticated/error)
     */
    Keeper.data.isAuthenticated = function () {
        return Keeper.data.request(Keeper.ROOT_URL + '/validate-session.php');
    };

    /**
     * Clears the user's session with the server
     *
     * @returns {Deferred} Called once the controller returns some information
     */
    Keeper.data.logout = function () {
        return Keeper.data.request(Keeper.ROOT_URL + '/logout.php');
    }

});