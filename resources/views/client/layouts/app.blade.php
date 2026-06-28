<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Sa'ee Client Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red:      #dc2626; --red-dark: #991b1b; --red-deep: #7f1d1d;
            --red-lt:   #ef4444; --red-glow: rgba(220,38,38,.35);
            --bg:       #080c1e; --bg-2: #0c1230;
            --sidebar:  #060917;
            --card:     rgba(12,18,48,.8);
            --bdr:      rgba(255,255,255,.06);
            --bdr-red:  rgba(220,38,38,.15);
            --text:     #f1f5f9; --text-sub: #94a3b8; --text-dim: #475569;
            --in-bg:    rgba(255,255,255,.04); --in-bdr: rgba(255,255,255,.08);
            --success:  #22c55e; --warning: #f59e0b; --info: #3b82f6;
        }
        html { height: 100%; }
        body { height: 100%; font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); }

        .shell { display: flex; height: 100vh; overflow: hidden; }

        /* ── Sidebar ─────────────────────────────────────── */
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: var(--sidebar);
            border-right: 1px solid var(--bdr);
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .sidebar-logo {
            padding: 20px 16px 18px;
            border-bottom: 1px solid var(--bdr);
            display: flex; align-items: center; gap: 10px; flex-shrink: 0;
        }
        .sidebar-logo-text { font-size: .75rem; font-weight: 700; color: rgba(255,255,255,.35); letter-spacing: .14em; text-transform: uppercase; }
        .sidebar-nav { flex: 1; padding: 12px 8px; overflow-y: auto; }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 2px; }
        .nav-label { font-size: .62rem; font-weight: 700; color: var(--text-dim); letter-spacing: .14em; text-transform: uppercase; padding: 12px 10px 5px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 11px; border-radius: 9px;
            color: var(--text-sub); font-size: .84rem; font-weight: 500;
            text-decoration: none; margin-bottom: 1px;
            transition: background .13s, color .13s;
        }
        .nav-item:hover { background: rgba(255,255,255,.04); color: var(--text); }
        .nav-item.active { background: rgba(220,38,38,.11); color: #fca5a5; border: 1px solid rgba(220,38,38,.14); }
        .nav-item.active svg { opacity: 1; color: var(--red-lt); }
        .nav-item svg { flex-shrink: 0; opacity: .55; }
        .sidebar-foot { padding: 12px 8px; border-top: 1px solid var(--bdr); flex-shrink: 0; }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 11px; border-radius: 10px;
            background: rgba(255,255,255,.025); border: 1px solid var(--bdr); margin-bottom: 6px;
        }
        .u-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--red-dark), var(--red)); display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; color: white; flex-shrink: 0; }
        .u-info { flex: 1; overflow: hidden; }
        .u-name { font-size: .8rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .u-role { font-size: .67rem; color: var(--red-lt); font-weight: 500; }
        .logout-btn { display: flex; align-items: center; gap: 9px; width: 100%; padding: 8px 11px; border-radius: 8px; border: none; background: none; color: var(--text-sub); font-size: .83rem; font-weight: 500; font-family: inherit; cursor: pointer; transition: background .13s, color .13s; text-decoration: none; }
        .logout-btn:hover { background: rgba(220,38,38,.08); color: #fca5a5; }
        .logout-btn svg { opacity: .6; flex-shrink: 0; }

        /* ── Main ────────────────────────────────────────── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        /* ── Topbar ──────────────────────────────────────── */
        .topbar { height: 58px; flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid var(--bdr); background: rgba(6,9,23,.7); backdrop-filter: blur(12px); }
        .topbar-left { display: flex; align-items: center; gap: 10px; overflow: hidden; }
        .topbar-title { font-size: .98rem; font-weight: 700; white-space: nowrap; }
        .topbar-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .icon-btn { width: 35px; height: 35px; border-radius: 9px; background: rgba(255,255,255,.04); border: 1px solid var(--bdr); display: flex; align-items: center; justify-content: center; color: var(--text-sub); cursor: pointer; transition: background .13s, color .13s; position: relative; text-decoration: none; }
        .icon-btn:hover { background: rgba(255,255,255,.07); color: var(--text); }
        .notif-badge { position: absolute; top: -4px; right: -4px; background: var(--red); color: white; font-size: .6rem; font-weight: 700; border-radius: 100px; padding: 1px 4px; min-width: 16px; text-align: center; line-height: 14px; box-shadow: 0 0 0 2px var(--sidebar); }
        .sidebar-badge {
            background: var(--red);
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 14px;
            display: inline-block;
            margin-left: 6px;
        }
        html[dir="rtl"] .sidebar-badge {
            margin-left: 0;
            margin-right: 6px;
        }

        /* ── Content ─────────────────────────────────────── */
        .content { flex: 1; overflow-y: auto; padding: 24px 26px; }
        .content::-webkit-scrollbar { width: 5px; }
        .content::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 3px; }

        /* ── Flash messages ──────────────────────────────── */
        .flash { border-radius: 11px; padding: 12px 16px; font-size: .85rem; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; animation: fu .4s both; }
        .flash-ok  { background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.2); color: #86efac; }
        .flash-err { background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.18); color: #fca5a5; }
        .flash-info{ background: rgba(59,130,246,.08); border: 1px solid rgba(59,130,246,.2); color: #93c5fd; }
        @keyframes fu { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }

        /* ── Page header ─────────────────────────────────── */
        .page-hd { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 22px; gap: 16px; flex-wrap: wrap; animation: fu .45s both; }
        .page-hd-left h1 { font-size: 1.35rem; font-weight: 800; letter-spacing: -.025em; }
        .page-hd-left p  { color: var(--text-sub); font-size: .82rem; margin-top: 3px; }
        .page-hd-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        /* ── Buttons ─────────────────────────────────────── */
        .btn-primary { display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, var(--red-deep), var(--red)); color: white; border: none; border-radius: 9px; padding: 9px 18px; font-size: .85rem; font-weight: 600; font-family: inherit; cursor: pointer; text-decoration: none; white-space: nowrap; transition: opacity .15s, transform .12s, box-shadow .15s; box-shadow: 0 3px 14px rgba(220,38,38,.28); }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }
        .btn-secondary { display: inline-flex; align-items: center; gap: 7px; background: rgba(255,255,255,.05); color: var(--text-sub); border: 1px solid var(--bdr); border-radius: 9px; padding: 9px 18px; font-size: .85rem; font-weight: 500; font-family: inherit; cursor: pointer; text-decoration: none; white-space: nowrap; transition: background .13s, color .13s; }
        .btn-secondary:hover { background: rgba(255,255,255,.08); color: var(--text); }
        .btn-danger { display: inline-flex; align-items: center; gap: 7px; background: rgba(220,38,38,.12); color: #f87171; border: 1px solid rgba(220,38,38,.22); border-radius: 9px; padding: 9px 18px; font-size: .85rem; font-weight: 600; font-family: inherit; cursor: pointer; text-decoration: none; transition: background .13s; }
        .btn-danger:hover { background: rgba(220,38,38,.22); }

        /* ── Cards ───────────────────────────────────────── */
        .card { background: var(--card); border: 1px solid var(--bdr); border-radius: 14px; padding: 20px 22px; backdrop-filter: blur(8px); }

        /* ── Filter bar ──────────────────────────────────── */
        .filter-bar { display: flex; align-items: center; gap: 10px; background: var(--card); border: 1px solid var(--bdr); border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; flex-wrap: wrap; backdrop-filter: blur(8px); }
        .filter-search-wrap { position: relative; flex: 1; min-width: 180px; }
        .filter-search-wrap svg { position: absolute; left: 10px; top: 50%; translate: 0 -50%; color: rgba(255,255,255,.22); pointer-events: none; }
        .filter-input { width: 100%; padding: 8px 12px 8px 34px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 8px; color: var(--text); font-size: .84rem; font-family: inherit; outline: none; transition: border-color .2s; }
        .filter-input:focus { border-color: rgba(220,38,38,.35); }
        .filter-select { padding: 8px 28px 8px 10px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 8px; color: var(--text); font-size: .84rem; font-family: inherit; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' fill='none' viewBox='0 0 24 24'%3E%3Cpath stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; }
        .filter-select:focus { border-color: rgba(220,38,38,.35); }
        .filter-select option { background: #0c1230; }

        /* ── Table ───────────────────────────────────────── */
        .table-card { background: var(--card); border: 1px solid var(--bdr); border-radius: 14px; overflow: hidden; backdrop-filter: blur(8px); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: rgba(255,255,255,.025); border-bottom: 1px solid var(--bdr); padding: 11px 16px; text-align: left; font-size: .69rem; font-weight: 700; color: var(--text-dim); letter-spacing: .09em; text-transform: uppercase; white-space: nowrap; }
        tbody td { padding: 13px 16px; border-bottom: 1px solid rgba(255,255,255,.03); font-size: .84rem; color: var(--text); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: rgba(255,255,255,.018); }
        .cell-main { font-weight: 600; font-size: .85rem; }
        .cell-sub { font-size: .73rem; color: var(--text-dim); margin-top: 1px; }

        /* ── Badges ──────────────────────────────────────── */
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 100px; font-size: .69rem; font-weight: 600; white-space: nowrap; }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge-pending  { background: rgba(245,158,11,.1);  color: #fbbf24; }
        .badge-active   { background: rgba(34,197,94,.1);   color: #4ade80; }
        .badge-info     { background: rgba(59,130,246,.1);  color: #60a5fa; }
        .badge-success  { background: rgba(34,197,94,.1);   color: #4ade80; }
        .badge-danger   { background: rgba(220,38,38,.1);   color: #f87171; }
        .badge-neutral  { background: rgba(148,163,184,.1); color: #94a3b8; }
        .badge-cod      { background: rgba(245,158,11,.1);  color: #fbbf24; }
        .badge-prepaid  { background: rgba(34,197,94,.1);   color: #4ade80; }

        /* ── Form elements ───────────────────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.5); letter-spacing: .07em; text-transform: uppercase; margin-bottom: 7px; }
        .form-input { width: 100%; padding: 11px 13px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 9px; color: var(--text); font-size: .88rem; font-family: inherit; outline: none; transition: border-color .2s; }
        .form-input:focus { border-color: rgba(220,38,38,.4); }
        .form-input.has-error { border-color: rgba(220,38,38,.45); }
        .form-select { width: 100%; padding: 11px 32px 11px 13px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 9px; color: var(--text); font-size: .88rem; font-family: inherit; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' viewBox='0 0 24 24'%3E%3Cpath stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
        .form-select:focus { border-color: rgba(220,38,38,.4); }
        .form-select option { background: #0c1230; }
        .form-textarea { width: 100%; padding: 11px 13px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 9px; color: var(--text); font-size: .88rem; font-family: inherit; outline: none; resize: vertical; min-height: 90px; transition: border-color .2s; }
        .form-textarea:focus { border-color: rgba(220,38,38,.4); }
        .form-error { font-size: .77rem; color: #f87171; margin-top: 5px; }
        .form-hint  { font-size: .77rem; color: var(--text-dim); margin-top: 5px; }

        /* ── Pagination ──────────────────────────────────── */
        .pagination { display: flex; align-items: center; gap: 6px; padding: 14px 16px; border-top: 1px solid var(--bdr); }
        .pagination a, .pagination span { padding: 5px 11px; border-radius: 7px; font-size: .8rem; font-weight: 500; text-decoration: none; color: var(--text-sub); background: rgba(255,255,255,.03); border: 1px solid var(--bdr); }
        .pagination a:hover { background: rgba(255,255,255,.07); color: var(--text); }
        .pagination .active span { background: rgba(220,38,38,.15); color: #fca5a5; border-color: rgba(220,38,38,.2); }
        .pagination .disabled span { opacity: .35; cursor: default; }

        /* ── Act buttons ─────────────────────────────────── */
        .act-btns { display: flex; align-items: center; gap: 4px; }
        .act-btn { width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: background .13s; text-decoration: none; }
        .act-btn-view { background: rgba(59,130,246,.1); color: #60a5fa; }
        .act-btn-view:hover { background: rgba(59,130,246,.2); }
        .act-btn-del  { background: rgba(220,38,38,.1); color: #f87171; }
        .act-btn-del:hover  { background: rgba(220,38,38,.2); }

        /* ── Notifications dropdown ──────────────────────── */
        .notif-panel { position: absolute; top: calc(100% + 8px); right: 0; width: 340px; background: #0c1230; border: 1px solid var(--bdr); border-radius: 14px; box-shadow: 0 16px 48px rgba(0,0,0,.6); z-index: 200; overflow: hidden; display: none; }
        .notif-panel.open { display: block; animation: fu .25s both; }
        .notif-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px 12px; border-bottom: 1px solid var(--bdr); }
        .notif-head-title { font-size: .88rem; font-weight: 700; }
        .notif-mark-all { font-size: .76rem; color: rgba(220,38,38,.8); cursor: pointer; background: none; border: none; font-family: inherit; }
        .notif-mark-all:hover { color: var(--red-lt); }
        .notif-list { max-height: 340px; overflow-y: auto; }
        .notif-item { display: flex; gap: 11px; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.03); cursor: pointer; transition: background .12s; }
        .notif-item:hover { background: rgba(255,255,255,.025); }
        .notif-item.unread { background: rgba(220,38,38,.03); }
        .notif-item.unread .notif-item-title { color: var(--text); }
        .notif-dot-unread { width: 7px; height: 7px; border-radius: 50%; background: var(--red); flex-shrink: 0; margin-top: 5px; }
        .notif-dot-read   { width: 7px; height: 7px; border-radius: 50%; background: transparent; flex-shrink: 0; margin-top: 5px; }
        .notif-item-title { font-size: .83rem; font-weight: 600; color: var(--text-sub); }
        .notif-item-msg   { font-size: .77rem; color: var(--text-dim); margin-top: 2px; line-height: 1.4; }
        .notif-item-time  { font-size: .7rem; color: var(--text-dim); margin-top: 4px; }
        .notif-footer { padding: 10px 16px; border-top: 1px solid var(--bdr); text-align: center; }
        .notif-footer a { font-size: .8rem; color: rgba(220,38,38,.8); text-decoration: none; }
        .notif-footer a:hover { color: var(--red-lt); }
        .notif-empty { padding: 28px 16px; text-align: center; color: var(--text-dim); font-size: .84rem; }

        /* ── Order status map ────────────────────────────── */
        .status-pending   { color: #fbbf24; background: rgba(245,158,11,.1); }
        .status-picked_up { color: #60a5fa; background: rgba(59,130,246,.1); }
        .status-delivered { color: #4ade80; background: rgba(34,197,94,.1); }
        .status-rejected  { color: #f87171; background: rgba(220,38,38,.1); }
        .status-returned  { color: #94a3b8; background: rgba(148,163,184,.1); }
        .status-cancelled { color: #6b7280; background: rgba(107,114,128,.1); }

        /* ── Grid ────────────────────────────────────────── */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        @media (max-width: 900px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }

        @keyframes slide-in { from { opacity:0; transform: translateX(-16px); } to { opacity:1; transform: translateX(0); } }

        /* ─── Light Mode Overrides ───────────────────────── */
        html.light-theme {
            --bg:       #f8fafc; --bg-2: #f1f5f9;
            --sidebar:  #ffffff;
            --card:     rgba(255, 255, 255, 0.75);
            --bdr:      rgba(15, 23, 42, 0.08);
            --bdr-red:  rgba(220, 38, 38, 0.1);
            --text:     #0f172a; --text-sub: #475569; --text-dim: #64748b;
            --in-bg:    rgba(15, 23, 42, 0.02); --in-bdr: rgba(15, 23, 42, 0.08);
        }
        html.light-theme body { background: var(--bg); color: var(--text); }
        html.light-theme .topbar { background: rgba(255, 255, 255, 0.7); border-bottom: 1px solid var(--bdr); }
        html.light-theme .icon-btn { background: rgba(15, 23, 42, 0.04); border-color: var(--bdr); }
        html.light-theme .icon-btn:hover { background: rgba(15, 23, 42, 0.07); }
        html.light-theme .nav-item:hover { background: rgba(15, 23, 42, 0.04); color: var(--text); }
        html.light-theme .sidebar-user { background: rgba(15, 23, 42, 0.02); }
        html.light-theme .notif-panel { background: #ffffff; color: #0f172a; border-color: var(--bdr); }
        html.light-theme .filter-select option { background: #ffffff; color: #0f172a; }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .sidebar { border-left: 1px solid var(--bdr); border-right: none; }
        html[dir="rtl"] .filter-search-wrap svg { right: 11px; left: auto; }
        html[dir="rtl"] .filter-input { padding: 8px 36px 8px 12px; }
        html[dir="rtl"] .filter-select { background-position: left 9px center; padding-left: 30px; padding-right: 11px; }
        html[dir="rtl"] .form-select { background-position: left 11px center; padding-left: 32px; padding-right: 13px; }
        html[dir="rtl"] thead th { text-align: right; }
        html[dir="rtl"] .notif-panel { right: auto; left: 0; }

        /* Table Sorting Styles */
        thead th.sortable-th {
            cursor: pointer;
            position: relative;
            user-select: none;
            transition: background 0.15s, color 0.15s;
        }
        thead th.sortable-th:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
        }
        thead th.sortable-th::after {
            content: ' ↕';
            opacity: 0.25;
            margin-left: 6px;
            font-size: 0.8em;
            display: inline-block;
            transition: opacity 0.15s, transform 0.15s;
        }
        thead th.sortable-th.sort-asc::after {
            content: ' ▲';
            opacity: 0.9;
            color: var(--red-lt);
        }
        thead th.sortable-th.sort-desc::after {
            content: ' ▼';
            opacity: 0.9;
            color: var(--red-lt);
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="shell">
    {{-- ═══════════ SIDEBAR ════════════ --}}
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img id="logoDark"  src="{{ asset('saee_logo_dark.png') }}"  alt="Sa'ee" width="50" height="50" style="object-fit:contain;border-radius:7px;">
            <img id="logoLight" src="{{ asset('saee_logo_light.png') }}" alt="Sa'ee" width="50" height="50" style="object-fit:contain;border-radius:7px;display:none;">
        </div>

        <nav class="sidebar-nav">
            <span class="nav-label">{{ __('Main') }}</span>

            <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ __('Home') }}
            </a>

            <a href="{{ route('client.orders.index') }}" class="nav-item {{ request()->routeIs('client.orders.*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                {{ __('Orders') }}
            </a>

            <span class="nav-label" style="margin-top:8px;">{{ __('Support & Finance') }}</span>

            <a href="{{ route('client.support.index') }}" class="nav-item {{ request()->routeIs('client.support.*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12c0-4.97 4.03-9 9-9s9 4.03 9 9" />
                    <rect x="1" y="10" width="3" height="6" rx="1.5" />
                    <rect x="20" y="10" width="3" height="6" rx="1.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 14c0 3 2.5 5 5.5 5" />
                </svg>
                <span style="flex: 1;">{{ __('Support') }}</span>
                @if(isset($unreadSupportMessagesCount) && $unreadSupportMessagesCount > 0)
                    <span class="sidebar-badge">{{ $unreadSupportMessagesCount }}</span>
                @endif
            </a>



            <a href="{{ route('client.financials.invoices') }}" class="nav-item {{ request()->routeIs('client.financials.invoices*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('Payout Invoices') }}
            </a>

            <a href="{{ route('client.billing.index') }}" class="nav-item {{ request()->routeIs('client.billing.*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('Billing') }}
            </a>

            <a href="{{ route('client.reports.index') }}" class="nav-item {{ request()->routeIs('client.reports.*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                {{ __('Reports') }}
            </a>

            <span class="nav-label" style="margin-top:8px;">{{ __('Account') }}</span>

            @if(auth()->user()->isClientMaster())
            <a href="{{ route('client.users.index') }}" class="nav-item {{ request()->routeIs('client.users.*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                {{ __('Team') }}
            </a>
            @endif

            <a href="{{ route('client.account.index') }}" class="nav-item {{ request()->routeIs('client.account.*') ? 'active' : '' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('Account') }}
            </a>
        </nav>

        <div class="sidebar-foot">
            <div class="sidebar-user">
                <div class="u-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="u-info">
                    <div class="u-name">{{ auth()->user()->name }}</div>
                    <div class="u-role">{{ auth()->user()->role === 'client_master' ? __('Account Owner') : __('Team Member') }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('client.logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    {{ __('Sign Out') }}
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════════ MAIN ════════════ --}}
    <div class="main">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="topbar-right">
                {{-- Language Switcher --}}
                @if(app()->getLocale() === 'en')
                    <a href="{{ route('lang.switch', 'ar') }}" class="icon-btn" title="تغيير اللغة إلى العربية" style="text-decoration:none;font-weight:700;font-size:.8rem;">عربي</a>
                @else
                    <a href="{{ route('lang.switch', 'en') }}" class="icon-btn" title="Switch to English" style="text-decoration:none;font-weight:700;font-size:.8rem;">EN</a>
                @endif

                {{-- Theme Switcher --}}
                <button class="icon-btn" id="themeToggler" onclick="toggleTheme()" title="{{ __('Toggle Theme') }}">
                    <svg id="themeMoon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                    <svg id="themeSun" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m8.942-8.942h-2.25M4.313 12H2.063m15.122-6.938l-1.591 1.591M6.818 17.182l-1.591 1.591m12.94 0l-1.591-1.591M6.818 6.818L5.227 5.227M12 9a3 3 0 100 6 3 3 0 000-6z"/></svg>
                </button>

                {{-- Notifications --}}
                <div style="position:relative;" id="notifWrap">
                    <button class="icon-btn" id="notifBtn" onclick="toggleNotifPanel()" aria-label="Notifications">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="notif-badge" id="notifCount" style="display:none;">0</span>
                    </button>

                    <div class="notif-panel" id="notifPanel">
                        <div class="notif-head">
                            <span class="notif-head-title">{{ __('Notifications') }}</span>
                            <button class="notif-mark-all" onclick="markAllRead()">{{ __('Mark all read') }}</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty">{{ __('Loading…') }}</div>
                        </div>
                        <div class="notif-footer">
                            <a href="{{ route('client.notifications.index') }}">{{ __('View all notifications') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <div class="content">
            @if(session('success'))
                <div class="flash flash-ok">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flash flash-err">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="flash flash-err">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script>
// ── Notifications panel ───────────────────────────────
function toggleNotifPanel() {
    const panel = document.getElementById('notifPanel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) loadNotifications();
}
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notifWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('notifPanel').classList.remove('open');
    }
});

function loadNotifications() {
    fetch('{{ route("client.notifications.index") }}?json=1', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(data => {
        renderNotifications(data.notifications || []);
    }).catch(() => {
        document.getElementById('notifList').innerHTML = '<div class="notif-empty">{{ __("Could not load notifications.") }}</div>';
    });
}

function renderNotifications(items) {
    const list = document.getElementById('notifList');
    if (!items.length) { list.innerHTML = '<div class="notif-empty">{{ __("No notifications yet.") }}</div>'; return; }
    list.innerHTML = items.slice(0,10).map(n => `
        <div class="notif-item ${!n.read_at ? 'unread' : ''}" onclick="markOneRead(${n.id}, this)">
            <div class="${!n.read_at ? 'notif-dot-unread' : 'notif-dot-read'}"></div>
            <div>
                <div class="notif-item-title">${escHtml(n.title || '')}</div>
                <div class="notif-item-msg">${escHtml(n.message || '')}</div>
                <div class="notif-item-time">${n.created_at}</div>
            </div>
        </div>
    `).join('');
}

function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function markOneRead(id, el) {
    fetch(`{{ url('client/notifications') }}/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    });
    el.classList.remove('unread');
    el.querySelector('[class^="notif-dot"]').className = 'notif-dot-read';
    updateBadge();
}

function markAllRead() {
    fetch('{{ route("client.notifications.read-all") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    });
    document.querySelectorAll('.notif-item.unread').forEach(el => {
        el.classList.remove('unread');
        el.querySelector('[class^="notif-dot"]').className = 'notif-dot-read';
    });
    hideBadge();
}

function updateBadge() {
    const unread = document.querySelectorAll('.notif-item.unread').length;
    const badge = document.getElementById('notifCount');
    if (unread > 0) { badge.textContent = unread; badge.style.display = 'block'; }
    else hideBadge();
}

function hideBadge() {
    const badge = document.getElementById('notifCount');
    badge.style.display = 'none';
}

// ── Theme toggle ─────────────────────────────────────
function toggleTheme() {
    const isLight = document.documentElement.classList.toggle('light-theme');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    updateThemeIcons();
}
function updateThemeIcons() {
    const isLight = document.documentElement.classList.contains('light-theme');
    const sun  = document.getElementById('themeSun');
    const moon = document.getElementById('themeMoon');
    if (sun && moon) {
        sun.style.display  = isLight ? 'none'  : 'block';
        moon.style.display = isLight ? 'block' : 'none';
    }
    const logoDark  = document.getElementById('logoDark');
    const logoLight = document.getElementById('logoLight');
    if (logoDark && logoLight) {
        logoDark.style.display  = isLight ? 'none'  : 'block';
        logoLight.style.display = isLight ? 'block' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', updateThemeIcons);

// Load unread count on page load
fetch('{{ route("client.notifications.unread") }}')
    .then(r => r.json())
    .then(d => {
        if (d.count > 0) {
            const badge = document.getElementById('notifCount');
            badge.textContent = d.count;
            badge.style.display = 'block';
        }
    }).catch(() => {});

// Client-side table sorter
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('table').forEach(function (table) {
        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        headers.forEach(function (th, index) {
            const text = th.textContent.trim();
            if (!text || th.classList.contains('no-sort') || th.querySelector('input[type="checkbox"]')) {
                return;
            }

            th.classList.add('sortable-th');

            th.addEventListener('click', function () {
                const isAsc = th.classList.contains('sort-asc');
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

                const direction = isAsc ? -1 : 1;
                th.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort(function (rowA, rowB) {
                    const cellA = rowA.children[index];
                    const cellB = rowB.children[index];
                    if (!cellA || !cellB) return 0;

                    const valA = getCellValue(cellA);
                    const valB = getCellValue(cellB);

                    if (!isNaN(valA) && !isNaN(valB) && valA !== '' && valB !== '') {
                        return (parseFloat(valA) - parseFloat(valB)) * direction;
                    }

                    const dateA = Date.parse(valA);
                    const dateB = Date.parse(valB);
                    if (!isNaN(dateA) && !isNaN(dateB)) {
                        return (dateA - dateB) * direction;
                    }

                    return valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' }) * direction;
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });

    function getCellValue(cell) {
        const input = cell.querySelector('input, select');
        if (input) {
            if (input.type === 'checkbox') {
                return input.checked ? '1' : '0';
            }
            return input.value;
        }
        return cell.getAttribute('data-sort') || cell.textContent.trim();
    }

    // Universal CSV Export
    const tables = document.querySelectorAll('table');
    tables.forEach(function(table, index) {
        if (table.closest('.timeline') || table.offsetParent === null) {
            return;
        }
        
        const bodyRows = table.querySelectorAll('tbody tr');
        if (bodyRows.length === 0) return;
        if (bodyRows.length === 1 && (bodyRows[0].textContent.includes('No ') || bodyRows[0].textContent.includes('Empty') || bodyRows[0].textContent.includes('No data'))) {
            return;
        }
        
        const headers = table.querySelectorAll('thead th');
        if (headers.length < 2) return;

        if (table.dataset.hasExportButton) return;
        table.dataset.hasExportButton = 'true';

        const bar = document.createElement('div');
        bar.className = 'table-export-bar';
        bar.style.display = 'flex';
        bar.style.justifyContent = 'flex-end';
        bar.style.marginBottom = '12px';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-secondary';
        btn.style.padding = '6px 12px';
        btn.style.fontSize = '0.78rem';
        btn.style.display = 'inline-flex';
        btn.style.alignItems = 'center';
        btn.style.gap = '6px';
        btn.style.cursor = 'pointer';
        btn.style.borderRadius = '8px';
        btn.style.fontWeight = '500';
        btn.innerHTML = `
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Table (CSV)
        `;

        btn.addEventListener('click', function() {
            const csv = [];
            const rows = table.querySelectorAll('tr');

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].offsetParent === null) continue;

                const row = [];
                const cols = rows[i].querySelectorAll('th, td');

                for (let j = 0; j < cols.length; j++) {
                    const col = cols[j];
                    
                    if (col.classList.contains('actions') || col.classList.contains('col-actions') || col.querySelector('button') || col.querySelector('input[type="checkbox"]')) {
                        const select = col.querySelector('select');
                        const input = col.querySelector('input:not([type="checkbox"]):not([type="hidden"])');
                        if (select) {
                            row.push(cleanCSVValue(select.options[select.selectedIndex]?.text || ''));
                        } else if (input) {
                            row.push(cleanCSVValue(input.value));
                        } else {
                            continue;
                        }
                    } else {
                        const select = col.querySelector('select');
                        const input = col.querySelector('input:not([type="checkbox"]):not([type="hidden"])');
                        if (select) {
                            row.push(cleanCSVValue(select.options[select.selectedIndex]?.text || ''));
                        } else if (input) {
                            row.push(cleanCSVValue(input.value));
                        } else {
                            let text = col.innerText || col.textContent || '';
                            row.push(cleanCSVValue(text));
                        }
                    }
                }
                if (row.length > 0) {
                    csv.push(row.join(','));
                }
            }

            const csvContent = "\uFEFF" + csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const filename = `export_${table.className.replace(/[^a-zA-Z0-9]/g, '_') || 'data'}_${new Date().toISOString().slice(0,10)}.csv`;

            const link = document.createElement("a");
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        bar.appendChild(btn);

        const parent = table.parentElement;
        if (parent && (parent.classList.contains('confirm-table-wrap') || parent.style.overflowX === 'auto' || parent.style.overflow === 'auto' || parent.tagName === 'DIV')) {
            parent.parentNode.insertBefore(bar, parent);
        } else {
            table.parentNode.insertBefore(bar, table);
        }
    });

    function cleanCSVValue(value) {
        let clean = value.replace(/"/g, '""');
        clean = clean.replace(/\r?\n|\r/g, ' ').trim();
        if (clean.indexOf(',') > -1 || clean.indexOf('"') > -1 || clean.indexOf('\n') > -1) {
            clean = `"${clean}"`;
        }
        return clean;
    }
});
</script>
    {{-- Floating AI Assistant Button --}}
    @if(!request()->routeIs('client.ai-chat.index'))
    <a href="{{ route('client.ai-chat.index') }}" class="floating-ai-btn {{ app()->getLocale() === 'ar' ? 'floating-ai-btn--rtl' : '' }}" title="{{ __('Ask AI Assistant') }}">
        <span class="ai-btn-pulse"></span>
        <svg class="ai-icon" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zM9 9h.008v.008H9V9zm0 3h.008v.008H9V12zm0 3h.008v.008H9V15zm3-6h.008v.008H12V9zm0 3h.008v.008H12V12zm0 3h.008v.008H12V15zm3-6h.008v.008H15V9zm0 3h.008v.008H15V12zm0 3h.008v.008H15V15z"/>
        </svg>
        <span class="ai-btn-label">{{ __('AI Assistant') }}</span>
    </a>

    <style>
        .floating-ai-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 50px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.45), inset 0 1px 0 rgba(255,255,255,0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            letter-spacing: 0.02em;
        }

        .floating-ai-btn:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 14px 30px rgba(239, 68, 68, 0.55), 0 0 15px rgba(239, 68, 68, 0.3);
            background: linear-gradient(135deg, #f87171, #dc2626);
        }

        .ai-icon {
            animation: float-icon 3s ease-in-out infinite;
            flex-shrink: 0;
        }

        .ai-btn-pulse {
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            border-radius: 50px;
            border: 2px solid rgba(239, 68, 68, 0.6);
            opacity: 0;
            animation: pulse-ring 2s cubic-bezier(0.24, 0, 0.38, 1) infinite;
            pointer-events: none;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.96);
                opacity: 0.8;
            }
            100% {
                transform: scale(1.15);
                opacity: 0;
            }
        }

        @keyframes float-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }

        @media (max-width: 640px) {
            .floating-ai-btn {
                padding: 12px;
                border-radius: 50%;
                bottom: 16px;
                right: 16px;
            }
            .floating-ai-btn--rtl {
                right: auto;
                left: 16px;
            }
            .ai-btn-label {
                display: none;
            }
        }

        /* Arabic / RTL: move button to left side */
        .floating-ai-btn--rtl {
            right: auto;
            left: 24px;
            font-family: 'Tajawal', 'Inter', sans-serif;
        }
    </style>
    @endif

    @stack('scripts')
</body>
</html>
