(function () {
    'use strict';

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
            delay: dismissAfter
        });
    };

})();