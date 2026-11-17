/**
 * Profile Dashboard Navigation, Accordion Menu, and My Films Status/Search Filters.
 */
(function () {
    'use strict';

    var activeMyFilmsStatus = 'all';

    function toggleMyAccountMenu() {
        var submenu = document.getElementById('my-account-submenu');
        var arrow = document.getElementById('my-account-arrow');
        if (!submenu) {
            return;
        }
        submenu.classList.toggle('collapsed');
        if (arrow) {
            arrow.textContent = submenu.classList.contains('collapsed') ? '\u25B8' : '\u25BE';
        }
    }

    function switchProfileSection(section) {
        var sectionMap = {
            profile: { panel: 'section-profile-setting', nav: 'nav-btn-profile' },
            password: { panel: 'section-change-password', nav: 'nav-btn-password' },
            films: { panel: 'section-my-films', nav: 'nav-btn-films' }
        };
        var targetKey = sectionMap[section] ? section : 'profile';

        Object.keys(sectionMap).forEach(function (key) {
            var cfg = sectionMap[key];
            var panel = document.getElementById(cfg.panel);
            var nav = document.getElementById(cfg.nav);
            var isMatch = (key === targetKey);
            if (panel) {
                panel.style.display = isMatch ? 'block' : 'none';
            }
            if (nav) {
                nav.classList.toggle('active', isMatch);
            }
        });
    }

    function filterMyFilms() {
        var searchEl = document.getElementById('my-films-search');
        var query = searchEl ? searchEl.value.trim().toLowerCase() : '';
        var items = document.querySelectorAll('#my-films-list .my-film-item');
        var visibleCount = 0;

        items.forEach(function (item) {
            var st = item.getAttribute('data-status') || '';
            var haystack = item.getAttribute('data-search') || '';
            var statusOk = (activeMyFilmsStatus === 'all' || st === activeMyFilmsStatus);
            var queryOk = (query === '' || haystack.indexOf(query) !== -1);
            var show = statusOk && queryOk;
            item.style.display = show ? 'flex' : 'none';
            if (show) {
                visibleCount++;
            }
        });

        var emptyEl = document.getElementById('my-films-empty-filter');
        if (emptyEl) {
            emptyEl.style.display = (items.length > 0 && visibleCount === 0) ? 'block' : 'none';
        }
    }

    function init() {
        var toggleBtn = document.getElementById('my-account-toggle-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleMyAccountMenu);
        }

        document.querySelectorAll('[data-profile-section]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchProfileSection(btn.getAttribute('data-profile-section'));
            });
        });

        document.querySelectorAll('#my-films-status-pills [data-status-filter]').forEach(function (pill) {
            pill.addEventListener('click', function () {
                activeMyFilmsStatus = pill.getAttribute('data-status-filter') || 'all';
                document.querySelectorAll('#my-films-status-pills .my-films-filter-pill').forEach(function (p) {
                    p.classList.toggle('active', p === pill);
                });
                filterMyFilms();
            });
        });

        var searchInput = document.getElementById('my-films-search');
        if (searchInput) {
            searchInput.addEventListener('input', filterMyFilms);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
