/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#matches
 *  Details             :  Module to display matches and match info
 */

Keeper.createModule(function (Keeper) {
    'use strict';

    /**
     * The properties of the admin activity sub-module
     *
     * @type {{id: string, name: string, title: string, icon: string, css: string, alt_key_shortcut: string,
     *     visible_in_nav_bar, show_in_nav_bar: string, update_interval: number}}
     */
    var Module = {
        id: 'matches', // Appears in address bar. Used in Links.
        title: 'Matches', // Used in title
        visible_in_nav_bar: false,
        navbar_visible: true,
        css: 'matches.css'
    };

    /**
     * Called when the module is removed
     */
    Module.unload = function () {

    };

    /**
     * The main display function of the module, called when the module is loaded by the system
     *
     * @param ContentPane The content pane for element to be drawn in
     * @param parameters The parameters passed to the module
     * @returns {boolean} True if the module was displayed successfully; otherwise false
     */
    Module.display = function (ContentPane, parameters) {
        if (!Keeper.isLoggedIn()) {
            return false;
        }

        if (!parameters[0]) {
            Keeper.showAlert('A tournament is required to show matches', 'danger');
            return false;
        }
        return true;
    };

    return Module;
});