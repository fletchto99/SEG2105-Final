/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#standings
 *  Details             :  Module to display tournament standings
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
        id: 'standings', // Appears in address bar. Used in Links.
        title: 'Standings', // Used in title
        visible_in_nav_bar: false,
        navbar_visible: true,
        css: 'standings.css'
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
            Keeper.showAlert('A tournament is required to show standings', 'danger');
            return false;
        }

        Keeper.data.get('tournament', {
            Tournament_ID: parameters[0]
        }).done(function(tournament) {
            createElement({
                elem: 'h1',
                textContent: 'Standings for ' + tournament.Tournament_Name,
                putIn: ContentPane
            });
            Keeper.data.get('tournament-standings', {
                Tournament_ID: tournament.Tournament_ID
            }).done(function(standings) {
                if (parseInt(standings.Tournament_Type) == 0) {
                    Module.displayKOStandings(standings.knockout_matches, ContentPane);
                } else if (parseInt(standings.Tournament_Type) == 1) {
                    console.log(standings);
                    Module.displayRRStandings(standings.roundrobin_matches, ContentPane);
                }
            }).fail(function() {
                Keeper.showAlert(data.message, 'danger');
            })
        }).fail(function(data) {
            Keeper.showAlert(data.message, 'danger');
        });

        return true;
    };

    Module.displayKOStandings = function (matches, container) {
        Keeper.createTableFromData({
            data: matches,
            fields: [
                {
                    title: 'Round',
                    key: 'Round'
                },
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
            buttons: [
                {
                    title: 'Match Details',
                    text: 'Details',
                    style: 'primary',
                    onclick: function (row) {
                        Keeper.showAlert('Implement this!!', 'danger');
                    }
                }
            ],
            putIn: container
        });
    };

    Module.displayRRStandings = function (standings, container) {
        var i = 1;
        standings.forEach(function(standings) {
            standings.Position = i;
            i++;
        });
        Keeper.createTableFromData({
            data: standings,
            fields: [
                {
                    title: 'Position',
                    key: 'Position'
                },
                {
                    title: 'Team',
                    key: 'Team_Name'
                },
                {
                    title: 'Matches Won',
                    key: 'Matches_Won'
                },
                {
                    title: 'Matches Played',
                    key: 'Matches_Played'
                }
            ],
            putIn: container
        });
    };


    return Module;
});