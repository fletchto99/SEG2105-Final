/**
 *  Author(s)           :  Matt Langlois, Kurt Bruneau
 *  File Created        :  May 2014
 *  File Updated        :  December 2015
 *  Details             :  This file contains all the top level functions for the Keeper library
 *                         It must be loaded before any other Keeper library files as it creates the top level namespace.
 */

(function () {
    'use strict';

    // -------------------------------------- Setup Variables ------------------------------------- //
    // Create top level namespace for application
    if (!window.Keeper) {
        window.Keeper = {};
    }

    Keeper.ROOT_URL = 'cgi-bin/controllers/';

    // Set some default variables
    Keeper.Modules = [];
    Keeper.ModuleMap = {};

    //Some global variables
    Keeper.CurrentModule = null;
    Keeper.user = null;

    /**
     * Checks if a user has a specific permission set
     *
     * @param {String} roleName The level of the permission set to check
     * @returns {boolean} True if the user has the required permissions; otherwise false
     */
    Keeper.hasRole = function (roleName) {
        return Keeper.user.Person_ID > 0 && Keeper.user.Role_Name == roleName;
    };

    Keeper.isLoggedIn = function () {
        return Keeper.user.Person_ID !== undefined && Keeper.user.Person_ID > 0;
    };

    /**
     * Initializes Keeper
     */
    Keeper.init = function () {

        //check if a session is already in progress, if so resume it
        Keeper.data.isAuthenticated().done(function (data) {
            Keeper.user = data;

            //Process our page and welcome the user
            var start = function() {
                Keeper.processHashChange();
                Keeper.showAlert('Welcome to Tournament Maker ' + Keeper.user.First_Name, 'info', 10000);
            };

            //Check if
            if (Keeper.user.Team_ID != null) {
                Keeper.data.get('team', {
                    Team_ID: Keeper.user.Team_ID
                }).done(function(data) {
                    Keeper.user.Team = data;
                    start();
                }).fail(function(data) {
                    Keeper.showAlert(data.message, 'warning');
                    Keeper.user.Team = null;
                    start();
                })
            } else {
                Keeper.user.Team = null;
                start();
            }



        }).fail(function () {
            Keeper.user = {
                Person_ID: -1,
                Role_Name: 'unauthenticated'
            };
            Keeper.loadModule('welcome');
        });

        //Listen for hash changes to determine page changes, maintaining state without reloading a page
        window.addEventListener('hashchange', Keeper.processHashChange);

        //Listen for error events and pass them to our notification system
        window.addEventListener('error', function (e) {
            Keeper.showAlert(e.message, 'danger');
        });

    };


    // ------------------------------- Core Keeper Library Functions -------------------------------- //

    /**
     * Navigates to required module when history state changes
     */
    Keeper.processHashChange = function () {
        var hash = window.location.hash.substring(1);
        var parameters = hash.split('/');

        // Hex decode any parameters
        var len = parameters.length;
        for (var i = 0; i < len; i++) {
            parameters[i] = decodeURIComponent(parameters[i]);
        }

        var module_id = parameters.shift();
        Keeper.loadModule(module_id, parameters, false);
    };

    /**
     * Updates the parameters of the currently loaded module
     *
     * @param {Array} parameters The new parameters of the module
     * @param {bool} addHistory Should the current state be stored within Keeper
     */
    Keeper.updateParameters = function (parameters, addHistory) {
        var newHash = '#' + Keeper.CurrentModule.id + '/' + parameters.map(encodeURIComponent).join('/');
        if (addHistory) {
            history.pushState({}, '', newHash);
        } else {
            history.replaceState({}, '', newHash);
        }
    };

    /**
     * Load an instance of a module
     *
     * @param {String} module_id The id of the module to load the instance of
     * @returns {Object} An instance of the requested module
     * @constructor
     */
    Keeper.Module = function (module_id) {
        var module = null;

        if (!module_id || module_id === '') {
            module = Keeper.ModuleMap[0]; //error loading module, load the first one in our module map
        } else if (module_id instanceof Object) {
            module = module_id;
        } else {
            module = Keeper.ModuleMap[module_id];
        }

        return module;
    };

    /**
     * Pushes a module to the mapping of all modules contained within the system.
     * Once the module has been pushed it is available for lookup and loading
     *
     * @param {Array} module The instance of the module to push to the system
     */
    Keeper.pushModule = function (module) {
        Keeper.Modules.push(module);
        Keeper.ModuleMap[module.id] = module;

        // Set the module title to it's name if its not set
        if (!module.title) {
            module.title = module.id;
        }
    };

    Keeper.createModule = function (generator) {
        Keeper.pushModule(generator(Keeper));
    };

    /**
     * Populates the content pane of the screen with the specified module. This loads
     * a module into the system and then calls it's display function. Removing any
     * overlays already created by the system. It pushes the currently loaded module to
     * the history so when the user preses their browser back button it will bring them
     * back to the previously loaded module
     *
     * @param {String} module The ID of the module to be loaded
     * @param {Array} parameters The parameters after the url required by the module
     * @returns {boolean} True if the module was loaded successfully; otherwise false
     */
    Keeper.loadModule = function (module, parameters) {
        module = Keeper.Module(module);
        if (module === undefined) {
            module = Keeper.ModuleMap[Object.keys(Keeper.ModuleMap)[0]];
        }
        // Handle case where module doesn't exist, this should never happpen unless all modules fail to load
        if (!module) {
            console.log("Could not locate!");
            Keeper.showAlert('404 Could not locate the page you were looking for! Sorry!', 'danger');
            return false;
        }

        // Set default value for parameters
        if (!parameters) {
            parameters = [];
        }

        var modulePath = module.id + '/' + parameters.map(encodeURIComponent).join('/');

        if (Keeper.CurrentModule !== module) {
            history.pushState({}, '', '#' + modulePath);

            console.log('Loading module ' + modulePath);
        } else {
            history.replaceState({}, '', '#' + modulePath);
            console.log('Reloading module ' + modulePath);
        }

        // Unload the current module since we're switching to a different one
        if (Keeper.CurrentModule && Keeper.CurrentModule.unload) {
            console.log("Calling " + Keeper.CurrentModule.id + "'s unload function");
            try {
                Keeper.CurrentModule.unload();
            } catch (e) {
                Keeper.error('Error Unloading Module', e.message, e.stack);
            }
        }

        // Set pointers to the new and previous module
        if (Keeper.CurrentModule && Keeper.CurrentModule !== module) {
            Keeper.PreviousModule = Keeper.CurrentModule;
            Keeper.last_params = Keeper.current_params;
        }

        // Replace the old ContentPane with a new one
        var OldContentPane = document.getElementById("ContentPane");
        var ContentPane = createElement({
            elem: 'div',
            id: 'ContentPane',
            className: 'container'
        });

        //Update the page content
        OldContentPane.parentElement.replaceChild(ContentPane, OldContentPane);

        //Update our current module global variables
        Keeper.CurrentModule = module;
        Keeper.current_params = parameters;

        // Build nav bar again
        Keeper.populateNavigationBar();

        //Hide any previously open modals
        Keeper.hideModal();

        // Load content
        var doLoad = function () {

            // Change the title of the page
            document.title = module.title;



            var loaded_ok;
            try {
                loaded_ok = Keeper.CurrentModule.display(ContentPane, parameters);
            } catch (e) {
                console.error(e);
                if (e.message == 'Insufficient Privileges') {
                    return false;
                }
            }

            if (!loaded_ok) {
                console.log("Module failed to display dynamic content, displaying internal error overlay");
                if (Keeper.PreviousModule) {
                    Keeper.loadModule(Keeper.PreviousModule.id);
                } else {
                    Keeper.loadModule('main');
                }
            } else {
                //Determine if the navbar should be visible
                if (module.navbar_visible) {
                    Keeper.showNavBar();
                } else if (!module.navbar_visible) {
                    Keeper.hideNavBar();
                }
            }
        };

        // Load CSS
        var oldCSS = document.getElementById("CurrentModuleStyleTag");
        var loaded = false;
        if (!Keeper.PreviousModule || Keeper.CurrentModule !== Keeper.PreviousModule) {
            if (typeof Keeper.CurrentModule.css !== 'undefined' && Keeper.CurrentModule.css) {
                var onloadFunc = function () {
                    if (!loaded) {
                        doLoad();
                        loaded = true;
                    }
                };
                createElement({
                    id: "CurrentModuleStyleTag",
                    elem: 'link',
                    rel: 'stylesheet',
                    type: 'text/css',
                    href: 'resources/css/modules/' + Keeper.CurrentModule.css,
                    onload: onloadFunc,
                    putIn: document.head
                });
                // We use a set timeout to guarantee that the module will load, regardless of the css.
                setTimeout(function () {
                    onloadFunc();
                }, 1000);
            } else {
                doLoad();
            }
        }

        //remove any old CSS
        if (oldCSS && oldCSS.parentElement) {
            oldCSS.parentElement.removeChild(oldCSS);
        }

        return true;
    };

    /**
     * Reloads the currently loaded module with a specific set of parameters
     *
     * @param {Array} parameters The parameters to reload the module with
     */
    Keeper.reloadModule = function (parameters) {
        Keeper.loadModule(Keeper.CurrentModule, parameters);
    };

    /**
     * Populates the navigation bar with all of the available modules within the system.
     * Should only be called during the initialization of the system
     */
    Keeper.populateNavigationBar = function () {

        // Dynamically populate navigation bar with links the user is allowed to see.
        var NavigationBarLinks = document.getElementById("NavigationBarLinks");
        NavigationBarLinks.innerHTML = "";
        Keeper.Modules.forEach(function (module) {
            // Check to see if the module says it should be visible in navigation
            if (module.visible_in_nav_bar === true || module.visible_in_nav_bar instanceof Function && module.visible_in_nav_bar() === true) {

                // Create the module button for the navigation
                var link = createElement({
                    elem: 'a',
                    href: '#' + module.id + '/',
                    className: 'navbar-link',
                    textContent: module.title,
                    onclick: function (e) {
                        Keeper.loadModule(module);
                        this.blur();
                        $(document.getElementById("CollapseableNavContainer")).collapse('hide');
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });

                // Put the button in a li and add it to the navigation bar
                createElement({
                    elem: 'li', inside: [link], putIn: NavigationBarLinks
                });

            }
        });
    };

    /**
     * Hides the navbar from view
     */
    Keeper.hideNavBar = function () {
        $(document.getElementById("NavigationBar")).fadeOut();
    };

    /**
     * Re-enables the visibility of the navbar
     */
    Keeper.showNavBar = function () {
        $(document.getElementById("NavigationBar")).fadeIn();
    };

})();