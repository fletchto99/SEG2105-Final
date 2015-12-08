/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Application Path    :  /#welcome
 *  Details             :  Module to display welcome screen
 */

Keeper.createModule(function (Keeper) {
    'use strict';

    /**
     * The properties of the welcome module
     */
    var Module = {
        id: 'welcome', // Appears in address bar. Used in Links.
        title: 'Welcome', // Used in title
        visible_in_nav_bar: false,
        navbar_visible: false
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

        createElement({
            elem: 'h1',
            textContent: 'Welcome to Tournament Maker',
            putIn: ContentPane
        });

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

        var createAccountUsername = createElement({
            elem: 'input',
            type: 'text',
            className: 'form-control',
            attributes: {
                placeHolder: 'Username',
                required: '',
                autofocus: ''
            }
        });

        var createAccountPassword = createElement({
            elem: 'input',
            type: 'password',
            className: 'form-control',
            attributes: {
                placeHolder: 'Password',
                required: ''
            }
        });

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
                required: '',
                autofocus: ''
            }
        });

        var loginForm = createElement({
            elem: 'div',
            inside: [
                usernameInput,
                passwordInput
            ]
        });

        var createAccountForm = createElement({
            elem: 'div',
            inside: [
                createAccountUsername,
                createAccountPassword,
                firstnameInput,
                lastnameInput
            ]
        });

        createElement({
            elem: 'button',
            textContent: 'Login',
            className: 'block-btn welcomebtn btn btn-info btn-lg',
            onclick: function () {
                Keeper.showModal('Login', loginForm,
                    'Login',
                    function () {
                        Keeper.data.login(usernameInput.value, passwordInput.value).done(function (data) {
                            Keeper.user = data;
                            Keeper.loadModule('main');
                            Keeper.showAlert('Welcome to Tournament Maker ' + Keeper.user.First_Name, 'info', 10000);
                        }).fail(function (error) {
                            Keeper.showAlert(error.message, 'danger');
                        });
                        return false;
                    });
            },
            putIn: ContentPane
        });

        createElement({
            elem: 'button',
            textContent: 'Create User',
            className: 'block-btn welcomebtn btn btn-info btn-lg',
            onclick: function () {
                Keeper.showModal('Create Account', createAccountForm,
                    'Login',
                    function () {
                        Keeper.data.update('create-user',{
                            Username: createAccountUsername.value,
                            Password: createAccountPassword.value,
                            First_Name: firstnameInput.value,
                            Last_Name: lastnameInput.value
                        }).done(function (data) {
                            Keeper.user = data;
                            Keeper.loadModule('main');
                            Keeper.showAlert('Welcome to Tournament Maker ' + Keeper.user.First_Name, 'info', 10000);
                        }).fail(function (error) {
                            Keeper.showAlert(error.message, 'danger');
                        });
                    })
            },
            putIn: ContentPane
        });
        return true;
    };

    return Module;
});