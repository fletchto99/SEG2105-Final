/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#teams
 *  Details             :  Module to display teams in the system (ability for player to join/create a team)
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
        id: 'teams', // Appears in address bar. Used in Links.
        title: 'Teams', // Used in title
        visible_in_nav_bar: true,
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

        if (parameters[0]) {
            console.log(parameters);
            Keeper.data.get('tournament', {
                Tournament_ID: parameters[0]
            }).done(function (data) {
                createElement({
                    elem: 'h1',
                    textContent: 'Teams in ' + data.Tournament_Name,
                    putIn: ContentPane
                });
                Keeper.data.get('teams-in-tournament', {
                    Tournament_ID: parameters[0]
                }).done(function (data) {
                    Module.buildTeamsTable(data.Teams, ContentPane, parameters[0]);
                }).fail(function (data) {
                    Keeper.showAlert(data.message, 'danger');
                });
            }).fail(function (data) {
                Keeper.showAlert(data.message, 'danger');
            });
        } else {
            createElement({
                elem: 'h1',
                textContent: 'All Teams',
                putIn: ContentPane
            });
            Keeper.data.get('teams').done(function (data) {
                Module.buildTeamsTable(data.Teams, ContentPane);
            }).fail(function (data) {
                Keeper.showAlert(data.message, 'danger');
            });
        }


        if (Keeper.hasRole('Organizer')) {

        } else {

        }

        return true;
    };

    Module.buildTeamsTable = function (teams, container, tournament) {

        teams.forEach(function (team) {
            team.Captain = team.Captain_First_Name + " " + team.Captain_Last_Name;
        });
        var buttons = [{
            title: 'Team Statistics',
            text: 'Statistics',
            style: 'primary',
            onclick: function (row) {
                if (tournament) {
                    Keeper.loadModule('standings', ['team', row.Team_ID, tournament]);
                } else {
                    Keeper.loadModule('standings', ['team', row.Team_ID]);
                }
            }
        }];
        if (Keeper.hasRole('Player') && Keeper.user.Team == null) {
            buttons.push({
                title: 'Join team',
                text: 'Join Team',
                style: 'success',
                onclick: function (row) {
                    Keeper.showAlert('Implement this!!', 'danger');
                }
            })
        }
        Keeper.createTableFromData({
            data: teams,
            fields: [
                {
                    title: 'Team Name',
                    key: 'Team_Name'
                },
                {
                    title: 'Team Captain',
                    key: 'Captain'
                }
            ],
            buttons: buttons,
            putIn: container
        });
    };

    return Module;
});