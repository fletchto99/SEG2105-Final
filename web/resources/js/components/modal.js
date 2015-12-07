(function () {
    'use strict';

    window.onkeyup = function (e) {
        if (e.which == 13 && Keeper.modalOpen == true) {
            document.getElementById('ModalButton').onclick();
        }
    };

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
            var dismiss = true;

            if (typeof(onSubmit) == 'function') {
                dismiss = onSubmit();
            }
            dismiss && modal.modal('hide');
            if (event) {
                event.preventDefault();
            }
        };
        modal.modal('show');
        Keeper.modalOpen = true;
    };

    Keeper.hideModal = function() {
        $('#MainModal').modal('hide');
        Keeper.modalOpen = false;
    }

})();