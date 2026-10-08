/**
 * Sa'ee realtime pages (admin + client dashboards).
 *
 * Listens on the user's private Pusher channel (App\Realtime\RealtimeHub). When data
 * relevant to the current page changes, the page is re-fetched in the background and
 * the content area is morphed in place (idiomorph) — no reload, no scroll jump.
 *
 * Page state is protected:
 *  - nothing happens while a modal/dropdown is open, the user is typing or selecting text,
 *    or the tab is hidden (the refresh waits until it's safe);
 *  - form fields the user touched are never overwritten;
 *  - attribute / class / style changes made by page JS are kept unless the server changed
 *    the same thing (3-way merge against the originally rendered HTML);
 *  - nodes injected by JS (export bars, maps, charts...), flash messages, <script>s and
 *    anything inside [data-live-ignore] are left alone.
 *
 * Config comes from resources/views/shared/realtime.blade.php (window.SaeeRealtimeConfig).
 */
(function () {
    'use strict';

    var cfg = window.SaeeRealtimeConfig;
    if (!cfg || !window.Idiomorph) return;

    var root = document.querySelector(cfg.root);
    if (!root) return;

    var KEEP_SELECTOR   = '.flash, [data-live-keep]';
    var IGNORE_SELECTOR = '[data-live-ignore], canvas, .leaflet-container';
    var DEBOUNCE_MS     = 600;
    var MIN_INTERVAL_MS = 2000;
    var RETRY_MS        = 1500;

    // ── Server baseline: attributes of every element as rendered by the server ─────────
    var baseline = new WeakMap();

    function snapshot(el) {
        var attrs = {};
        for (var i = 0; i < el.attributes.length; i++) {
            attrs[el.attributes[i].name] = el.attributes[i].value;
        }
        return attrs;
    }

    function captureTree(el) {
        if (el.nodeType !== 1) return;
        baseline.set(el, snapshot(el));
        var all = el.querySelectorAll('*');
        for (var i = 0; i < all.length; i++) baseline.set(all[i], snapshot(all[i]));
    }

    captureTree(root);

    // ── Touched form controls are never overwritten ────────────────────────────────────
    var touched = new WeakSet();
    function markTouched(e) { touched.add(e.target); }
    root.addEventListener('input', markTouched, true);
    root.addEventListener('change', markTouched, true);

    // ── 3-way attribute merge ──────────────────────────────────────────────────────────
    function tokens(str) {
        return (str || '').split(/\s+/).filter(Boolean);
    }

    function mergeClass(base, live, server) {
        var b = tokens(base), l = tokens(live), result = tokens(server);
        l.forEach(function (t) { if (b.indexOf(t) === -1 && result.indexOf(t) === -1) result.push(t); }); // added by JS
        b.forEach(function (t) { if (l.indexOf(t) === -1) result = result.filter(function (x) { return x !== t; }); }); // removed by JS
        return result.length ? result.join(' ') : null;
    }

    function parseStyle(str) {
        var map = {};
        (str || '').split(';').forEach(function (decl) {
            var i = decl.indexOf(':');
            if (i > 0) map[decl.slice(0, i).trim().toLowerCase()] = decl.slice(i + 1).trim();
        });
        return map;
    }

    function mergeStyle(base, live, server) {
        var b = parseStyle(base), l = parseStyle(live), s = parseStyle(server);
        var props = {};
        [b, l, s].forEach(function (m) { Object.keys(m).forEach(function (k) { props[k] = true; }); });
        var out = [];
        Object.keys(props).forEach(function (p) {
            var v = (l[p] !== b[p] && s[p] === b[p]) ? l[p] : s[p];
            if (v !== undefined && v !== '') out.push(p + ': ' + v);
        });
        return out.length ? out.join('; ') : null;
    }

    /** Rewrite the incoming server node's attributes so idiomorph applies the merged result. */
    function mergeAttributes(live, server) {
        var base   = baseline.get(live) || {};
        var fresh  = snapshot(server);
        var names  = {};
        [base, fresh].forEach(function (m) { Object.keys(m).forEach(function (k) { names[k] = true; }); });
        for (var i = 0; i < live.attributes.length; i++) names[live.attributes[i].name] = true;

        Object.keys(names).forEach(function (name) {
            var b = base[name] !== undefined ? base[name] : null;
            var l = live.getAttribute(name);
            var s = fresh[name] !== undefined ? fresh[name] : null;
            var v;

            if (name === 'class')      v = mergeClass(b, l, s);
            else if (name === 'style') v = mergeStyle(b, l, s);
            else                       v = (l !== b && s === b) ? l : s;

            if (v === null) server.removeAttribute(name);
            else            server.setAttribute(name, v);
        });

        baseline.set(live, fresh);
    }

    // ── Morph ──────────────────────────────────────────────────────────────────────────
    var keepSeq = 0;

    function isKept(node) {
        return node.nodeType === 1 && (!baseline.has(node) || node.matches(KEEP_SELECTOR));
    }

    /** Give kept nodes a unique id so idiomorph never pairs them with server nodes. */
    function shieldKeptNodes() {
        var all = root.querySelectorAll('*');
        for (var i = 0; i < all.length; i++) {
            var el = all[i];
            if (isKept(el) && !el.getAttribute('id')) el.setAttribute('id', 'live-keep-' + (++keepSeq));
        }
    }

    function morph(freshRoot) {
        shieldKeptNodes();

        Idiomorph.morph(root, freshRoot, {
            morphStyle: 'innerHTML',
            ignoreActiveValue: true,
            callbacks: {
                beforeNodeAdded: function (node) {
                    return node.nodeName !== 'SCRIPT';
                },
                afterNodeAdded: function (node) {
                    captureTree(node);
                },
                beforeNodeRemoved: function (node) {
                    if (node.nodeType !== 1) return true;
                    if (node.nodeName === 'SCRIPT' || isKept(node)) return false;
                    return true;
                },
                beforeNodeMorphed: function (oldNode, newNode) {
                    if (oldNode.nodeType !== 1) return true;
                    if (oldNode === root) return true;
                    if (oldNode.nodeName === 'SCRIPT') return false;
                    if (oldNode.matches(IGNORE_SELECTOR)) return false;
                    if (touched.has(oldNode)) return false;
                    mergeAttributes(oldNode, newNode);
                    return true;
                },
            },
        });

        document.dispatchEvent(new CustomEvent('realtime:updated'));
    }

    // ── When is it safe to refresh? ────────────────────────────────────────────────────
    function isVisible(el) {
        var cs = window.getComputedStyle(el);
        return cs.display !== 'none' && cs.visibility !== 'hidden' && el.getClientRects().length > 0;
    }

    function overlayOpen() {
        var fixed = document.querySelectorAll('[class*="modal"], [class*="overlay"], [class*="drawer"], [class*="popup"], [class*="dropdown"], dialog[open]');
        for (var i = 0; i < fixed.length; i++) {
            var el = fixed[i];
            if (el.closest('#toastStack')) continue;
            if (window.getComputedStyle(el).position === 'fixed' && isVisible(el)) return true;
        }
        var open = root.querySelectorAll('[class*="dropdown"].open, [class*="dropdown"].show, [class*="dropdown"].is-open, [class*="menu"].open, [class*="menu"].show, [class*="menu"].is-open');
        for (var j = 0; j < open.length; j++) {
            if (isVisible(open[j])) return true;
        }
        return false;
    }

    function userBusy() {
        var a = document.activeElement;
        if (a && a !== document.body && (a.matches('textarea, select, [contenteditable=""], [contenteditable="true"]') ||
            (a.matches('input') && !/^(checkbox|radio|button|submit|reset)$/i.test(a.type)))) {
            return true;
        }
        var sel = window.getSelection && window.getSelection();
        return !!(sel && !sel.isCollapsed && root.contains(sel.anchorNode));
    }

    function blocked() {
        return document.hidden || userBusy() || overlayOpen();
    }

    // ── Scheduling ─────────────────────────────────────────────────────────────────────
    var pending = false, inflight = false, timer = null, lastRun = 0, stopped = false;

    function schedule(delay) {
        if (stopped) return;
        pending = true;
        clearTimeout(timer);
        timer = setTimeout(tryRefresh, delay === undefined ? DEBOUNCE_MS : delay);
    }

    function tryRefresh() {
        if (!pending || inflight || stopped) return;
        if (blocked()) return schedule(RETRY_MS);
        var wait = lastRun + MIN_INTERVAL_MS - Date.now();
        if (wait > 0) return schedule(wait);
        refresh();
    }

    function refresh() {
        pending  = false;
        inflight = true;
        lastRun  = Date.now();

        fetch(window.location.href, {
            credentials: 'same-origin',
            headers: { 'Accept': 'text/html', 'X-Saee-Realtime': '1' },
            cache: 'no-store',
        })
            .then(function (res) {
                // Logged out / redirected elsewhere / error page → stop silently, user will notice on next click.
                if (!res.ok || new URL(res.url).pathname !== window.location.pathname) {
                    stopped = true;
                    return null;
                }
                return res.text();
            })
            .then(function (html) {
                if (!html) return;
                var doc   = new DOMParser().parseFromString(html, 'text/html');
                var fresh = doc.querySelector(cfg.root);
                if (!fresh) return;
                if (blocked()) { pending = true; return; } // user started interacting while we fetched
                morph(fresh);
            })
            .catch(function () { /* network blip — next event will retry */ })
            .then(function () {
                inflight = false;
                if (pending) schedule(RETRY_MS);
            });
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && pending) schedule(200);
    });
    document.addEventListener('focusout', function () {
        if (pending) schedule(RETRY_MS);
    });

    // ── Pusher subscription ────────────────────────────────────────────────────────────
    function relevant(data) {
        if (cfg.types.indexOf('*') !== -1) return true;
        return (data.types || []).some(function (t) { return cfg.types.indexOf(t) !== -1; });
    }

    function connect() {
        var pusher = window.saeePusher;
        if (!pusher) return;

        var channel = pusher.subscribe('private-' + cfg.channel);
        channel.bind('changed', function (data) {
            if (relevant(data || {})) schedule();
        });

        // Catch up on anything missed while the socket was down.
        var wasDisconnected = false;
        pusher.connection.bind('state_change', function (s) {
            if (s.current === 'unavailable' || s.current === 'disconnected') wasDisconnected = true;
            if (s.current === 'connected' && wasDisconnected) {
                wasDisconnected = false;
                schedule();
            }
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', connect);
    else connect();

    window.SaeeRealtime = { refresh: function () { schedule(0); } };
})();
