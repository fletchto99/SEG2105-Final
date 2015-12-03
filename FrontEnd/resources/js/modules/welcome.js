/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#welcome
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
        id: 'welcome', // Appears in address bar. Used in Links.
        title: 'Welcome', // Used in title
        visible_in_nav_bar: false,
        navbar_visible: false,
        css: 'welcome.css'
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

        var usernameInput = createElement({
            elem: 'input',
            type: 'text',
            className: 'form-control',
            attributes: {
                placeHolder: 'Username',
                required: '',
                autofocus: ''
            }
        });

        var passwordInput = createElement({
            elem: 'input',
            type: 'password',
            className: 'form-control',
            attributes: {
                placeHolder: 'Password',
                required: ''
            }
        });

        var loginForm = createElement({
            elem: 'div',
            inside: [
                usernameInput,
                passwordInput
            ]
        });

        createElement({
            elem: 'button',
            textContent: 'Login',
            className: 'btn btn-default',
            onclick: function () {
                Keeper.showModal('test', loginForm,
                    'Login',
                    function () {
                        Keeper.data.login(usernameInput.value, passwordInput.value).done(function (data) {
                            Keeper.user = data;
                            Keeper.loadModule('main');
                        }).fail(function (error) {
                            alert(error.message);
                        });
                    });
            },
            putIn: ContentPane
        });

        createElement({
            elem: 'button',
            textContent: 'Create User',
            className: 'btn btn-default',
            onclick: function () {
                Keeper.showModal('test', loginForm,
                    'Login',
                    function () {
                        Keeper.data.login(usernameInput.value, passwordInput.value).done(function (data) {
                            Keeper.user = data;
                            Keeper.loadModule('main');
                        }).fail(function (error) {
                            alert(error.message);
                        });
                    });
            },
            putIn: ContentPane
        });
        return true;
    };

    return Module;
});