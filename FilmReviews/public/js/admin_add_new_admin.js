/**
 * Animated Slide Toggle between Create Sub-Admin and Restore Sub-Admin Password panels.
 */
(function () {
    'use strict';

    function init() {
        var stage = document.getElementById('admin-auth-switch-stage');
        var createPanel = document.getElementById('create-admin-panel');
        var restorePanel = document.getElementById('restore-subadmin-panel');
        var showRestoreBtn = document.getElementById('btn-show-restore-subadmin');
        var backCreateBtn = document.getElementById('btn-back-create-admin');
        var cardTitle = document.getElementById('admin-account-card-title');

        if (!stage || !createPanel || !restorePanel) {
            return;
        }

        function setPanelState(panel, active) {
            panel.classList.toggle('is-active', active);
            panel.setAttribute('aria-hidden', active ? 'false' : 'true');
            panel.querySelectorAll('input, button, select, textarea').forEach(function (el) {
                el.tabIndex = active ? 0 : -1;
            });
        }

        function activatePanel(mode) {
            var isRestore = (mode === 'restore');
            setPanelState(createPanel, !isRestore);
            setPanelState(restorePanel, isRestore);

            if (cardTitle) {
                if (isRestore) {
                    cardTitle.textContent = 'RESTORE SUB-ADMIN PASSWORD';
                    cardTitle.classList.remove('text-primary');
                    cardTitle.classList.add('text-warning');
                } else {
                    cardTitle.textContent = 'GRANT NEW ADMIN PRIVILEGES';
                    cardTitle.classList.remove('text-warning');
                    cardTitle.classList.add('text-primary');
                }
            }
        }

        var initialPanel = stage.getAttribute('data-active-panel') || 'create';
        activatePanel(initialPanel);

        if (showRestoreBtn) {
            showRestoreBtn.addEventListener('click', function () {
                activatePanel('restore');
            });
        }

        if (backCreateBtn) {
            backCreateBtn.addEventListener('click', function () {
                activatePanel('create');
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
