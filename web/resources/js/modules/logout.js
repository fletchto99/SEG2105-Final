/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#logout
 *  Details             :  Module to logout
 */

Keeper.createModule(function (Keeper) {
    'use strict';

    /**
     * The properties of the logout module
     */
    var Module = {
        id: 'logout', // Appears in address bar. Used in Links.
        title: 'Logout', // Used in title
        visible_in_nav_bar: true,
        navbar_visible: true
    };

    /**
     * Called when the module is removed
     */
    Module.unload = function () {
        Keeper.showAlert('Successfully logged out.', 'success', 10000);
    };

    /**
     * The main display function of the module, called when the module is loaded by the system
     *
     * @param ContentPane The content pane for element to be drawn in
     * @param parameters The parameters passed to the module
     * @returns {boolean} True if the module was displayed successfully; otherwise false
     */
    Module.display = function (ContentPane, parameters) {

        //call the logout controller
        Keeper.data.logout().done(function () {
            Keeper.loadModule('welcome');
        }).fail(function (error) {
            Keeper.loadModule('welcome');
        });
        return true;
    };

    return Module;
});