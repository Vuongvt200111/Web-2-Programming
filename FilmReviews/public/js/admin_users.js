/**
 * Admin User Management Block Modal Binding.
 */
(function () {
    'use strict';

    function init() {
        var blockModal = document.getElementById('blockUserModal');
        if (!blockModal) {
            return;
        }

        blockModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) {
                return;
            }
            var userId = button.getAttribute('data-user-id') || '';
            var userName = button.getAttribute('data-user-name') || 'User';
            var idInput = document.getElementById('modal_block_user_id');
            var nameEl = document.getElementById('modal_block_user_name');
            if (idInput) {
                idInput.value = userId;
            }
            if (nameEl) {
                nameEl.textContent = userName;
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
