/**
 * Global Progressive Enhancement Behaviors (CSP-friendly, zero inline JS handlers needed).
 */
(function () {
    'use strict';

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || !(form instanceof HTMLFormElement)) {
            return;
        }

        var confirmMsg = form.getAttribute('data-confirm');
        if (confirmMsg && !window.confirm(confirmMsg)) {
            event.preventDefault();
            return;
        }

        if (form.getAttribute('data-disable-on-submit') === 'true') {
            var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                var loadingText = form.getAttribute('data-loading-text') || 'Sending...';
                if (submitBtn.tagName === 'BUTTON') {
                    submitBtn.textContent = loadingText;
                } else {
                    submitBtn.value = loadingText;
                }
            }
        }
    });

    document.addEventListener('change', function (event) {
        var target = event.target;
        if (!target) {
            return;
        }

        var labelTargetId = target.getAttribute('data-file-label');
        if (labelTargetId) {
            var labelEl = document.getElementById(labelTargetId);
            if (labelEl) {
                var emptyText = target.getAttribute('data-file-empty') || 'No file chosen';
                labelEl.textContent = (target.files && target.files[0]) ? target.files[0].name : emptyText;
            }
        }

        if (target.getAttribute('data-auto-submit') === 'true' && target.form) {
            target.form.submit();
        }
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-alert-message]');
        if (trigger) {
            event.preventDefault();
            window.alert(trigger.getAttribute('data-alert-message'));
        }
    });

    document.addEventListener('error', function (event) {
        var img = event.target;
        if (img && img.tagName === 'IMG' && img.getAttribute('data-avatar-fallback') === 'true') {
            img.style.display = 'none';
            if (img.nextElementSibling) {
                img.nextElementSibling.style.display = 'inline';
            }
        }
    }, true);
})();
