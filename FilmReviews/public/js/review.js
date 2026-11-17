/**
 * Community Film Review Interactive Composer, Verdict Switcher, Tag Pills & Spoiler Reveal.
 */
(function () {
    'use strict';

    var selectedTags = [];

    function updateVerdictUI() {
        var yesBtn = document.getElementById('verdict-yes-label');
        var noBtn = document.getElementById('verdict-no-label');
        var yesRadio = document.getElementById('verdict-yes');
        if (!yesBtn || !noBtn || !yesRadio) {
            return;
        }
        var isYes = yesRadio.checked;
        yesBtn.classList.toggle('selected', isYes);
        noBtn.classList.toggle('selected', !isYes);
    }

    function updateCharCount(textarea) {
        var counter = document.getElementById('char-counter');
        if (counter && textarea) {
            counter.textContent = textarea.value.length + ' / 1000 characters';
        }
    }

    function init() {
        var textarea = document.getElementById('review-content');
        if (textarea) {
            updateCharCount(textarea);
            textarea.addEventListener('input', function () {
                updateCharCount(textarea);
            });
        }

        var yesRadio = document.getElementById('verdict-yes');
        var noRadio = document.getElementById('verdict-no');
        if (yesRadio && noRadio) {
            yesRadio.addEventListener('change', updateVerdictUI);
            noRadio.addEventListener('change', updateVerdictUI);
            updateVerdictUI();
        }

        var tagsInput = document.getElementById('selected-tags-input');
        document.querySelectorAll('[data-tag-pill]').forEach(function (pill) {
            pill.addEventListener('click', function () {
                var tag = pill.getAttribute('data-tag-pill');
                if (!tag) {
                    return;
                }
                var idx = selectedTags.indexOf(tag);
                if (idx > -1) {
                    selectedTags.splice(idx, 1);
                    pill.classList.remove('active');
                } else {
                    if (selectedTags.length >= 5) {
                        window.alert('You can select a maximum of 5 emotion tags!');
                        return;
                    }
                    selectedTags.push(tag);
                    pill.classList.add('active');
                }
                if (tagsInput) {
                    tagsInput.value = selectedTags.join(', ');
                }
            });
        });

        document.querySelectorAll('.spoiler-content').forEach(function (box) {
            box.addEventListener('click', function () {
                box.classList.toggle('revealed');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
