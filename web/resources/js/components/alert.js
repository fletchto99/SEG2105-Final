/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Details             :  This component is used to display a dismissible alert message at the bottom
 *                         right corner of the screen. Done using Bootstrap Notify library
 */
(function () {
    'use strict';

    /**
     * Displays an alert bubble at the bottom right corner of the screen
     *
     * @param message The message to display
     * @param style The style of the alert, according to bootstrap's themes
     * @param dismissAfter The amount of time before the alert automatically dismisses, if undefined then it will stay open until the user dismisses the alert
     */
    Keeper.showAlert = function(message, style, dismissAfter) {
        $.notify({
            message: message
        },{
            type: style,
            placement: {
                from: "bottom",
                align: "right"
            },
            animate: {
                enter: 'animated fadeInUp',
                exit: 'animated rollOut'
            },
            z_index: 99999999,
            delay: dismissAfter || 0
        });
    };

})();