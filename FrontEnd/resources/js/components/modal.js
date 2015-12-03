(function () {
    'use strict';

    Keeper.showModal = function (modalTitle, modalContent, buttonTitle, onSubmit) {
        var modal = $('#MainModal');
        modal.modal({
            show: false
        });
        document.getElementById('ModalContent').innerHTML = "";
        document.getElementById('ModalContent').appendChild(modalContent);
        document.getElementById('ModalTitle').textContent = modalTitle;
        document.getElementById('ModalButton').textContent = buttonTitle;
        document.getElementById('ModalButton').onclick = function(event) {
            modal.modal('hide');
            if (typeof(onSubmit) == 'function') {
                onSubmit();
            }
            event.preventDefault();
        };
        modal.modal('show');
    };
})();