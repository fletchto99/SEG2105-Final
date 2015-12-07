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

            if (Keeper.hasRole('Organizer')) {
                createElement({
                    elem: 'button',
                    className: 'btn-inline btn btn-primary',
                    textContent: 'Create Player',
                    onclick: function () {
                        Module.createPlayer();
                    },
                    putIn: ContentPane
                });
            }

            if (Keeper.hasRole('Organizer') || (Keeper.hasRole('Player') && Keeper.user.Team == null)) {
                createElement({
                    elem: 'button',
                    className: 'btn-inline btn btn-primary',
                    textContent: 'Create Team',
                    onclick: function () {
                        Module.createTeam();
                    },
                    putIn: ContentPane
                })
            }

            Keeper.data.get('teams').done(function (data) {
                Module.buildTeamsTable(data.Teams, ContentPane);
            }).fail(function (data) {
                Keeper.showAlert(data.message, 'danger');
            });
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
                    Module.joinTeam(row);
                }
            });
        } else if (Keeper.hasRole('Organizer') && tournament == undefined) {
            buttons.push({
                title: 'Add Player',
                text: 'Add Player',
                style: 'success',
                onclick: function (row) {
                    Module.addToTeam(row);
                }
            });
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

    Module.createTeam = function () {
        if (Keeper.hasRole('Organizer')) {
            Keeper.data.get('manual-players', {
                No_Team_Assigned: true
            }).done(function (data) {
                if (data.Players.length > 0) {
                    var teamNameInput = createElement({
                        elem: 'input',
                        type: 'text',
                        className: 'form-control',
                        attributes: {
                            placeHolder: 'Team Name',
                            required: '',
                            autofocus: ''
                        }
                    });

                    var playerOptions = [createElement({
                        elem: 'option',
                        disabled: true,
                        selected: true,
                        value: -1,
                        textContent: 'Select team captain'
                    })];


                    data.Players.forEach(function (player) {
                        playerOptions.push(createElement({
                            elem: 'option',
                            value: player.Person_ID,
                            textContent: player.First_Name + " " + player.Last_Name
                        }));
                    });

                    var playerSelect = createElement({
                        elem: 'select',
                        className: 'form-control',
                        inside: playerOptions
                    });
                    Keeper.showModal('Create Team', createElement({
                        elem: 'div',
                        inside: [
                            teamNameInput,
                            playerSelect
                        ]
                    }), 'Create', function () {
                        if (playerSelect.value > -1) {
                            Keeper.data.update('create-team', {
                                Team_Name: teamNameInput.value,
                                Captain_ID: playerSelect.value
                            }).done(function (data) {
                                Keeper.reloadModule(Keeper.current_params);
                                Keeper.showAlert('Team created!', 'success')
                            }).fail(function (data) {
                                Keeper.showAlert(data.message, 'danger')
                            });
                        } else {
                            Keeper.showAlert('Please select a team captain!', 'warning');
                        }
                    })
                } else {
                    Keeper.showAlert('There are no free players to be the team captain! Please create a player first!', 'warning');
                }
            }).fail(function (data) {
                Keeper.showAlert(data.message, 'danger');
            });
        } else {
            var teamNameInput = createElement({
                elem: 'input',
                type: 'text',
                className: 'form-control',
                attributes: {
                    placeHolder: 'Team Name',
                    required: '',
                    autofocus: ''
                }
            });
            Keeper.showModal('Create Team', createElement({
                elem: 'div',
                inside: [
                    teamNameInput
                ]
            }), 'Create', function () {
                Keeper.data.update('create-team', {
                    Team_Name: teamNameInput.value
                }).done(function (data) {
                    Keeper.user.Team = data;
                    Keeper.reloadModule(Keeper.current_params);
                    Keeper.showAlert('Team created!', 'success')
                }).fail(function (data) {
                    Keeper.showAlert(data.message, 'danger')
                });
            })
        }
    };

    Module.addToTeam = function (team) {
        Keeper.data.get('manual-players', {
            No_Team_Assigned: true
        }).done(function (data) {
            if (data.Players.length > 0) {

                var playerOptions = [createElement({
                    elem: 'option',
                    disabled: true,
                    selected: true,
                    value: -1,
                    textContent: 'Select player...'
                })];


                data.Players.forEach(function (player) {
                    playerOptions.push(createElement({
                        elem: 'option',
                        value: player.Person_ID,
                        textContent: player.First_Name + " " + player.Last_Name
                    }));
                });

                var playerSelect = createElement({
                    elem: 'select',
                    className: 'form-control',
                    inside: playerOptions
                });
                Keeper.showModal('Add Player to ' + team.Team_Name, createElement({
                    elem: 'div',
                    inside: [
                        playerSelect
                    ]
                }), 'Add', function () {
                    if (playerSelect.value > -1) {
                        Keeper.data.update('add-player-to-team', {
                            Team_ID: team.Team_ID,
                            Player_ID: playerSelect.value
                        }).done(function (data) {
                            Keeper.reloadModule(Keeper.current_params);
                            Keeper.showAlert('Player added!', 'success')
                        }).fail(function (data) {
                            Keeper.showAlert(data.message, 'danger')
                        });
                    } else {
                        Keeper.showAlert('Please select a player', 'warning');
                    }
                })
            } else {
                Keeper.showAlert('There are no free players! Please create a player first!', 'warning');
            }
        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger');
        });

    };

    Module.joinTeam = function(team) {
        Keeper.data.update('add-player-to-team', {
            Team_ID: team.Team_ID
        }).done(function (data) {
            Keeper.user.Team = team;
            Keeper.reloadModule(Keeper.current_params);
            Keeper.showAlert('Successfully joined ' + team.Team_Name, 'success')
        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger')
        });
    };

    Module.createPlayer = function () {
        var firstnameInput = createElement({
            elem: 'input',
            type: 'text',
            className: 'form-control',
            attributes: {
                placeHolder: 'First Name',
                required: '',
                autofocus: ''
            }
        });

        var lastnameInput = createElement({
            elem: 'input',
            type: 'text',
            className: 'form-control',
            attributes: {
                placeHolder: 'Last Name',
                required: ''
            }
        });

        var jerseyNumberInput = createElement({
            elem: 'input',
            type: 'text',
            className: 'form-control',
            attributes: {
                placeHolder: 'Jersey Number',
                required: ''
            }
        });

        Keeper.showModal('Create Player', createElement({
            elem: 'div',
            inside: [
                firstnameInput,
                lastnameInput,
                jerseyNumberInput
            ]
        }), 'Create', function () {
            Keeper.data.update('create-player', {
                First_Name: firstnameInput.value,
                Last_Name: lastnameInput.value,
                Jersey_Number: jerseyNumberInput.value
            }).done(function (data) {
                Keeper.hideModal();
                Keeper.showAlert('Player created!', 'success')
            }).fail(function (data) {
                Keeper.showAlert(data.message, 'danger')
            });

        })
    };

    return Module;
});