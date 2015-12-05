/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#help
 *  Details             :  Module to logout
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
        id: 'hep', // Appears in address bar. Used in Links.
        title: 'Help', // Used in title
        visible_in_nav_bar: true,
        navbar_visible: true,
        css: 'help.css'
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

        if (Keeper.hasRole('Player')) {

            createElement({
                elem: 'h1',
                textContent: 'Tournament Maker Help',
                putIn: ContentPane
            });
            return true;

            createElement({
                elem: 'p',
                textContent: 'Welcome to Keeper, your personalized Tournament Maker!'
                putIn: ContentPane
            });

             createElement({
                elem: 'h3',
                textContent: 'Create User'
                putIn: ContentPane
            });

              createElement({
                elem: 'p',
                textContent: 'If this is your first visit to this app, you need to create a user! 
                You will need to enter you first name, last name, username, and password. You will then be prompted to enter a jersey number.'
                putIn: ContentPane
            });

              createElement({
                elem: 'h3',
                textContent: 'Join a Team'
                putIn: ContentPane
            });

               createElement({
                elem: 'p',
                textContent: 'Once you have created a player account, you can now join a team. Click on the link "Teams" in the navigation bar to display the team list.
                Click on the "Join Team" button beside your chosen team to join it. You can also click on "Statistics" button of a given team to view the players in the team, their goals scored, and their team ranking (based on total goals scored).'
                putIn: ContentPane
            });

              createElement({
                elem: 'h3',
                textContent: 'View Tournaments'
                putIn: ContentPane
            });

               createElement({
                elem: 'p'
                textContent: 'To view all tournaments (Pre-game, In-progress, or Post-game), click on the "Tournament" button in the navigation bar.
                You can view the teams entered in each tournament by pressing the "Teams" button beside a specific team. For Post-game tournaments, you can also view the completed matches with results, and final team standings.',
                putIn: ContentPane
            });
        }

               createElement({
                elem: 'h3',
                textContent: 'Join a Tournaments'
                putIn: ContentPane
            });

               createElement({
                elem: 'p'
                textContent: 'If you are a team captain, you have the ability to join tournaments! Simply click the "Join" button beside any of the Pre-game tournaments to join it.',
                putIn: ContentPane
            });
        }

        if(Keeper.hasRole('Organizer')) {

            createElement({
                elem: 'p',
                textContent: 'Welcome to Keeper, your personalized Tournament Maker! '
                putIn: ContentPane
            });

            createElement({
                elem: 'h3',
                textContent: 'Create a Player'
                putIn: ContentPane
            });

               createElement({
                elem: 'p',
                textContent: 'As an organizer, you have the ability to create new players. Simply navigate to "Teams" from the navigation bar, and press "Create player". 
                Enter the name and jersey number of the player, and press "Create".'
                putIn: ContentPane
            });

            createElement({
                elem: 'h3',
                textContent: 'Teams'
                putIn: ContentPane
            });

               createElement({
                elem: 'p',
                textContent: 'Click on the link "Teams" in the navigation bar to display the team list.
                Click on the "Join Team" button beside your chosen team to join it. 
                You can also click on "Statistics" button of a given team to view the players in the team, their goals scored, and their team ranking (based on total goals scored).
                You can also add a player to a team by chosing "Add a player" button of the chosen team, and then chosing the player to add from the drop down list.'
                putIn: ContentPane
            });

              createElement({
                elem: 'h3',
                textContent: 'Create a Team'
                putIn: ContentPane
            });

               createElement({
                elem: 'p',
                textContent: 'As an organizer, you have the ability to create new teams. In order to create a team, there must exist atleast one free player (a player unattached to a team). 
                This is beacause every team needs a unique team captain. ' 
                putIn: ContentPane
            });

              createElement({
                elem: 'h3',
                textContent: 'View Tournaments'
                putIn: ContentPane
            });

               createElement({
                elem: 'p'
                textContent: 'To view all tournaments (Pre-game, In-progress, or Post-game), click on the "Tournament" button in the navigation bar.
                You can view the teams entered in each tournament by pressing the "Teams" button beside a specific team. For Post-game tournaments, you can also view the completed matches with results, and final team standings.',
                putIn: ContentPane
            });
        }
               createElement({
                elem: 'h3',
                textContent: 'Create a Tournaments'
                putIn: ContentPane
            });

               createElement({
                elem: 'p'
                textContent: 'To create a tournament, simply navigate to the Tournament page from the navigation bar, then press "Create Tournament". Enter a tournament name, and choose from Round Robin, Knock-out, or Combination formats.
                A Knock-out style tournament requires n^2 teams, and both Combination and Round Robit require 2 or more teams. You can also being a Pre-game tournament by pressing the "Begin" button next to the tournament name.'
                putIn: ContentPane
            });
        }
       }

    };

    return Module;
});