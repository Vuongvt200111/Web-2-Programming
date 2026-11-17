/**
 * Admin Category Inline Row Editor Toggle.
 */
(function () {
    'use strict';

    function init() {
        document.querySelectorAll('[data-toggle-edit-id]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-toggle-edit-id');
                if (!id) {
                    return;
                }
                var row = document.getElementById('edit-row-' + id);
                if (!row) {
                    return;
                }
                if (row.style.display === 'none' || row.style.display === '') {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
