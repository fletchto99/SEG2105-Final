/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#matches
 *  Details             :  Module to display matchinfo
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

        Keeper.data.get('tournament', {
            Tournament_ID: parameters[0]
        }).done(function (tournament) {
            createElement({
                elem: 'h1',
                textContent: 'Matches in ' + tournament.Tournament_Name,
                putIn: ContentPane
            });
            Keeper.data.get('matches-in-tournament', {
                Tournament_ID: tournament.Tournament_ID
            }).done(function (matches) {
                if (matches.pregame.length > 0) {
                    Module.displayPregameMatches(matches.pregame, ContentPane);
                }
                if (matches.inprogress.length > 0) {
                    Module.displayInprogressMatches(matches.inprogress, ContentPane);
                }
                if (matches.over.length > 0) {
                    Module.displayEndedMatches(matches.over, ContentPane);
                }
            }).fail(function () {
                Keeper.showAlert(data.message, 'danger');
            })
        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger');
        });

        return true;
    };

    Module.displayPregameMatches = function(matches, container) {
        matches.forEach(function(match) {
            if (match.Team_A_Name == null) {
                match.Team_A_Name = "Not yet decided!"
            }
            if (match.Team_B_Name == null) {
                match.Team_B_Name = "Not yet decided!"
            }
        });

        var buttons = [];

        if (Keeper.hasRole('Organizer')) {
            buttons.push({
                title: 'Begin Match',
                text: 'Begin',
                style: 'primary',
                onclick: function (row) {
                    Keeper.data.update('begin-match', {
                        Match_ID: row.Match_ID
                    }).done(function() {
                        console.log(Keeper.current_params);
                        Keeper.reloadModule(Keeper.current_params);
                    }).fail(function (data) {
                        Keeper.showAlert(data.message, 'danger');
                    })
                }
            });
        }

        Keeper.createTableFromData({
            data: matches,
            fields:[
                {
                    title: 'Team A',
                    key: 'Team_A_Name'
                },
                {
                    title: 'Team B',
                    key: 'Team_B_Name'
                }
            ],
            buttons:buttons,
            putIn: container
        })

    };


    Module.displayInprogressMatches = function(matches, container) {

        var buttons = [];

        if (Keeper.hasRole('Organizer')) {
            buttons.push({
                title: 'Add Goal',
                text: 'Add Goal',
                style: 'primary',
                onclick: function (row) {
                    Keeper.showAlert('Implement adding goals!', 'danger');
                }
            });
        }

        Keeper.createTableFromData({
            data: matches,
            fields:[
                {
                    title: 'Team A',
                    key: 'Team_A_Name'
                },
                {
                    title: 'Team B',
                    key: 'Team_B_Name'
                }
            ],
            buttons:buttons,
            putIn: container
        })

    };

    Module.displayEndedMatches = function(matches, container) {
        console.log(matches);
        Keeper.createTableFromData({
            data: matches,
            fields:[
                {
                    title: 'Winner',
                    key: 'Winning_Team_Name'
                },
                {
                    title: 'Team A',
                    key: 'Team_A_Name'
                },
                {
                    title: 'Team B',
                    key: 'Team_B_Name'
                }
            ],
            putIn: container
        })

    };


    return Module;
});