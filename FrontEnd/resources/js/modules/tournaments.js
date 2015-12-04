/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#tournaments
 *  Details             :  Module to display tournaments
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
        id: 'tournaments', // Appears in address bar. Used in Links.
        title: 'Tournaments', // Used in title
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

        createElement({
            elem: 'h1',
            textContent: 'Tournaments',
            putIn: ContentPane
        });

        Keeper.data.get('tournaments').done(function (data) {

            data.pregame.forEach(function(item) {
               item.Tournament_Type_String = Module.getTournamentTypeString(item.Tournament_Type);
            });
            data.inprogress.forEach(function (item) {
                item.Tournament_Type_String = Module.getTournamentTypeString(item.Tournament_Type);
            });
            data.over.forEach(function (item) {
                item.Tournament_Type_String = Module.getTournamentTypeString(item.Tournament_Type);
            });


            if (Keeper.hasRole('Organizer')) {
                createElement({
                    elem: 'button',
                    className: 'block-btn btn btn-primary',
                    textContent: 'Create Tournament',
                    onclick: function () {
                        Module.createTournament();
                    },
                    putIn: ContentPane
                })
            }

            if (data.pregame.length > 0) {

                createElement({
                    elem: 'h2',
                    textContent: 'Pre-game Tournaments',
                    putIn: ContentPane
                });

                var pregameButtons = [{
                    title: 'View Teams',
                    text: 'Teams',
                    style: 'primary',
                    onclick: function (row) {
                        Keeper.loadModule('teams', [row.Tournament_ID]);
                    }
                }];

                if (Keeper.hasRole('Organizer')) {
                    pregameButtons.push({
                        title: 'Add Team',
                        text: 'Add Team',
                        style: 'success',
                        onclick: function (row) {
                            Module.addTeam(row);
                        }
                    });

                    pregameButtons.push({
                        title: 'Begin Tournament',
                        text: 'Begin',
                        style: 'success',
                        onclick: function (row) {
                            Module.beginTournament(row);
                        }
                    });
                } else if (Keeper.hasRole('Player')) {//&& Keeper.user.Team != null && Keeper.user.Team.Captain_ID == Keeper.user.Person_ID
                    pregameButtons.push({
                        title: 'Join Tournament',
                        text: 'Join',
                        style: 'success',
                        onclick: function (row) {
                            Keeper.showAlert('Not yet implemented :( sorry', 'warning');
                        }
                    });
                }

                Keeper.createTableFromData({
                    id: 'TournamentsTable',
                    data: data.pregame,
                    fields: [
                        {
                            title: 'Tournament Name',
                            key: 'Tournament_Name'
                        },
                        {
                            title: 'Tournament Type',
                            key: 'Tournament_Type_String'
                        }
                    ],
                    buttons: pregameButtons,
                    putIn: ContentPane
                });
            }

            if (data.inprogress.length > 0) {
                createElement({
                    elem: 'h2',
                    textContent: 'In-progress Tournaments',
                    putIn: ContentPane
                });

                var inprogressButtons = [{
                    title: 'View Teams',
                    text: 'Teams',
                    style: 'primary',
                    onclick: function (row) {
                        Keeper.loadModule('teams', [row.Tournament_ID]);
                    }
                },{
                    title: 'View Matches',
                    text: 'Matches',
                    style: 'primary',
                    onclick: function(row) {
                        Keeper.loadModule('matches', [row.Tournament_ID]);
                    }
                }];

                if (Keeper.hasRole('Organizer')) {
                    inprogressButtons.push({
                        title: 'End Tournament',
                        text: 'End',
                        style: 'danger',
                        onclick: function (row) {
                            Module.endTournament(row);
                        }
                    });
                }

                Keeper.createTableFromData({
                    id: 'TournamentsTable',
                    data: data.inprogress,
                    fields: [
                        {
                            title: 'Tournament Name',
                            key: 'Tournament_Name'
                        },
                        {
                            title: 'Tournament Type',
                            key: 'Tournament_Type_String'
                        }
                    ],
                    buttons: inprogressButtons,
                    putIn: ContentPane
                });
            }

            if (data.over.length > 0) {
                createElement({
                    elem: 'h2',
                    textContent: 'Post-game Tournaments',
                    putIn: ContentPane,
                    className: 'tournament-table-header'
                });
                Keeper.createTableFromData({
                    id: 'TournamentsTable',
                    data: data.over,
                    fields: [
                        {
                            title: 'Tournament Name',
                            key: 'Tournament_Name'
                        },
                        {
                            title: 'Tournament Type',
                            key: 'Tournament_Type_String'
                        }
                    ],
                    buttons: [
                        {
                            title: 'View Teams',
                            text: 'Teams',
                            style: 'primary',
                            onclick: function (row) {
                                Keeper.loadModule('teams', [row.Tournament_ID]);
                            }
                        }, {
                            title: 'View Matches',
                            text: 'Matches',
                            style: 'primary',
                            onclick: function (row) {
                                Keeper.loadModule('matches', [row.Tournament_ID]);
                            }
                        },
                        {
                            title: 'View Standings',
                            text: 'Standings',
                            style: 'primary',
                            onclick: function (row) {
                                Keeper.loadModule('standings',['tournament', row.Tournament_ID])
                            }
                        }
                    ],
                    putIn: ContentPane
                });
            }
        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger');
        });

        if (Keeper.hasRole('Organizer')) {

        }
        return true;
    };

    Module.createTournament = function () {
        var tournamentName = createElement({
            elem: 'input',
            type: 'text',
            className: 'form-control',
            attributes: {
                placeHolder: 'Tournament Name',
                required: '',
                autofocus: ''
            }
        });

        var tournamentSelect = createElement({
            elem: 'select',
            className: 'form-control',
            inside: [
                createElement({
                    elem: 'option',
                    disabled: true,
                    selected: true,
                    value: -1,
                    textContent: 'Select tournament type'
                }),
                createElement({
                    elem: 'option',
                    value: 0,
                    textContent: 'Knockout'
                }),
                createElement({
                    elem: 'option',
                    value: 1,
                    textContent: 'Roundrobin'
                }),
                createElement({
                    elem: 'option',
                    value: 2,
                    textContent: 'Knockout Roundrobin'
                })
            ]
        });
        Keeper.showModal('Create Tournament', createElement({
            elem: 'div',
            inside: [
                tournamentName,
                tournamentSelect
            ]
        }), 'Create', function () {
            if (tournamentSelect.value > -1) {
                Keeper.data.update('create-tournament', {
                    Tournament_Name: tournamentName.value,
                    Tournament_Type: tournamentSelect.value
                }).done(function () {
                    Keeper.reloadModule();
                }).fail(function (data) {
                    Keeper.showAlert(data.message, 'danger');
                })
            }
        })
    };

    Module.beginTournament = function (tournament) {
        Keeper.data.update('begin-tournament', {
            Tournament_ID: tournament.Tournament_ID
        }).done(function () {
            Keeper.reloadModule();
        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger');
        })
    };

    Module.endTournament = function (tournament) {
        Keeper.data.update('end-tournament', {
            Tournament_ID: tournament.Tournament_ID
        }).done(function () {
            Keeper.reloadModule();
        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger');
        })
    };

    Module.addTeam = function (tournament) {

        Keeper.data.get('teams-not-in-tournament', {
            Tournament_ID: tournament.Tournament_ID
        }).done(function (data) {
            var teams = data.Teams;

            if (teams.length == 0) {
                Keeper.showAlert('All teams in the system have already been added to this tournament!', 'warning');
                return;
            }

            var options = [createElement({
                elem: 'option',
                disabled: true,
                selected: true,
                value: -1,
                textContent: 'Select team'
            })];

            teams.forEach(function (team) {
                options.push(createElement({
                    elem: 'option',
                    value: team.Team_ID,
                    textContent: team.Team_Name
                }));
            });

            var teamSelect = createElement({
                elem: 'select',
                className: 'form-control',
                inside: options
            });

            Keeper.showModal('Add Team to ' + tournament.Tournament_Name, createElement({
                elem: 'div',
                inside: [
                    teamSelect
                ]
            }), 'Add', function () {
                if (teamSelect.value > -1) {
                    Keeper.data.update('add-team-to-tournament', {
                        Tournament_ID: tournament.Tournament_ID,
                        Team_ID: teamSelect.value
                    }).done(function () {
                        Keeper.showAlert('Team has bee added to ' + tournament.Tournament_Name + '!', 'success', 5000);
                        Keeper.reloadModule();
                    }).fail(function (data) {
                        Keeper.showAlert(data.message, 'danger');
                    })
                } else {
                    Keeper.showAlert('You must select a team before you can add to the tournament', 'warning');
                }
            });

        }).fail(function (data) {
            Keeper.showAlert(data.message, 'danger');
        });
    };

    Module.getTournamentTypeString = function(tt) {
        switch (parseInt(tt)) {
            case 0:
                return "Knockout";
            case 1:
                return "Roundrobin";
            case 2:
                return "Knockout Roundrobin";
            default:
                return 'Unknown!';
        }
    };

    return Module;
});