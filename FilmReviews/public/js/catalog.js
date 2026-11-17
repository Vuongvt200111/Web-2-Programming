/**
 * Catalog Sort Control Dynamic Width Synchronization.
 */
(function () {
    'use strict';

    function autoResizeSortSelect(el) {
        if (!el) {
            return;
        }
        var span = document.createElement('span');
        span.style.visibility = 'hidden';
        span.style.position = 'absolute';
        span.style.whiteSpace = 'nowrap';
        span.style.font = window.getComputedStyle(el).font;
        var opt = el.options[el.selectedIndex];
        span.textContent = opt ? opt.text : el.value;
        document.body.appendChild(span);
        var w = Math.ceil(span.getBoundingClientRect().width);
        document.body.removeChild(span);
        el.style.width = (w + 64) + 'px';
    }

    function init() {
        var sel = document.getElementById('sortSelectControl');
        if (!sel) {
            return;
        }
        autoResizeSortSelect(sel);
        sel.addEventListener('change', function () {
            autoResizeSortSelect(sel);
            if (sel.form) {
                sel.form.submit();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
