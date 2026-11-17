/**
 * TAB SESSION MANAGEMENT, ANTI-DUPLICATION & CONCURRENT LOGIN GUARD (RFC 4122)
 * Core Capabilities:
 * 1. Generates and persists a unique UUID v4 in HTML5 sessionStorage.
 * 2. Strict Anti-URL-Cloning: Prevents copying/pasting ?tab_id= into any new or existing tab.
 * 3. Preserves current tab session 100% reliably on F5 refresh, internal link navigation, and form submits.
 * 4. Resolves Duplicate Tab clones via BroadcastChannel peer verification.
 * 5. Tracks live peer tabs via BroadcastChannel (alive_tab_ids) to enforce Single Active Session per Account.
 * 6. Immune to DOM Clobbering (e.g., <select name="action"> or <input name="action"> inside <form>).
 */
(function() {
    'use strict';

    const TAB_KEY = 'tab_session_id';
    const INSTANCE_KEY = 'tab_instance_id';
    const CHANNEL_NAME = 'tab_session_sync_channel';

    // RFC 4122 Compliant UUID v4 Generator
    function generateUUID() {
        if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
            return crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // RFC 4122 UUID Format Validator
    function isValidUUID(uuid) {
        const pattern = /^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i;
        return typeof uuid === 'string' && pattern.test(uuid);
    }

    // Memory-only instance ID unique to this exact window/tab execution context
    const myInstanceId = Math.random().toString(36).substring(2) + Date.now().toString(36);
    const peerAliveTabs = new Set();

    let currentTabId = sessionStorage.getItem(TAB_KEY);
    const url = new URL(window.location.href);
    const urlTab = url.searchParams.get('tab_id');

    // 1. Ensure this tab has its own valid UUID in sessionStorage
    if (!currentTabId || !isValidUUID(currentTabId)) {
        currentTabId = generateUUID();
        sessionStorage.setItem(TAB_KEY, currentTabId);
    }
    sessionStorage.setItem(INSTANCE_KEY, myInstanceId);

    // 2. STRICT ANTI-URL-CLONING ENFORCEMENT:
    // If the URL contains a tab_id that does NOT match this tab's own sessionStorage UUID
    // (whether pasted into a brand new tab OR pasted into an already open tab + F5),
    // immediately block rendering, rewrite the URL to this tab's own UUID, and force reload!
    if (urlTab && urlTab !== currentTabId) {
        if (document.documentElement) {
            document.documentElement.style.visibility = 'hidden';
        }
        url.searchParams.set('tab_id', currentTabId);
        window.location.replace(url.toString());
        return;
    }

    // 3. Anti-Duplication & Live Peer Discovery Protocol via BroadcastChannel
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel(CHANNEL_NAME);

        channel.onmessage = function(event) {
            const msg = event.data;
            if (!msg || typeof msg !== 'object') return;

            // Scenario A: Another tab is asking who owns currentTabId (Duplicate Tab check)
            if (msg.type === 'WHO_OWNS_TAB' && msg.tabId === currentTabId) {
                if (msg.instanceId !== myInstanceId) {
                    channel.postMessage({
                        type: 'DUPLICATE_DETECTED',
                        tabId: currentTabId,
                        ownerInstanceId: myInstanceId,
                        targetInstanceId: msg.instanceId
                    });
                }
            }

            // Scenario B: We have been identified as a duplicated clone
            if (msg.type === 'DUPLICATE_DETECTED' && msg.targetInstanceId === myInstanceId) {
                console.warn('[TabSession] Duplicate Tab detected! Initializing fresh independent Tab Session...');
                if (document.documentElement) {
                    document.documentElement.style.visibility = 'hidden';
                }
                currentTabId = generateUUID();
                sessionStorage.setItem(TAB_KEY, currentTabId);
                sessionStorage.setItem(INSTANCE_KEY, myInstanceId);
                const reloadUrl = new URL(window.location.href);
                reloadUrl.searchParams.set('tab_id', currentTabId);
                window.location.replace(reloadUrl.toString());
                return;
            }

            // Scenario C: Peer discovery for Single Active Account Session enforcement
            if (msg.type === 'PING_PEERS' && msg.requesterInstanceId !== myInstanceId) {
                if (msg.requesterTabId && isValidUUID(msg.requesterTabId)) {
                    peerAliveTabs.add(msg.requesterTabId);
                    updateAliveTabsInputs();
                }
                channel.postMessage({
                    type: 'PONG_PEER',
                    tabId: currentTabId,
                    responderInstanceId: myInstanceId
                });
            }

            if (msg.type === 'PONG_PEER' && msg.responderInstanceId !== myInstanceId) {
                if (msg.tabId && isValidUUID(msg.tabId) && msg.tabId !== currentTabId) {
                    peerAliveTabs.add(msg.tabId);
                    updateAliveTabsInputs();
                }
            }
        };

        // Challenge peers for duplicate tab ID & discover all live open tabs
        channel.postMessage({
            type: 'WHO_OWNS_TAB',
            tabId: currentTabId,
            instanceId: myInstanceId
        });

        channel.postMessage({
            type: 'PING_PEERS',
            requesterTabId: currentTabId,
            requesterInstanceId: myInstanceId
        });
    }

    // 4. Keep Address Bar URL Query Synchronized (?tab_id=...) when URL has no tab_id
    try {
        const currentUrl = new URL(window.location.href);
        const presentTab = currentUrl.searchParams.get('tab_id');
        if (presentTab !== currentTabId) {
            currentUrl.searchParams.set('tab_id', currentTabId);
            window.history.replaceState(null, '', currentUrl.toString());
        }
    } catch(e) {}

    // Safe attribute readers/writers immune to DOM Clobbering (e.g. child <select name="action"> or <input name="method">)
    function getFormAttr(form, attrName) {
        return HTMLFormElement.prototype.getAttribute.call(form, attrName);
    }

    function setFormAttr(form, attrName, attrVal) {
        HTMLFormElement.prototype.setAttribute.call(form, attrName, attrVal);
    }

    // Helper: Update hidden alive_tab_ids inputs in POST forms
    function updateAliveTabsInputs() {
        const aliveList = Array.from(peerAliveTabs).join(',');
        document.querySelectorAll('form').forEach(function(form) {
            const method = (getFormAttr(form, 'method') || 'GET').toUpperCase();
            if (method !== 'POST') return;
            let aliveInput = form.querySelector('input[name="alive_tab_ids"]');
            if (!aliveInput) {
                aliveInput = document.createElement('input');
                aliveInput.type = 'hidden';
                aliveInput.name = 'alive_tab_ids';
                form.appendChild(aliveInput);
            }
            aliveInput.value = aliveList;
        });
    }

    // Synchronize a single <form> safely without DOM Clobbering
    function syncSingleForm(form) {
        if (!form || typeof form.querySelector !== 'function') return;
        try {
            const method = (getFormAttr(form, 'method') || 'GET').toUpperCase();
            const aliveList = Array.from(peerAliveTabs).join(',');

            let input = form.querySelector('input[name="tab_id"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tab_id';
                form.appendChild(input);
            }
            input.value = currentTabId;

            if (method === 'POST') {
                let aliveInput = form.querySelector('input[name="alive_tab_ids"]');
                if (!aliveInput) {
                    aliveInput = document.createElement('input');
                    aliveInput.type = 'hidden';
                    aliveInput.name = 'alive_tab_ids';
                    form.appendChild(aliveInput);
                }
                aliveInput.value = aliveList;

                const rawAction = getFormAttr(form, 'action');
                if (typeof rawAction === 'string' && rawAction.trim() !== '') {
                    const actionUrl = new URL(rawAction, window.location.href);
                    if (actionUrl.origin === window.location.origin) {
                        actionUrl.searchParams.set('tab_id', currentTabId);
                        setFormAttr(form, 'action', actionUrl.toString());
                    }
                }
            } else {
                // For GET forms, remove any stale alive_tab_ids input and ensure action URL is clean
                const staleAlive = form.querySelector('input[name="alive_tab_ids"]');
                if (staleAlive && staleAlive.parentNode) {
                    staleAlive.parentNode.removeChild(staleAlive);
                }
            }
        } catch(e) {}
    }

    // 5. Synchronize all forms (<form>) with currentTabId
    function syncForms() {
        document.querySelectorAll('form').forEach(syncSingleForm);
    }

    // Run form synchronization synchronously on submit capture phase
    document.addEventListener('submit', function(e) {
        if (e.target) {
            syncSingleForm(e.target);
        }
    }, true);

    // Also ensure programmatic form.submit() (e.g. onchange="this.form.submit()") always has tab_id synced
    document.addEventListener('change', function(e) {
        if (e.target && e.target.form) {
            syncSingleForm(e.target.form);
        }
    }, true);

    // 6. Automatic Interception of Internal Links (<a>)
    function attachTabIdToLink(link) {
        if (!link || !link.href) return;
        try {
            const targetUrl = new URL(link.href, window.location.href);
            if (targetUrl.origin === window.location.origin && !targetUrl.pathname.match(/\.(pdf|zip|docx|png|jpg|jpeg|gif)$/i)) {
                if (targetUrl.searchParams.get('tab_id') !== currentTabId) {
                    targetUrl.searchParams.set('tab_id', currentTabId);
                    link.href = targetUrl.toString();
                }
            }
        } catch(e) {}
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link) {
            attachTabIdToLink(link);
        }
    }, true);

    // 7. Enforce rel="noopener" on external / target="_blank" links
    function sanitizeBlankLinks() {
        document.querySelectorAll('a[target="_blank"]').forEach(function(a) {
            const rel = (a.getAttribute('rel') || '').toLowerCase();
            if (!rel.includes('noopener')) {
                a.setAttribute('rel', (rel + ' noopener').trim());
            }
        });
    }

    // 8. Intercept Fetch & XMLHttpRequest to inject X-Tab-Session header
    if (typeof window.fetch === 'function') {
        const originalFetch = window.fetch;
        window.fetch = function(resource, init) {
            init = init || {};
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                init.headers.set('X-Tab-Session', currentTabId);
            } else if (Array.isArray(init.headers)) {
                init.headers.push(['X-Tab-Session', currentTabId]);
            } else {
                init.headers['X-Tab-Session'] = currentTabId;
            }
            return originalFetch.call(this, resource, init);
        };
    }

    // 9. Lifecycle initialization on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            syncForms();
            sanitizeBlankLinks();
            document.querySelectorAll('a').forEach(attachTabIdToLink);
        });
    } else {
        syncForms();
        sanitizeBlankLinks();
        document.querySelectorAll('a').forEach(attachTabIdToLink);
    }

    window.getTabSessionId = function() {
        return currentTabId;
    };
})();
