/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#matches
 *  Details             :  Module to display match info
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
                    title: 'Round',
                    key: 'Round'
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

                    var assisterSelect = createElement({
                        elem: 'select',
                        className: 'form-control',
                        inside: [
                            createElement({
                                elem: 'option',
                                disabled: true,
                                selected: true,
                                value: -1,
                                textContent: 'Please choose a team to pick a goal scorer...'
                            })
                        ]
                    });

                    var playerSelect = createElement({
                        elem: 'select',
                        className: 'form-control',
                        inside: [
                            createElement({
                                elem: 'option',
                                disabled: true,
                                selected: true,
                                value: -1,
                                textContent: 'Please choose a team to pick an assister...'
                            })
                        ]
                    });

                    var teamSelect = createElement({
                        elem: 'select',
                        className: 'form-control',
                        inside: [
                            createElement({
                                elem: 'option',
                                disabled: true,
                                selected: true,
                                value: -1,
                                textContent: 'Select team'
                            }),
                            createElement({
                                elem: 'option',
                                value: row.Team_A_ID,
                                textContent: row.Team_A_Name
                            }),
                            createElement({
                                elem: 'option',
                                value: row.Team_B_ID,
                                textContent: row.Team_B_Name
                            })
                        ],
                        onchange: function() {
                            Keeper.data.get('players-in-team',{
                                Team_ID: teamSelect.value
                            }).done(function(teamData) {
                                var data = teamData.Players;
                                playerSelect.innerHTML = '';//remove all children
                                assisterSelect.innerHTML = '';//remove all children

                                createElement({
                                    elem: 'option',
                                    disabled: true,
                                    selected: true,
                                    value: -1,
                                    textContent: 'Select goal scorer...',
                                    putIn: playerSelect
                                });

                                createElement({
                                    elem: 'option',
                                    disabled: true,
                                    selected: true,
                                    value: -1,
                                    textContent: 'Select assister...',
                                    putIn: assisterSelect
                                });

                                createElement({
                                    elem: 'option',
                                    value: -1,
                                    textContent: 'None',
                                    putIn: assisterSelect
                                });


                                data.forEach(function(player) {
                                    createElement({
                                        elem: 'option',
                                        value: player.Person_ID,
                                        textContent: player.First_Name + " " + player.Last_Name,
                                        putIn: playerSelect
                                    });
                                    createElement({
                                        elem: 'option',
                                        value: player.Person_ID,
                                        textContent: player.First_Name + " " + player.Last_Name,
                                        putIn: assisterSelect
                                    });
                                });
                            }).fail(function(data) {
                                Keeper.showAlert(data.message, 'danger');
                            })
                        }
                    });

                    Keeper.showModal('Add Goal', createElement({
                        elem: 'div',
                        inside: [
                            teamSelect,
                            playerSelect,
                            assisterSelect
                        ]
                    }), 'Add', function () {
                        if (playerSelect.value > -1) {
                            Keeper.data.update('add-goal', {
                                Match_ID: row.Match_ID,
                                Player_ID: playerSelect.value,
                                Assist_ID: assisterSelect.value > -1 ? assisterSelect.value : null
                            }).done(function () {
                                Keeper.showAlert('Goal added', 'success', 5000);
                                Keeper.hideModal();
                            }).fail(function (data) {
                                Keeper.showAlert(data.message, 'danger');
                            })
                        } else {
                            Keeper.showAlert('You must choose a player who scored the goal!')
                        }
                    })
                }
            });

            buttons.push({
                title: 'End Match',
                text: 'End',
                style: 'danger',
                onclick: function (row) {
                    Keeper.data.update('end-match', {
                        Match_ID: row.Match_ID
                    }).done(function() {
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
                    title: 'Round',
                    key: 'Round'
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
            buttons:buttons,
            putIn: container
        })

    };

    Module.displayEndedMatches = function(matches, container) {
        Keeper.createTableFromData({
            data: matches,
            fields:[
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
            putIn: container
        })

    };


    return Module;
});