<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Sign In')) — Sa'ee Client Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
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
            --bg:         #07091a;
            --bg-card:    rgba(7, 10, 28, 0.88);
            --text:       #f1f5f9;
            --text-sub:   #94a3b8;
            --text-dim:   #475569;
            --input-bg:   rgba(255,255,255,0.035);
            --input-bdr:  rgba(255,255,255,0.07);
        }
        html, body { height: 100%; font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow: hidden; }

        #cvs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }

        .blob { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(80px); }
        .blob-1 { width: 700px; height: 700px; background: radial-gradient(circle, rgba(185,28,28,0.22) 0%, transparent 65%); bottom: -250px; left: -150px; animation: blob-drift 12s ease-in-out infinite alternate; }
        .blob-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(220,38,38,0.12) 0%, transparent 65%); top: -100px; right: 5%; animation: blob-drift 9s ease-in-out infinite alternate-reverse; }
        @keyframes blob-drift { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(60px,-60px) scale(1.15); } }

        .wrap { display: flex; height: 100vh; position: relative; z-index: 1; }

        /* Brand panel */
        .brand { flex: 0 0 58%; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; border-right: 1px solid rgba(220,38,38,0.08); }
        .brand::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(220,38,38,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(220,38,38,0.035) 1px, transparent 1px); background-size: 56px 56px; animation: grid-scroll 28s linear infinite; }
        @keyframes grid-scroll { to { background-position: 56px 56px; } }
        .ring { position: absolute; left: 50%; top: 50%; translate: -50% -50%; border-radius: 50%; border: 1px solid rgba(220,38,38,0.1); pointer-events: none; }
        .r1 { width: 420px; height: 420px; animation: ring-breathe 5s ease-in-out infinite; }
        .r2 { width: 650px; height: 650px; animation: ring-breathe 5s 1.5s ease-in-out infinite; opacity:.6; }
        .r3 { width: 900px; height: 900px; animation: ring-breathe 5s 3s ease-in-out infinite; opacity:.3; }
        @keyframes ring-breathe { 0%,100% { transform: translate(-50%,-50%) scale(1); opacity:.3; } 50% { transform: translate(-50%,-50%) scale(1.04); opacity:.7; } }
        .brand-inner { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 28px; text-align: center; opacity: 0; transform: translateX(-36px); animation: slide-in-left .9s .05s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes slide-in-left { to { opacity: 1; transform: translateX(0); } }
        .brand-headline { font-size: 2rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; line-height: 1.2; }
        .brand-headline em { font-style: normal; color: var(--red-light); }
        .brand-sub { font-size: .93rem; color: rgba(255,255,255,.38); letter-spacing: .06em; margin-top: -14px; }
        .pills { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .pill { display: flex; align-items: center; gap: 7px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 100px; padding: 7px 15px; font-size: .78rem; color: rgba(255,255,255,.5); font-weight: 500; animation: pill-float 4s ease-in-out infinite; }
        .pill:nth-child(2) { animation-delay: 1.3s; }
        .pill:nth-child(3) { animation-delay: 2.6s; }
        @keyframes pill-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .pill-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--red); box-shadow: 0 0 7px var(--red); }

        /* Form panel */
        .form-side { flex: 0 0 42%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 36px; gap: 20px; }

        .card { width: 100%; max-width: 420px; background: var(--bg-card); border: 1px solid rgba(220,38,38,0.1); border-radius: 22px; padding: 46px 40px; backdrop-filter: blur(24px); box-shadow: 0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px rgba(0,0,0,0.7); opacity: 0; transform: translateY(22px); animation: card-in .85s .15s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes card-in { to { opacity: 1; transform: translateY(0); } }
        .card-logo { margin-bottom: 22px; }
        .badge-portal { display: inline-flex; align-items: center; gap: 7px; background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.22); color: #f87171; font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; padding: 5px 13px; border-radius: 100px; margin-bottom: 18px; }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--red); box-shadow: 0 0 8px var(--red); animation: badge-pulse 2s ease-in-out infinite; }
        @keyframes badge-pulse { 0%,100% { box-shadow: 0 0 8px var(--red); opacity: 1; } 50% { box-shadow: 0 0 3px var(--red); opacity: .5; } }
        .card h1 { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.03em; margin-bottom: 5px; }
        .card p.sub { color: var(--text-sub); font-size: .88rem; margin-bottom: 26px; }

        .alert { border-radius: 11px; padding: 12px 16px; font-size: .84rem; margin-bottom: 18px; }
        .alert-err { background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.18); color: #fca5a5; }
        .alert-ok  { background: rgba(34,197,94,0.07); border: 1px solid rgba(34,197,94,0.18); color: #86efac; }

        .field { margin-bottom: 15px; }
        .field-label { display: block; font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.5); letter-spacing: .07em; text-transform: uppercase; margin-bottom: 7px; }
        .field-wrap { position: relative; }
        .field-icon { position: absolute; left: 14px; top: 50%; translate: 0 -50%; color: rgba(255,255,255,.2); pointer-events: none; transition: color .2s; }
        .field-wrap:focus-within .field-icon { color: rgba(220,38,38,.55); }
        input[type="text"], input[type="tel"], input[type="password"], input[type="number"] { width: 100%; padding: 13px 44px; background: var(--input-bg); border: 1px solid var(--input-bdr); border-radius: 11px; color: #fff; font-size: .9rem; font-family: 'Inter', sans-serif; outline: none; transition: border-color .2s, box-shadow .2s; -webkit-appearance: none; }
        input:focus { border-color: rgba(220,38,38,.4); box-shadow: 0 0 0 3px rgba(220,38,38,.07); }
        input.has-error { border-color: rgba(220,38,38,.45) !important; }
        .pwd-btn { position: absolute; right: 13px; top: 50%; translate: 0 -50%; background: none; border: none; cursor: pointer; color: rgba(255,255,255,.25); padding: 4px; transition: color .2s; line-height: 1; }
        .pwd-btn:hover { color: rgba(255,255,255,.65); }
        .field-error { font-size: .76rem; color: #fca5a5; margin-top: 5px; }

        .extras { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .forgot { font-size: .84rem; color: rgba(220,38,38,.7); text-decoration: none; font-weight: 500; transition: color .2s; }
        .forgot:hover { color: var(--red-light); }

        .btn { width: 100%; padding: 14px 24px; background: linear-gradient(135deg, var(--red-deeper) 0%, var(--red) 55%, #f87171 100%); background-size: 200% 200%; color: #fff; border: none; border-radius: 11px; font-size: .9rem; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; position: relative; overflow: hidden; transition: background-position .35s, transform .12s, box-shadow .2s; box-shadow: 0 4px 20px rgba(220,38,38,.32); }
        .btn:hover { background-position: 100% 100%; transform: translateY(-2px); box-shadow: 0 10px 32px rgba(220,38,38,.5); }
        .btn:active { transform: translateY(0); }
        .btn::after { content: ''; position: absolute; top: 0; left: -100%; width: 55%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent); transform: skewX(-20deg); transition: left .55s; }
        .btn:hover::after { left: 160%; }
        .btn.loading { pointer-events: none; opacity: .75; }
        .spinner { width: 18px; height: 18px; display: none; border: 2px solid rgba(255,255,255,.25); border-top-color: #fff; border-radius: 50%; animation: spin .65s linear infinite; }
        .btn.loading .spinner { display: block; }
        .btn.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-arrow { transition: transform .2s; }
        .btn:hover .btn-arrow { transform: translateX(4px); }

        .card-footer { text-align: center; color: var(--text-dim); font-size: .73rem; margin-top: 26px; }
        .card-footer a { color: rgba(220,38,38,.7); text-decoration: none; }
        .card-footer a:hover { color: var(--red-light); }

        .f1 { animation: fade-up .5s .38s both; }
        .f2 { animation: fade-up .5s .46s both; }
        .f3 { animation: fade-up .5s .54s both; }
        .f4 { animation: fade-up .5s .62s both; }
        .f5 { animation: fade-up .5s .70s both; }
        @keyframes fade-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) { .brand { display: none; } .form-side { flex: 1; } html, body { overflow: auto; } }
        @media (max-width: 480px) { .form-side { padding: 24px 18px; } .card { padding: 32px 22px; border-radius: 16px; } .card h1 { font-size: 1.4rem; } }

        /* Phone country-code picker */
        .phone-wrap { display:flex; align-items:stretch; border:1px solid var(--input-bdr); border-radius:11px; background:var(--input-bg); transition:border-color .2s,box-shadow .2s; position:relative; overflow:visible; }
        .phone-wrap:focus-within { border-color:rgba(220,38,38,.4); box-shadow:0 0 0 3px rgba(220,38,38,.07); }
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

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .brand { border-left: 1px solid rgba(220,38,38,0.08); border-right: none; }
        html[dir="rtl"] .brand-inner { transform: translateX(36px); }
        html[dir="rtl"] .field-icon { right: 14px; left: auto; }
        html[dir="rtl"] .pwd-btn { left: 13px; right: auto; }
        html[dir="rtl"] .country-btn { padding: 0 12px 0 9px; border-left: 1px solid var(--input-bdr); border-right: none; border-radius: 0 11px 11px 0; }
        html[dir="rtl"] .phone-wrap input[type="tel"] { border-radius: 11px 0 0 11px !important; }
        html[dir="rtl"] .country-dropdown { right: 0; left: auto; }
        html[dir="rtl"] .country-chevron { margin-right: 2px; margin-left: 0; }

        /* ─── Footer Styles ──────────────────────────────── */
        .auth-footer {
            width: 100%;
            max-width: 420px;
            font-size: 0.76rem;
            color: var(--text-dim, #475569);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 16px;
            animation: card-in .85s .15s cubic-bezier(0.16,1,0.3,1) both;
        }
        .auth-footer-right {
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .smartedge-logo-auth {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.8rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 3px 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            overflow: visible;
        }
        .smartedge-logo-auth .smart {
            color: #ffffff;
        }
        .smartedge-logo-auth .edge {
            color: #3b82f6;
            text-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
        }
        .smartedge-logo-auth:hover {
            background: rgba(59, 130, 246, 0.08);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.15);
            transform: translateY(-1px);
        }
        .smartedge-logo-auth:hover .edge {
            color: #60a5fa;
            text-shadow: 0 0 12px rgba(96, 165, 250, 0.6);
        }
        .smartedge-logo-auth .tech-tooltip {
            position: absolute;
            bottom: calc(100% + 10px);
            right: 50%;
            transform: translateX(50%) translateY(5px);
            background: #0d1230;
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6), 0 0 15px rgba(59, 130, 246, 0.15);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }
        .smartedge-logo-auth:hover .tech-tooltip {
            opacity: 1;
            transform: translateX(50%) translateY(0);
        }
        .smartedge-logo-auth .tech-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: rgba(59, 130, 246, 0.35) transparent transparent transparent;
        }
        html[dir="rtl"] .smartedge-logo-auth .tech-tooltip {
            right: auto;
            left: 50%;
            transform: translateX(-50%) translateY(5px);
        }
        html[dir="rtl"] .smartedge-logo-auth:hover .tech-tooltip {
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

<canvas id="cvs"></canvas>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrap">
    {{-- Brand --}}
    <div class="brand">
        <div class="ring r1"></div>
        <div class="ring r2"></div>
        <div class="ring r3"></div>
        <div class="brand-inner">
            <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" style="width:280px;max-width:100%;filter:drop-shadow(0 4px 24px rgba(0,0,0,.45));">
            <div>
                <p class="brand-headline">{{ __('Your Business,') }}<br><em>{{ __('Delivered.') }}</em></p>
                <p class="brand-sub">{{ __("SA'EE CLIENT PORTAL") }}</p>
            </div>
            <div class="pills">
                <span class="pill"><span class="pill-dot"></span> {{ __('Track Orders') }}</span>
                <span class="pill"><span class="pill-dot"></span> {{ __('Manage Shipments') }}</span>
                <span class="pill"><span class="pill-dot"></span> {{ __('Real-time Updates') }}</span>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="form-side">
        @yield('form')

        {{-- Footer --}}
        <footer class="auth-footer">
            <span>&copy; {{ date('Y') }} {{ __('Sa\'ee Logistics') }}</span>
            <div class="auth-footer-right">
                <span style="color: var(--text-sub);">{{ __('Powered by') }}</span>
                <a href="https://smartedge.me" target="_blank" class="smartedge-logo-auth">
                    @if(app()->getLocale() === 'ar')
                        <span class="smart">الحافة الذكية للحلول الرقمية</span>
                    @else
                        <span class="smart">Smart</span><span class="edge">Edge</span>
                    @endif
                    <span class="tech-tooltip">{{ __('Premium Web Systems & Software Design') }}</span>
                </a>
            </div>
        </footer>
    </div>
</div>


<script>
(function() {
    const canvas = document.getElementById('cvs');
    const ctx    = canvas.getContext('2d');
    let W, H, pts = [];
    const N = 60, LINK = 120;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
    resize(); window.addEventListener('resize', resize);
    class Pt {
        constructor() { this.init(); }
        init() { this.x = Math.random()*W; this.y = Math.random()*H; this.vx = (Math.random()-.5)*.35; this.vy = (Math.random()-.5)*.35; this.r = Math.random()*1.2+.4; this.a = Math.random()*.35+.08; }
        step() { this.x += this.vx; this.y += this.vy; if (this.x<0||this.x>W) this.vx*=-1; if (this.y<0||this.y>H) this.vy*=-1; }
        draw() { ctx.beginPath(); ctx.arc(this.x,this.y,this.r,0,Math.PI*2); ctx.fillStyle=`rgba(255,255,255,${this.a})`; ctx.fill(); }
    }
    for (let i=0;i<N;i++) pts.push(new Pt());
    function loop() {
        ctx.clearRect(0,0,W,H);
        pts.forEach(p => { p.step(); p.draw(); });
        for (let i=0;i<N;i++) for (let j=i+1;j<N;j++) {
            const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y, d=Math.sqrt(dx*dx+dy*dy);
            if (d<LINK) { ctx.beginPath(); ctx.moveTo(pts[i].x,pts[i].y); ctx.lineTo(pts[j].x,pts[j].y); ctx.strokeStyle=`rgba(220,38,38,${.07*(1-d/LINK)})`; ctx.lineWidth=.6; ctx.stroke(); }
        }
        requestAnimationFrame(loop);
    }
    loop();
})();
</script>

@stack('scripts')
</body>
</html>
