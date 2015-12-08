/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#main
 *  Details             :  Module to display main logged in welcome page
 */

Keeper.createModule(function (Keeper) {
    'use strict';

    /**
     * The properties of the main module
     */
    var Module = {
        id: 'main', // Appears in address bar. Used in Links.
        title: 'Tournament Maker', // Used in title
        visible_in_nav_bar: false,
        navbar_visible: true
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

        //validate the user is logged in
        if (!Keeper.isLoggedIn()) {
            return false;
        }

        createElement({
            elem: 'h1',
            textContent: 'Welcome ' + Keeper.user.First_Name,
            putIn: ContentPane
        });

        if (Keeper.hasRole('Organizer')) {
            createElement({
                elem: 'p',
                textContent: 'Welcome to tournament maker, ' + Keeper.user.First_Name + '! This page will soon serve as your personal organizer portal.',
                putIn: ContentPane
            });
        } else {
            if (Keeper.user.Jersey_Number == null) {
                createElement({
                    elem: 'p',
                    textContent: 'Welcome to tournament maker, ' + Keeper.user.First_Name + '! We noticed your jersey number is not set. Please use the button below to set your jersey number before you join a team!',
                    putIn: ContentPane
                });
            } else if (Keeper.user.Team_ID == null) {
                createElement({
                    elem: 'p',
                    textContent: 'Welcome to tournament maker, ' + Keeper.user.First_Name + '! We noticed you are not part of a team yet. Before you are able to join a tournament you must be a member of a team. Please go to the teams page to create or join a team.',
                    putIn: ContentPane
                });
            } else {
                createElement({
                    elem: 'p',
                    textContent: 'Welcome to tournament maker, ' + Keeper.user.First_Name + '! This page will soon serve as your personal portal.',
                    putIn: ContentPane
                });
            }
            var jerseyNumberInput = createElement({
                elem: 'input',
                type: 'number',
                className: 'form-control',
                value: Keeper.user.Jersey_Number,
                attributes: {
                    placeHolder: 'Jersey Number',
                    required: '',
                    autofocus: '',
                    minValue: 0
                }
            });
            createElement({
                elem: 'button',
                textContent: 'Update Jersey Number',
                className: 'block-btn btn btn-info btn-lg',
                onclick: function () {
                    Keeper.showModal('Update Jersey Number', jerseyNumberInput,
                        Keeper.user.Jersey_Number == null ? 'Set' : 'Update',
                        function () {
                            Keeper.data.update('player-number',{
                                Jersey_Number: jerseyNumberInput.value
                            }).done(function (data) {
                                console.log(data);
                                Keeper.user.Jersey_Number = data.Number;
                                Keeper.showAlert(data.Success, 'info', 10000);
                                Keeper.reloadModule();
                            }).fail(function (error) {
                                Keeper.showAlert(error.message, 'danger');
                            });
                        });
                    return false
                },
                putIn: ContentPane
            });
        }

        return true;
    };

    return Module;
});