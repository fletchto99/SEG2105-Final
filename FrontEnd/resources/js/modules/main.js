/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#main
 *  Details             :  Module to display welcome screen
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
        id: 'main', // Appears in address bar. Used in Links.
        title: 'Tournament Maker', // Used in title
        visible_in_nav_bar: false,
        navbar_visible: true,
        css: 'main.css'
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

        return true;
    };

    return Module;
});