<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Sa'ee Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ─── Reset & Variables ──────────────────────────── */
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

        /* ─── Shell ──────────────────────────────────────── */
        .shell { display: flex; height: 100vh; overflow: hidden; }

        /* ─── Sidebar ────────────────────────────────────── */
        .sidebar {
            width: 248px; flex-shrink: 0;
            background: var(--sidebar);
            border-right: 1px solid var(--bdr);
            display: flex; flex-direction: column;
            overflow: hidden;
            animation: sidebar-in .55s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes sidebar-in { from { transform: translateX(-100%); } to { transform: translateX(0); } }

        .sidebar-logo {
            padding: 20px 18px 18px;
            border-bottom: 1px solid var(--bdr);
            display: flex; align-items: center; gap: 10px; flex-shrink: 0;
        }
        .sidebar-logo-text { font-size: .78rem; font-weight: 700; color: rgba(255,255,255,.4); letter-spacing: .14em; text-transform: uppercase; }

        .sidebar-nav { flex: 1; padding: 14px 8px; overflow-y: auto; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 2px; }

        .nav-label {
            font-size: .64rem; font-weight: 700; color: var(--text-dim);
            letter-spacing: .14em; text-transform: uppercase;
            padding: 14px 10px 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 11px; border-radius: 9px;
            color: var(--text-sub); font-size: .845rem; font-weight: 500;
            text-decoration: none; margin-bottom: 1px; cursor: pointer;
            transition: background .13s, color .13s; position: relative;
        }
        .nav-item:hover { background: rgba(255,255,255,.04); color: var(--text); }
        .nav-item.active { background: rgba(220,38,38,.11); color: #fca5a5; border: 1px solid rgba(220,38,38,.14); }
        .nav-item.active > svg:first-child { opacity: 1; color: var(--red-lt); }
        .nav-item > svg:first-child { flex-shrink: 0; opacity: .6; }
        .nav-item span.nav-label-text { flex: 1; }

        /* Parent nav item (chevron) */
        .nav-parent-btn { width: 100%; background: none; border: none; font-family: inherit; cursor: pointer; text-align: left; }
        .nav-chevron { transition: transform .25s; flex-shrink: 0; opacity: .45; }
        .nav-item.parent-open .nav-chevron { transform: rotate(180deg); }

        /* Sub-menu */
        .nav-submenu {
            overflow: hidden; max-height: 0;
            transition: max-height .3s cubic-bezier(.4,0,.2,1);
            padding-left: 12px;
        }
        .nav-submenu.open { max-height: 220px; }
        .nav-sub-item {
            display: flex; align-items: center; gap: 9px;
            padding: 7px 11px; border-radius: 8px;
            color: var(--text-dim); font-size: .82rem; font-weight: 500;
            text-decoration: none; margin-bottom: 1px;
            transition: background .13s, color .13s; position: relative;
        }
        .nav-sub-item:hover { background: rgba(255,255,255,.035); color: var(--text-sub); }
        .nav-sub-item.active { color: #fca5a5; }
        .sub-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; opacity: .6; flex-shrink: 0; }
        .nav-sub-item.active .sub-dot { background: var(--red-lt); opacity: 1; box-shadow: 0 0 5px var(--red-lt); }

        /* Sidebar footer */
        .sidebar-foot { padding: 12px 8px; border-top: 1px solid var(--bdr); flex-shrink: 0; }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 11px; border-radius: 10px;
            background: rgba(255,255,255,.025); border: 1px solid var(--bdr);
            margin-bottom: 6px;
        }
        .u-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--red-dark), var(--red));
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; color: white; flex-shrink: 0;
        }
        .u-info { flex: 1; overflow: hidden; }
        .u-name { font-size: .8rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .u-role { font-size: .68rem; color: var(--red-lt); font-weight: 500; text-transform: capitalize; }
        .logout-btn {
            display: flex; align-items: center; gap: 10px; width: 100%;
            padding: 8px 11px; border-radius: 8px; border: none;
            background: none; color: var(--text-sub); font-size: .83rem;
            font-weight: 500; font-family: inherit; cursor: pointer;
            transition: background .13s, color .13s;
        }
        .logout-btn:hover { background: rgba(220,38,38,.08); color: #fca5a5; }
        .logout-btn svg { opacity: .6; flex-shrink: 0; }

        /* ─── Main ───────────────────────────────────────── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        /* ─── Topbar ─────────────────────────────────────── */
        .topbar {
            height: 58px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 26px;
            border-bottom: 1px solid var(--bdr);
            background: rgba(6,9,23,.7); backdrop-filter: blur(12px);
        }
        .topbar-left { display: flex; align-items: center; gap: 10px; overflow: hidden; }
        .topbar-title { font-size: 1rem; font-weight: 700; white-space: nowrap; }
        .breadcrumb { font-size: .75rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .breadcrumb a { color: var(--text-dim); text-decoration: none; }
        .breadcrumb a:hover { color: var(--text-sub); }
        .breadcrumb .sep { margin: 0 5px; opacity: .4; }
        .breadcrumb .current { color: var(--red-lt); }
        .topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .icon-btn {
            width: 35px; height: 35px; border-radius: 9px;
            background: rgba(255,255,255,.04); border: 1px solid var(--bdr);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-sub); cursor: pointer;
            transition: background .13s, color .13s; position: relative;
        }
        .icon-btn:hover { background: rgba(255,255,255,.07); color: var(--text); }
        .notif-dot {
            position: absolute; top: 6px; right: 6px;
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--red); box-shadow: 0 0 5px var(--red);
            animation: dot-p 2s infinite;
        }
        @keyframes dot-p { 0%,100%{opacity:1;} 50%{opacity:.4;} }

        /* ─── Content ────────────────────────────────────── */
        .content { flex: 1; overflow-y: auto; padding: 24px 26px; }
        .content::-webkit-scrollbar { width: 6px; }
        .content::-webkit-scrollbar-track { background: transparent; }
        .content::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 3px; }

        /* ─── Flash messages ─────────────────────────────── */
        .flash {
            border-radius: 11px; padding: 12px 16px; font-size: .85rem;
            margin-bottom: 18px; display: flex; align-items: center; gap: 10px;
            animation: fu .4s both;
        }
        .flash-ok  { background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.2); color: #86efac; }
        .flash-err { background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.18); color: #fca5a5; }

        /* ─── Page header ────────────────────────────────── */
        .page-hd {
            display: flex; align-items: flex-start; justify-content: space-between;
            margin-bottom: 22px; gap: 16px; flex-wrap: wrap;
            animation: fu .45s .05s both;
        }
        .page-hd-left h1 { font-size: 1.4rem; font-weight: 800; letter-spacing: -.025em; }
        .page-hd-left p  { color: var(--text-sub); font-size: .83rem; margin-top: 3px; }

        /* ─── Buttons ────────────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            background: linear-gradient(135deg, var(--red-deep), var(--red));
            color: white; border: none; border-radius: 9px; padding: 9px 18px;
            font-size: .85rem; font-weight: 600; font-family: inherit; cursor: pointer;
            text-decoration: none; white-space: nowrap;
            transition: opacity .15s, transform .12s, box-shadow .15s;
            box-shadow: 0 3px 14px rgba(220,38,38,.28);
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(220,38,38,.4); }
        .btn-primary:active { transform: translateY(0); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,.05); color: var(--text-sub);
            border: 1px solid var(--bdr); border-radius: 9px; padding: 9px 18px;
            font-size: .85rem; font-weight: 500; font-family: inherit; cursor: pointer;
            text-decoration: none; white-space: nowrap; transition: background .13s, color .13s;
        }
        .btn-secondary:hover { background: rgba(255,255,255,.08); color: var(--text); }
        .btn-danger {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(220,38,38,.12); color: #f87171;
            border: 1px solid rgba(220,38,38,.22); border-radius: 9px; padding: 9px 18px;
            font-size: .85rem; font-weight: 600; font-family: inherit; cursor: pointer;
            text-decoration: none; transition: background .13s;
        }
        .btn-danger:hover { background: rgba(220,38,38,.22); }

        /* ─── Stats mini-row ─────────────────────────────── */
        .mini-stats { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; animation: fu .45s .1s both; }
        .mini-stat {
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 11px; padding: 14px 20px;
            display: flex; align-items: center; gap: 12px;
            backdrop-filter: blur(8px); flex: 1; min-width: 130px;
        }
        .mini-stat-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .mini-stat-val  { font-size: 1.4rem; font-weight: 800; line-height: 1; }
        .mini-stat-lbl  { font-size: .72rem; color: var(--text-sub); margin-top: 2px; }

        /* ─── Filter bar ─────────────────────────────────── */
        .filter-bar {
            display: flex; align-items: center; gap: 10px;
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 12px; padding: 12px 16px;
            margin-bottom: 16px; flex-wrap: wrap;
            backdrop-filter: blur(8px); animation: fu .45s .15s both;
        }
        .filter-search-wrap { position: relative; flex: 1; min-width: 200px; }
        .filter-search-wrap svg { position: absolute; left: 11px; top: 50%; translate: 0 -50%; color: rgba(255,255,255,.25); pointer-events: none; }
        .filter-input {
            width: 100%; padding: 8px 12px 8px 36px;
            background: var(--in-bg); border: 1px solid var(--in-bdr);
            border-radius: 8px; color: var(--text); font-size: .84rem;
            font-family: inherit; outline: none;
            transition: border-color .2s;
        }
        .filter-input:focus { border-color: rgba(220,38,38,.35); }
        .filter-select {
            padding: 8px 30px 8px 11px; background: var(--in-bg); border: 1px solid var(--in-bdr);
            border-radius: 8px; color: var(--text); font-size: .84rem;
            font-family: inherit; outline: none; cursor: pointer;
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' viewBox='0 0 24 24'%3E%3Cpath stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 9px center;
            transition: border-color .2s;
        }
        .filter-select:focus { border-color: rgba(220,38,38,.35); }
        .filter-select option { background: #0c1230; }

        /* ─── Table ──────────────────────────────────────── */
        .table-card {
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 14px; overflow: hidden;
            backdrop-filter: blur(8px); animation: fu .45s .2s both;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: rgba(255,255,255,.025); border-bottom: 1px solid var(--bdr);
            padding: 11px 16px; text-align: left; font-size: .7rem;
            font-weight: 700; color: var(--text-dim); letter-spacing: .09em; text-transform: uppercase;
            white-space: nowrap;
        }
        tbody td {
            padding: 13px 16px; border-bottom: 1px solid rgba(255,255,255,.03);
            font-size: .84rem; color: var(--text); vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: rgba(255,255,255,.018); }

        /* ─── Table cell helpers ─────────────────────────── */
        .cell-name { display: flex; align-items: center; gap: 10px; }
        .cell-avatar {
            width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; color: white;
        }
        .cell-main  { font-weight: 600; font-size: .855rem; }
        .cell-sub   { font-size: .74rem; color: var(--text-dim); margin-top: 1px; }

        /* ─── Status badges ──────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px; border-radius: 100px;
            font-size: .7rem; font-weight: 600; white-space: nowrap;
        }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge-active   { background: rgba(34,197,94,.1);  color: #4ade80; }
        .badge-suspended{ background: rgba(239,68,68,.1);  color: #f87171; }
        .badge-pending  { background: rgba(245,158,11,.1); color: #fcd34d; }
        .badge-pv       { background: rgba(59,130,246,.1); color: #60a5fa; }
        .badge-info     { background: rgba(59,130,246,.1); color: #60a5fa; }
        .badge-superadmin { background: rgba(168,85,247,.1); color: #c084fc; }
        .badge-admin    { background: rgba(220,38,38,.1);  color: #f87171; }
        .badge-yes      { background: rgba(34,197,94,.1);  color: #4ade80; }
        .badge-no       { background: rgba(100,116,139,.1);color: #94a3b8; }

        /* ─── Action buttons ─────────────────────────────── */
        .actions { display: flex; align-items: center; gap: 5px; }
        .act-btn {
            width: 31px; height: 31px; border-radius: 7px;
            border: 1px solid var(--bdr); background: rgba(255,255,255,.03);
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; color: var(--text-sub); cursor: pointer;
            transition: background .12s, border-color .12s, color .12s; flex-shrink: 0;
        }
        .act-btn:hover       { background: rgba(255,255,255,.07); color: var(--text); }
        .act-btn.act-delete:hover { background: rgba(220,38,38,.1); border-color: rgba(220,38,38,.2); color: #f87171; }
        .act-btn.act-view:hover  { background: rgba(59,130,246,.08); border-color: rgba(59,130,246,.15); color: #60a5fa; }
        .act-btn.act-edit:hover  { background: rgba(245,158,11,.08); border-color: rgba(245,158,11,.15); color: #fcd34d; }

        /* ─── Toggle switch ──────────────────────────────── */
        .toggle-switch { display:inline-flex; align-items:center; cursor:pointer; }
        .toggle-switch input { display:none; }
        .toggle-track { width:34px; height:18px; background:rgba(255,255,255,.12); border-radius:9px; position:relative; transition:background .2s; }
        .toggle-thumb { position:absolute; top:2px; left:2px; width:14px; height:14px; background:#fff; border-radius:50%; transition:transform .2s; }
        .toggle-switch input:checked ~ .toggle-track { background:var(--red); }
        .toggle-switch input:checked ~ .toggle-track .toggle-thumb { transform:translateX(16px); }

        /* ─── Empty state ────────────────────────────────── */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--text-dim);
        }
        .empty-state svg { opacity: .25; margin-bottom: 14px; }
        .empty-state h3  { font-size: 1rem; font-weight: 600; color: var(--text-sub); margin-bottom: 6px; }
        .empty-state p   { font-size: .83rem; }

        /* ─── Pagination ─────────────────────────────────── */
        .pagination-wrap { padding: 14px 16px; border-top: 1px solid var(--bdr); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .pag-info { font-size: .78rem; color: var(--text-dim); }
        .pag-links { display: flex; gap: 4px; }
        .pag-links a, .pag-links span {
            display: flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 7px;
            font-size: .78rem; font-weight: 500; text-decoration: none; color: var(--text-sub);
            border: 1px solid transparent; transition: background .12s, color .12s;
        }
        .pag-links a:hover { background: rgba(255,255,255,.06); color: var(--text); }
        .pag-links span.active { background: rgba(220,38,38,.12); border-color: rgba(220,38,38,.18); color: #fca5a5; }
        .pag-links span.disabled { opacity: .3; pointer-events: none; }

        /* ─── Forms ──────────────────────────────────────── */
        .form-wrap { max-width: 860px; animation: fu .45s .05s both; }
        .form-section {
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 14px; padding: 24px; margin-bottom: 18px;
            backdrop-filter: blur(8px);
        }
        .form-section-title {
            font-size: .8rem; font-weight: 700; color: var(--text-sub);
            letter-spacing: .07em; text-transform: uppercase;
            margin-bottom: 18px; padding-bottom: 12px;
            border-bottom: 1px solid var(--bdr);
            display: flex; align-items: center; gap: 8px;
        }
        .form-section-title svg { color: var(--red-lt); opacity: .7; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .form-group  { display: flex; flex-direction: column; gap: 6px; }
        .form-group.span-2 { grid-column: span 2; }
        .form-group.span-3 { grid-column: span 3; }
        .form-label {
            font-size: .7rem; font-weight: 700; color: rgba(255,255,255,.45);
            letter-spacing: .08em; text-transform: uppercase;
        }
        .form-label .req { color: var(--red-lt); margin-left: 2px; }
        .form-input, .form-select, .form-textarea {
            background: var(--in-bg); border: 1px solid var(--in-bdr);
            border-radius: 9px; padding: 10px 13px; color: var(--text);
            font-size: .875rem; font-family: inherit; outline: none;
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: rgba(220,38,38,.4);
            box-shadow: 0 0 0 3px rgba(220,38,38,.07);
        }
        .form-input.err, .form-select.err, .form-textarea.err { border-color: rgba(220,38,38,.45); }
        .form-select {
            appearance: none; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' viewBox='0 0 24 24'%3E%3Cpath stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 11px center;
            padding-right: 32px;
        }
        .form-select option { background: #0c1230; }
        .form-textarea { resize: vertical; min-height: 80px; }
        .form-error { font-size: .73rem; color: #f87171; }
        .form-hint  { font-size: .73rem; color: var(--text-dim); }
        .form-actions {
            display: flex; align-items: center; gap: 10px; justify-content: flex-end;
            padding-top: 4px;
        }

        /* ─── Permission checkboxes ──────────────────────── */
        .perm-groups { display: flex; flex-direction: column; gap: 18px; }
        .perm-group-title {
            font-size: .72rem; font-weight: 700; color: var(--red-lt);
            letter-spacing: .09em; text-transform: uppercase;
            margin-bottom: 10px; padding-bottom: 7px;
            border-bottom: 1px solid rgba(220,38,38,.1);
        }
        .perm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px; }
        .perm-item {
            display: flex; align-items: center; gap: 9px;
            background: var(--in-bg); border: 1px solid var(--in-bdr);
            border-radius: 9px; padding: 9px 12px; cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .perm-item:hover { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.1); }
        .perm-item input[type=checkbox] { accent-color: var(--red); cursor: pointer; flex-shrink: 0; }
        .perm-item input:checked ~ .perm-item-name { color: #fca5a5; }
        .perm-item-wrap { display: flex; flex-direction: column; }
        .perm-item-name { font-size: .8rem; font-weight: 500; color: var(--text-sub); transition: color .12s; }
        .perm-item input:checked ~ div .perm-item-name { color: #fca5a5; }

        /* ─── Show/Profile page ──────────────────────────── */
        .profile-hd {
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 14px; padding: 24px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
            backdrop-filter: blur(8px); animation: fu .4s .05s both;
        }
        .profile-avatar {
            width: 66px; height: 66px; border-radius: 16px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; font-weight: 800; color: white;
        }
        .profile-info { flex: 1; min-width: 180px; }
        .profile-name { font-size: 1.25rem; font-weight: 800; letter-spacing: -.02em; }
        .profile-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
        .profile-meta-item { font-size: .8rem; color: var(--text-sub); display: flex; align-items: center; gap: 5px; }
        .profile-actions { display: flex; gap: 8px; flex-wrap: wrap; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .info-card {
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 14px; padding: 20px; backdrop-filter: blur(8px);
        }
        .info-card.full { grid-column: span 2; }
        .info-card-title {
            font-size: .72rem; font-weight: 700; color: var(--text-dim);
            letter-spacing: .09em; text-transform: uppercase; margin-bottom: 16px;
            padding-bottom: 10px; border-bottom: 1px solid var(--bdr);
        }
        .info-rows { display: flex; flex-direction: column; gap: 10px; }
        .info-row  { display: flex; align-items: flex-start; gap: 12px; }
        .info-row-key { font-size: .78rem; color: var(--text-dim); width: 140px; flex-shrink: 0; padding-top: 1px; }
        .info-row-val { font-size: .84rem; color: var(--text); flex: 1; word-break: break-word; }

        /* ─── Animations ─────────────────────────────────── */
        @keyframes fu { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes shake { 0%,100%{transform:translateX(0);} 15%,45%,75%{transform:translateX(-4px);} 30%,60%,90%{transform:translateX(4px);} }
        @keyframes fade-in { from{opacity:0;} to{opacity:1;} }
        @keyframes modal-in { from{transform:scale(.9);opacity:0;} to{transform:scale(1);opacity:1;} }

        /* ─── Delete modal ───────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,.72); backdrop-filter: blur(5px);
            display: none; align-items: center; justify-content: center;
            animation: fade-in .2s;
        }
        .modal-overlay.open { display: flex; }
        .modal-card {
            background: #0b1228; border: 1px solid rgba(220,38,38,.15);
            border-radius: 18px; padding: 36px 32px; max-width: 380px; width: 90%;
            text-align: center; box-shadow: 0 24px 80px rgba(0,0,0,.9);
            animation: modal-in .25s cubic-bezier(.16,1,.3,1);
        }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 14px; margin: 0 auto 18px;
            background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.2);
            display: flex; align-items: center; justify-content: center;
        }
        .modal-card h3 { font-size: 1.1rem; font-weight: 800; margin-bottom: 8px; }
        .modal-card p  { font-size: .85rem; color: var(--text-sub); line-height: 1.55; margin-bottom: 26px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-actions .btn-secondary { flex: 1; justify-content: center; }
        .modal-actions .btn-danger    { flex: 1; justify-content: center; }

        /* ─── Responsive ─────────────────────────────────── */
        @media(max-width:1024px) {
            .sidebar { display:none; }
            .form-grid-2, .form-grid-3, .info-grid { grid-template-columns:1fr; }
            .form-group.span-2, .form-group.span-3, .info-card.full { grid-column:span 1; }
        }
        @media(max-width:600px) {
            .content { padding:16px; }
            .page-hd { flex-direction:column; }
        }
        /* ─── View aliases & extras ──────────────────────── */
        /* mini-stat short aliases */
        .ms-val { font-size: 1.4rem; font-weight: 800; line-height: 1; }
        .ms-lbl { font-size: .72rem; color: var(--text-sub); margin-top: 2px; }
        /* filter helpers */
        .filter-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex: 1; }
        .filter-search {
            flex: 1; min-width: 180px;
            padding: 8px 12px; background: var(--in-bg); border: 1px solid var(--in-bdr);
            border-radius: 8px; color: var(--text); font-size: .84rem;
            font-family: inherit; outline: none; transition: border-color .2s;
        }
        .filter-search:focus { border-color: rgba(220,38,38,.35); }
        /* action group alias */
        .act-btns { display: flex; align-items: center; gap: 5px; }
        /* profile page extras */
        .profile-meta { flex: 1; min-width: 180px; }
        .profile-sub  { font-size: .82rem; color: var(--text-sub); margin-top: 3px; }
        .profile-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        /* info-row simple span/strong layout */
        .info-row span { font-size: .78rem; color: var(--text-dim); min-width: 130px; flex-shrink: 0; }
        .info-row strong { font-size: .84rem; color: var(--text); flex: 1; word-break: break-word; }
        /* perm helpers */
        .perm-name { font-size: .8rem; font-weight: 500; color: var(--text-sub); }
        .perm-desc { font-size: .7rem; color: var(--text-dim); margin-top: 2px; }
        /* form error state alias */
        .form-input.is-error,.form-select.is-error,.form-textarea.is-error { border-color: rgba(220,38,38,.45); }
        /* form label optional note */
        .form-label .opt { font-size: .68rem; color: var(--text-dim); font-weight: 400; letter-spacing: 0; text-transform: none; }
        /* cell avatar background */
        .cell-avatar { background: linear-gradient(135deg,#7f1d1d,#dc2626); }
        /* profile avatar background */
        .profile-avatar { background: linear-gradient(135deg,#7f1d1d,#dc2626); }
    </style>
    @yield('head')
</head>
<body>
<div class="shell">
    @include('admin.partials.sidebar')
    <div class="main">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">Sa'ee Admin</a>
                    @yield('breadcrumb')
                </div>
            </div>
            <div class="topbar-right">
                <div style="position: relative;">
                    <div class="icon-btn" title="Notifications" id="notifBell" onclick="toggleNotifDropdown(event)">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="notif-dot" id="notifDot" style="display: none;"></span>
                    </div>
                    
                    {{-- Dropdown Container --}}
                    <div id="notifDropdown" style="display: none; position: absolute; right: 0; top: 44px; width: 320px; background: #0c1230; border: 1px solid var(--bdr); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 9999; overflow: hidden; animation: modal-in .18s ease-out;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: .78rem; font-weight: 700; color: var(--text-sub);">Notifications</span>
                            <button onclick="clearAllNotifications(event)" style="background: none; border: none; font-size: .7rem; color: var(--red-lt); font-family: inherit; font-weight:600; cursor: pointer; hover: text-decoration: underline;">Mark all read</button>
                        </div>
                        <div id="notifList" style="max-height: 250px; overflow-y: auto;">
                            {{-- Dynamic List --}}
                        </div>
                        <a href="{{ route('admin.notifications.index') }}" style="display: block; text-align: center; padding: 10px; border-top: 1px solid var(--bdr); font-size: .72rem; color: var(--text-dim); text-decoration: none; font-weight: 600;">View All Notifications</a>
                    </div>
                </div>

                <div class="icon-btn">
                    <div class="u-avatar" style="width:24px;height:24px;font-size:.65rem;border-radius:6px">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <div class="content">
            @if(session('success'))
                <div class="flash flash-ok">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flash flash-err" style="animation:shake .4s both">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-card">
        <div class="modal-icon">
            <svg width="26" height="26" fill="none" stroke="#ef4444" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3>Delete <span id="modalEntityName">this record</span>?</h3>
        <p>This will soft-delete the record. It can be restored by a superadmin if needed.</p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="flex:1">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" style="width:100%;justify-content:center">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('modalEntityName').textContent = name || 'this record';
    document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});

// Sidebar submenu toggle
function toggleSubmenu(btnId, menuId) {
    const btn  = document.getElementById(btnId);
    const menu = document.getElementById(menuId);
    const open = menu.classList.toggle('open');
    btn.classList.toggle('parent-open', open);
}

// Notifications dropdown logic
function toggleNotifDropdown(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';
}

function fetchUnreadNotifications() {
    fetch("{{ route('admin.notifications.unread') }}")
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const dot = document.getElementById('notifDot');
            const list = document.getElementById('notifList');
            
            if (data.count > 0) {
                dot.style.display = 'block';
            } else {
                dot.style.display = 'none';
            }

            list.innerHTML = '';
            if (data.notifications.length === 0) {
                list.innerHTML = `<div style="padding: 20px; text-align: center; font-size: .78rem; color: var(--text-dim);">No unread notifications.</div>`;
            } else {
                data.notifications.forEach(n => {
                    const item = document.createElement('a');
                    item.href = n.link ? n.link : '#';
                    item.style.display = 'block';
                    item.style.padding = '12px 16px';
                    item.style.borderBottom = '1px solid rgba(255,255,255,0.02)';
                    item.style.textDecoration = 'none';
                    item.style.color = 'inherit';
                    item.style.transition = 'background .15s';
                    item.onmouseenter = () => item.style.background = 'rgba(255,255,255,0.02)';
                    item.onmouseleave = () => item.style.background = 'transparent';

                    item.innerHTML = `
                        <div style="font-weight: 600; font-size: .8rem; color: #fff;">${n.title}</div>
                        <div style="font-size: .74rem; color: var(--text-sub); margin-top: 3px; line-height:1.4;">${n.message}</div>
                        <div style="font-size: .65rem; color: var(--text-dim); margin-top: 5px;">Just now</div>
                    `;
                    list.appendChild(item);
                });
            }
        }
    });
}

function clearAllNotifications(e) {
    e.stopPropagation();
    fetch("{{ route('admin.notifications.clear') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fetchUnreadNotifications();
        }
    });
}

// Close dropdown on click outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('notifDropdown');
    const bell = document.getElementById('notifBell');
    if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(e.target) && !bell.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// Run on load and poll every 10 seconds
if (document.getElementById('notifBell')) {
    fetchUnreadNotifications();
    setInterval(fetchUnreadNotifications, 10000);
}

// Auto-open menus based on current route
(function() {
    const path = window.location.pathname;
    if (path.includes('/admin/users/')) {
        const menu = document.getElementById('usersMenu');
        const btn  = document.getElementById('usersBtn');
        if (menu) menu.classList.add('open');
        if (btn)  btn.classList.add('parent-open');
    }
    if (path.includes('/admin/cities') || path.includes('/admin/rejection-reasons') || path.includes('/admin/whatsapp-templates') || path.includes('/admin/attendance') || path.includes('/admin/notifications')) {
        const menu = document.getElementById('settingsMenu');
        const btn  = document.getElementById('settingsBtn');
        if (menu) menu.classList.add('open');
        if (btn)  btn.classList.add('parent-open');
    }
    if (path.includes('/admin/financials')) {
        const menu = document.getElementById('financeMenu');
        const btn  = document.getElementById('financeBtn');
        if (menu) menu.classList.add('open');
        if (btn)  btn.classList.add('parent-open');
    }
    if (path.includes('/admin/reports')) {
        const menu = document.getElementById('reportsMenu');
        const btn  = document.getElementById('reportsBtn');
        if (menu) menu.classList.add('open');
        if (btn)  btn.classList.add('parent-open');
    }
})();
</script>
@yield('scripts')
</body>
</html>
