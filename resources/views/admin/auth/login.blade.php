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
    <title>Admin Login — Sa'ee Logistic Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:        #dc2626;
            --red-dark:   #991b1b;
            --red-deeper: #7f1d1d;
            --red-light:  #ef4444;
            --red-glow:   rgba(220,38,38,0.45);
            --bg:         #07091a;
            --bg-card:    rgba(7, 10, 28, 0.88);
            --text:       #f1f5f9;
            --text-sub:   #94a3b8;
            --text-dim:   #475569;
            --input-bg:   rgba(255,255,255,0.035);
            --input-bdr:  rgba(255,255,255,0.07);
        }

        html, body {
            height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow: hidden;
        }

        /* ── Canvas ─────────────────────────────────── */
        #cvs {
            position: fixed; inset: 0;
            pointer-events: none; z-index: 0;
        }

        /* ── Aurora blobs ───────────────────────────── */
        .blob {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(80px);
        }
        .blob-1 {
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(185,28,28,0.22) 0%, transparent 65%);
            bottom: -250px; left: -150px;
            animation: blob-drift 12s ease-in-out infinite alternate;
        }
        .blob-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(220,38,38,0.12) 0%, transparent 65%);
            top: -100px; right: 5%;
            animation: blob-drift 9s ease-in-out infinite alternate-reverse;
        }
        @keyframes blob-drift {
            0%   { transform: translate(0,0) scale(1); }
            100% { transform: translate(60px,-60px) scale(1.15); }
        }

        /* ── Layout ─────────────────────────────────── */
        .wrap {
            display: flex;
            height: 100vh;
            position: relative; z-index: 1;
        }

        /* ╔══════════════════════════════╗
           ║  LEFT  –  Brand panel        ║
           ╚══════════════════════════════╝ */
        .brand {
            flex: 0 0 58%;
            position: relative;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            border-right: 1px solid rgba(220,38,38,0.08);
        }

        /* Subtle grid */
        .brand::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(220,38,38,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(220,38,38,0.035) 1px, transparent 1px);
            background-size: 56px 56px;
            animation: grid-scroll 28s linear infinite;
        }
        @keyframes grid-scroll {
            to { background-position: 56px 56px; }
        }

        /* Glowing rings */
        .ring {
            position: absolute; left: 50%; top: 50%;
            translate: -50% -50%;
            border-radius: 50%;
            border: 1px solid rgba(220,38,38,0.1);
            pointer-events: none;
        }
        .r1 { width: 420px; height: 420px; animation: ring-breathe 5s ease-in-out infinite; }
        .r2 { width: 650px; height: 650px; animation: ring-breathe 5s 1.5s ease-in-out infinite; opacity:.6; }
        .r3 { width: 900px; height: 900px; animation: ring-breathe 5s 3s   ease-in-out infinite; opacity:.3; }
        @keyframes ring-breathe {
            0%,100% { transform: translate(-50%,-50%) scale(1);    opacity: .3; }
            50%      { transform: translate(-50%,-50%) scale(1.04); opacity: .7; }
        }

        /* Corner accent glow */
        .brand::after {
            content: '';
            position: absolute;
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(220,38,38,0.28) 0%, transparent 65%);
            border-radius: 50%;
            bottom: -80px; right: -80px;
            animation: corner-float 7s ease-in-out infinite alternate;
        }
        @keyframes corner-float {
            0%   { transform: scale(1)   translate(0,0); }
            100% { transform: scale(1.2) translate(-40px,-40px); }
        }

        /* Brand content wrapper */
        .brand-inner {
            position: relative; z-index: 2;
            display: flex; flex-direction: column; align-items: center;
            gap: 32px; text-align: center;
            opacity: 0; transform: translateX(-36px);
            animation: slide-in-left .9s .05s cubic-bezier(0.16,1,0.3,1) forwards;
        }
        @keyframes slide-in-left {
            to { opacity: 1; transform: translateX(0); }
        }

        /* Logo */
        .brand-logo {
            animation: logo-breathe 5s ease-in-out infinite;
        }
        @keyframes logo-breathe {
            0%,100% { filter: drop-shadow(0 0 22px rgba(220,38,38,0.35)); }
            50%      { filter: drop-shadow(0 0 50px rgba(220,38,38,0.65)); }
        }

        .brand-headline {
            font-size: 2rem; font-weight: 800;
            color: #fff; letter-spacing: -0.03em; line-height: 1.2;
        }
        .brand-headline em { font-style: normal; color: var(--red-light); }
        .brand-sub {
            font-size: .93rem; color: rgba(255,255,255,.38);
            letter-spacing: .06em; margin-top: -20px;
        }

        /* Stats row */
        .stats {
            display: flex; gap: 0;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px; overflow: hidden;
        }
        .stat {
            padding: 18px 30px; text-align: center;
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .stat:last-child { border-right: none; }
        .stat-num {
            display: block; font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1;
        }
        .stat-lbl {
            display: block; font-size: .72rem; font-weight: 500;
            color: rgba(255,255,255,.35); letter-spacing: .08em;
            text-transform: uppercase; margin-top: 4px;
        }

        /* Feature pills */
        .pills { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .pill {
            display: flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 100px; padding: 7px 15px;
            font-size: .78rem; color: rgba(255,255,255,.5);
            font-weight: 500;
            animation: pill-float 4s ease-in-out infinite;
        }
        .pill:nth-child(2) { animation-delay: 1.3s; }
        .pill:nth-child(3) { animation-delay: 2.6s; }
        @keyframes pill-float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-5px); }
        }
        .pill-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--red); box-shadow: 0 0 7px var(--red); }

        /* ╔══════════════════════════════╗
           ║  RIGHT  –  Form panel        ║
           ╚══════════════════════════════╝ */
        .form-side {
            flex: 0 0 42%;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 36px;
        }

        .card {
            width: 100%; max-width: 420px;
            background: var(--bg-card);
            border: 1px solid rgba(220,38,38,0.1);
            border-radius: 22px;
            padding: 46px 40px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px rgba(0,0,0,0.7);
            opacity: 0; transform: translateY(22px);
            animation: card-in .85s .15s cubic-bezier(0.16,1,0.3,1) forwards;
        }
        @keyframes card-in {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Small logo mark on card */
        .card-logo { margin-bottom: 22px; }

        /* Badge */
        .badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(220,38,38,0.1);
            border: 1px solid rgba(220,38,38,0.22);
            color: #f87171; font-size: .72rem; font-weight: 600;
            letter-spacing: .1em; text-transform: uppercase;
            padding: 5px 13px; border-radius: 100px; margin-bottom: 18px;
        }
        .badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--red);
            box-shadow: 0 0 8px var(--red);
            animation: badge-pulse 2s ease-in-out infinite;
        }
        @keyframes badge-pulse {
            0%,100% { box-shadow: 0 0 8px var(--red); opacity: 1; }
            50%      { box-shadow: 0 0 3px var(--red); opacity: .5; }
        }

        .card h1 {
            font-size: 1.7rem; font-weight: 800; color: #fff;
            letter-spacing: -.03em; margin-bottom: 5px;
        }
        .card p.sub {
            color: var(--text-sub); font-size: .88rem; margin-bottom: 28px;
        }

        /* ── Alert ─────────────────────── */
        .alert {
            border-radius: 11px; padding: 12px 16px;
            font-size: .84rem; margin-bottom: 20px;
            animation: shake .5s cubic-bezier(0.36,0.07,0.19,0.97);
        }
        .alert-err {
            background: rgba(220,38,38,0.08);
            border: 1px solid rgba(220,38,38,0.18);
            color: #fca5a5;
        }
        .alert-ok {
            background: rgba(34,197,94,0.07);
            border: 1px solid rgba(34,197,94,0.18);
            color: #86efac;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            15%,45%,75% { transform: translateX(-5px); }
            30%,60%,90% { transform: translateX(5px); }
        }

        /* ── Fields ────────────────────── */
        .field { margin-bottom: 16px; }
        .field-label {
            display: block; font-size: .72rem; font-weight: 600;
            color: rgba(255,255,255,.5); letter-spacing: .07em;
            text-transform: uppercase; margin-bottom: 7px;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 14px; top: 50%; translate: 0 -50%;
            color: rgba(255,255,255,.2); pointer-events: none;
            transition: color .2s;
        }
        .field-wrap:focus-within .field-icon { color: rgba(220,38,38,.55); }

        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="text"] {
            width: 100%; padding: 13px 44px;
            background: var(--input-bg);
            border: 1px solid var(--input-bdr);
            border-radius: 11px;
            color: #fff; font-size: .9rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }
        input:focus {
            border-color: rgba(220,38,38,.4);
            background: rgba(220,38,38,.025);
            box-shadow: 0 0 0 3px rgba(220,38,38,.07), 0 0 18px rgba(220,38,38,.04);
        }
        input.has-error { border-color: rgba(220,38,38,.45) !important; }

        /* Password toggle */
        .pwd-btn {
            position: absolute; right: 13px; top: 50%; translate: 0 -50%;
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,.25); padding: 4px;
            transition: color .2s; line-height: 1;
        }
        .pwd-btn:hover { color: rgba(255,255,255,.65); }

        /* ── Extras row ─────────────────── */
        .extras {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 22px;
        }
        .check-wrap {
            display: flex; align-items: center; gap: 8px;
            font-size: .84rem; color: var(--text-sub); cursor: pointer;
        }
        input[type="checkbox"] {
            width: 15px; height: 15px; border-radius: 4px;
            accent-color: var(--red); cursor: pointer; padding: 0;
        }
        .forgot {
            font-size: .84rem; color: rgba(220,38,38,.7);
            text-decoration: none; font-weight: 500;
            transition: color .2s;
        }
        .forgot:hover { color: var(--red-light); }

        /* ── Submit button ──────────────── */
        .btn {
            width: 100%; padding: 14px 24px;
            background: linear-gradient(135deg, var(--red-deeper) 0%, var(--red) 55%, #f87171 100%);
            background-size: 200% 200%;
            color: #fff; border: none; border-radius: 11px;
            font-size: .9rem; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            position: relative; overflow: hidden;
            transition: background-position .35s, transform .12s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(220,38,38,.32);
            letter-spacing: .015em;
        }
        .btn:hover {
            background-position: 100% 100%;
            transform: translateY(-2px);
            box-shadow: 0 10px 32px rgba(220,38,38,.5);
        }
        .btn:active { transform: translateY(0); }
        /* shimmer */
        .btn::after {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 55%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
            transform: skewX(-20deg);
            transition: left .55s;
        }
        .btn:hover::after { left: 160%; }
        /* loading */
        .btn.loading { pointer-events: none; opacity: .75; }
        .spinner {
            width: 18px; height: 18px; display: none;
            border: 2px solid rgba(255,255,255,.25);
            border-top-color: #fff; border-radius: 50%;
            animation: spin .65s linear infinite;
        }
        .btn.loading .spinner  { display: block; }
        .btn.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Arrow icon on button */
        .btn-arrow {
            transition: transform .2s;
        }
        .btn:hover .btn-arrow { transform: translateX(4px); }

        /* ── Footer ─────────────────────── */
        .card-footer {
            text-align: center; color: var(--text-dim);
            font-size: .73rem; margin-top: 28px;
        }

        /* ── Stagger animations ──────────── */
        .f1 { animation: fade-up .5s .38s both; }
        .f2 { animation: fade-up .5s .46s both; }
        .f3 { animation: fade-up .5s .54s both; }
        .f4 { animation: fade-up .5s .62s both; }
        .f5 { animation: fade-up .5s .70s both; }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ─────────────────── */
        @media (max-width: 1024px) {
            .brand { display: none; }
            .form-side { flex: 1; }
            html, body { overflow: auto; }
        }
        @media (max-width: 480px) {
            .form-side { padding: 24px 18px; }
            .card { padding: 32px 22px; border-radius: 16px; }
            .card h1 { font-size: 1.5rem; }
        }

        /* Phone country-code picker */
        .phone-wrap { display:flex; align-items:stretch; border:1px solid var(--input-bdr); border-radius:11px; background:var(--input-bg); transition:border-color .2s,box-shadow .2s; position:relative; overflow:visible; }
        .phone-wrap:focus-within { border-color:rgba(220,38,38,.4); background:rgba(220,38,38,.025); box-shadow:0 0 0 3px rgba(220,38,38,.07),0 0 18px rgba(220,38,38,.04); }
        .phone-wrap.has-error { border-color:rgba(220,38,38,.45) !important; }
        .country-btn { display:flex; align-items:center; gap:5px; padding:0 9px 0 12px; background:none; border:none; border-right:1px solid var(--input-bdr); color:rgba(255,255,255,.75); cursor:pointer; font-family:'Inter',sans-serif; font-size:.82rem; font-weight:500; white-space:nowrap; transition:background .2s; border-radius:11px 0 0 11px; }
        .country-btn:hover,.country-btn.open { background:rgba(255,255,255,.045); }
        .country-flag { font-size:1rem; line-height:1; }
        .country-chevron { opacity:.4; transition:transform .2s; margin-left:2px; }
        .country-btn.open .country-chevron { transform:rotate(180deg); }
        .phone-wrap input[type="tel"] { background:transparent !important; border:none !important; box-shadow:none !important; border-radius:0 11px 11px 0 !important; padding-left:12px !important; padding-right:12px !important; width:auto !important; flex:1; min-width:0; }
        .country-dropdown { position:absolute; top:calc(100% + 5px); left:0; width:215px; background:#0d0f22; border:1px solid rgba(255,255,255,.1); border-radius:11px; z-index:999; box-shadow:0 10px 28px rgba(0,0,0,.65); display:none; max-height:252px; overflow-y:auto; }
        .country-dropdown.open { display:block; }
        .country-option { display:flex; align-items:center; gap:8px; padding:9px 12px; cursor:pointer; font-size:.81rem; color:rgba(255,255,255,.68); transition:background .15s; }
        .country-option:hover { background:rgba(255,255,255,.06); color:#fff; }
        .country-option.active { background:rgba(220,38,38,.1); color:#fff; }
        .country-option .opt-flag { font-size:.95rem; flex-shrink:0; }
        .country-option .opt-name { flex:1; }
        .country-option .opt-dial { color:rgba(255,255,255,.38); font-size:.78rem; flex-shrink:0; }
    </style>
</head>
<body>

<canvas id="cvs"></canvas>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrap">

    {{-- ═══════════════════════════  LEFT BRAND PANEL  ═══════════════════════════ --}}
    <div class="brand">
        <div class="ring r1"></div>
        <div class="ring r2"></div>
        <div class="ring r3"></div>

        <div class="brand-inner">

            {{-- Logo --}}
            <div class="brand-logo">
                <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee Logistic Services" style="width:320px;max-width:100%;filter:drop-shadow(0 4px 24px rgba(0,0,0,.45));">
            </div>

            <div>
                <p class="brand-headline">The Admin<br><em>Command Center</em></p>
            </div>

            <div class="stats">
                <div class="stat"><span class="stat-num">500+</span><span class="stat-lbl">Drivers</span></div>
                <div class="stat"><span class="stat-num">1.2K+</span><span class="stat-lbl">Clients</span></div>
                <div class="stat"><span class="stat-num">50K+</span><span class="stat-lbl">Deliveries</span></div>
            </div>

            <div class="pills">
                <span class="pill"><span class="pill-dot"></span> Real-time Tracking</span>
                <span class="pill"><span class="pill-dot"></span> Fleet Management</span>
                <span class="pill"><span class="pill-dot"></span> Smart Analytics</span>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════  RIGHT FORM PANEL  ════════════════════════════ --}}
    <div class="form-side">
        <div class="card">

            {{-- Small logo mark --}}
            <div class="card-logo">
                <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="44" height="44" style="object-fit:contain;border-radius:10px;">
            </div>

            <span class="badge"><span class="badge-dot"></span>Admin Portal</span>
            <h1>Welcome back</h1>
            <p class="sub">Sign in to access the dashboard</p>

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="alert alert-err">
                    {{ $errors->first() }}
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-ok">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" id="loginForm" novalidate>
                @csrf

                {{-- Phone --}}
                <div class="field f1">
                    <label class="field-label" for="phoneLocal">Phone Number</label>
                    <div class="field-wrap phone-wrap{{ $errors->has('phone') ? ' has-error' : '' }}">
                        <button type="button" class="country-btn" id="countryBtn" aria-label="Select country code">
                            <span class="country-flag" id="countryFlag">🇯🇴</span>
                            <span class="country-dial" id="countryDial">+962</span>
                            <svg class="country-chevron" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="country-dropdown" id="countryDropdown">
                            <div class="country-option active" data-dial="+962" data-flag="🇯🇴"><span class="opt-flag">🇯🇴</span><span class="opt-name">Jordan</span><span class="opt-dial">+962</span></div>
                            <div class="country-option" data-dial="+966" data-flag="🇸🇦"><span class="opt-flag">🇸🇦</span><span class="opt-name">Saudi Arabia</span><span class="opt-dial">+966</span></div>
                            <div class="country-option" data-dial="+971" data-flag="🇦🇪"><span class="opt-flag">🇦🇪</span><span class="opt-name">UAE</span><span class="opt-dial">+971</span></div>
                            <div class="country-option" data-dial="+965" data-flag="🇰🇼"><span class="opt-flag">🇰🇼</span><span class="opt-name">Kuwait</span><span class="opt-dial">+965</span></div>
                            <div class="country-option" data-dial="+974" data-flag="🇶🇦"><span class="opt-flag">🇶🇦</span><span class="opt-name">Qatar</span><span class="opt-dial">+974</span></div>
                            <div class="country-option" data-dial="+973" data-flag="🇧🇭"><span class="opt-flag">🇧🇭</span><span class="opt-name">Bahrain</span><span class="opt-dial">+973</span></div>
                            <div class="country-option" data-dial="+968" data-flag="🇴🇲"><span class="opt-flag">🇴🇲</span><span class="opt-name">Oman</span><span class="opt-dial">+968</span></div>
                            <div class="country-option" data-dial="+20" data-flag="🇪🇬"><span class="opt-flag">🇪🇬</span><span class="opt-name">Egypt</span><span class="opt-dial">+20</span></div>
                            <div class="country-option" data-dial="+970" data-flag="🇵🇸"><span class="opt-flag">🇵🇸</span><span class="opt-name">Palestine</span><span class="opt-dial">+970</span></div>
                            <div class="country-option" data-dial="+961" data-flag="🇱🇧"><span class="opt-flag">🇱🇧</span><span class="opt-name">Lebanon</span><span class="opt-dial">+961</span></div>
                            <div class="country-option" data-dial="+964" data-flag="🇮🇶"><span class="opt-flag">🇮🇶</span><span class="opt-name">Iraq</span><span class="opt-dial">+964</span></div>
                            <div class="country-option" data-dial="+963" data-flag="🇸🇾"><span class="opt-flag">🇸🇾</span><span class="opt-name">Syria</span><span class="opt-dial">+963</span></div>
                        </div>
                        <input id="phoneLocal" type="tel" placeholder="7xxxxxxxx" autocomplete="tel" autofocus>
                        <input type="hidden" name="phone" id="phoneHidden">
                    </div>
                </div>

                {{-- Password --}}
                <div class="field f2">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input
                            id="password" name="password" type="password"
                            placeholder="••••••••••"
                            autocomplete="current-password"
                        >
                        <button type="button" class="pwd-btn" id="pwdToggle" aria-label="Toggle password">
                            <svg id="eyeIcon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Extras --}}
                <div class="extras f3">
                    <label class="check-wrap">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    <a href="{{ route('admin.forgot-password') }}" class="forgot">Forgot password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn f4" id="submitBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">Sign In</span>
                    <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <p class="card-footer f5">&copy; {{ date('Y') }} Sa'ee Logistic Services. All rights reserved.</p>
        </div>
    </div>

</div>

<script>
(function() {
    /* ── Particles ──────────────────────────────────── */
    const canvas = document.getElementById('cvs');
    const ctx    = canvas.getContext('2d');
    let W, H, pts = [];
    const N = 70, LINK = 130;

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    class Pt {
        constructor() { this.init(); }
        init() {
            this.x  = Math.random() * W;
            this.y  = Math.random() * H;
            this.vx = (Math.random() - .5) * .35;
            this.vy = (Math.random() - .5) * .35;
            this.r  = Math.random() * 1.2 + .4;
            this.a  = Math.random() * .35 + .08;
        }
        step() {
            this.x += this.vx; this.y += this.vy;
            if (this.x < 0 || this.x > W) this.vx *= -1;
            if (this.y < 0 || this.y > H) this.vy *= -1;
        }
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${this.a})`;
            ctx.fill();
        }
    }

    for (let i = 0; i < N; i++) pts.push(new Pt());

    function loop() {
        ctx.clearRect(0, 0, W, H);
        pts.forEach(p => { p.step(); p.draw(); });
        for (let i = 0; i < N; i++) {
            for (let j = i + 1; j < N; j++) {
                const dx = pts[i].x - pts[j].x;
                const dy = pts[i].y - pts[j].y;
                const d  = Math.sqrt(dx*dx + dy*dy);
                if (d < LINK) {
                    ctx.beginPath();
                    ctx.moveTo(pts[i].x, pts[i].y);
                    ctx.lineTo(pts[j].x, pts[j].y);
                    ctx.strokeStyle = `rgba(220,38,38,${.07*(1-d/LINK)})`;
                    ctx.lineWidth = .6;
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(loop);
    }
    loop();

    /* ── Password toggle ────────────────────────────── */
    const pwdInput  = document.getElementById('password');
    const pwdToggle = document.getElementById('pwdToggle');
    const eyeIcon   = document.getElementById('eyeIcon');

    const eyeOpen   = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    const eyeClosed = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;

    pwdToggle.addEventListener('click', () => {
        const isText = pwdInput.type === 'text';
        pwdInput.type = isText ? 'password' : 'text';
        eyeIcon.innerHTML = isText ? eyeOpen : eyeClosed;
    });

    /* ── Country code dropdown ──────────────────────── */
    const countryBtn      = document.getElementById('countryBtn');
    const countryDropdown = document.getElementById('countryDropdown');
    const countryFlag     = document.getElementById('countryFlag');
    const countryDial     = document.getElementById('countryDial');

    countryBtn.addEventListener('click', e => {
        e.stopPropagation();
        countryBtn.classList.toggle('open');
        countryDropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => {
        countryBtn.classList.remove('open');
        countryDropdown.classList.remove('open');
    });
    countryDropdown.querySelectorAll('.country-option').forEach(opt => {
        opt.addEventListener('click', () => {
            countryDropdown.querySelectorAll('.country-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            countryFlag.textContent = opt.dataset.flag;
            countryDial.textContent = opt.dataset.dial;
            countryBtn.classList.remove('open');
            countryDropdown.classList.remove('open');
        });
    });

    /* ── Submit: combine dial + local, then load ────── */
    const form      = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', () => {
        let local = document.getElementById('phoneLocal').value.trim();
        if (local.startsWith('0')) local = local.slice(1);
        document.getElementById('phoneHidden').value = countryDial.textContent + local;
        submitBtn.classList.add('loading');
    });
})();
</script>
</body>
</html>
