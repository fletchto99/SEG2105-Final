/**
 *  Author(s)           :  Matt Langlois
 *  File Created        :  December 2015
 *  Details             :  This component is used to display a dismissible alert message at the bottom
 *                         right corner of the screen. Done using Bootstrap Notify library
 */
(function () {
    'use strict';

    /**
     * Binds the window's keyup functionality to listen to the enter key.
     * This enables modal's to take action when the enter key is pressed
     *
     * @param e The key event
     */
    window.onkeyup = function (e) {
        if (e.which == 13 && Keeper.modalOpen == true) {
            //triger the "save" button
            document.getElementById('ModalButton').onclick();
        }
    };

    /**
     * Displays a full screen modal window
     *
     * @param modalTitle The title of the modal window
     * @param modalContent The content of the modal window
     * @param buttonTitle The title of the save button (TODO: ideally this would be an array of objects which would be rendered into buttons)
     * @param onSubmit The action to perform when the save button is pressed
     */
    Keeper.showModal = function (modalTitle, modalContent, buttonTitle, onSubmit) {

        //Prepare the mainmodal
        var modal = $('#MainModal');
        modal.modal({
            show: false
        });

        //update the contents and events of the main modal
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

        //set the modal as visible
        modal.modal('show');
        Keeper.modalOpen = true;
    };

    /**
     * Hides any open modal windows
     */
    Keeper.hideModal = function() {
        $('#MainModal').modal('hide');
        Keeper.modalOpen = false;
    }

})();