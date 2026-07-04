<?php
    use App\Models\SiteSetting;
    $brandHeadline = SiteSetting::getVal('login_brand_headline', 'Your Business, Delivered.');
    $brandSubtitle = SiteSetting::getVal('login_brand_subtitle', "SA'EE LOGISTICS PORTAL");
    $brandPoints   = SiteSetting::getVal('login_brand_points', ['Track Orders', 'Manage Shipments', 'Real-time Updates']);
    if (!is_array($brandPoints)) $brandPoints = ['Track Orders', 'Manage Shipments', 'Real-time Updates'];
?>
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
    <title>{{ __('Sign In') }} — Sa'ee Portal</title>
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
            --red-glow:   rgba(220,38,38,0.45);
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

        /* ── Brand panel ─────────────────────── */
        .brand { flex: 0 0 58%; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; border-right: 1px solid rgba(220,38,38,0.08); }
        .brand::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(220,38,38,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(220,38,38,0.035) 1px, transparent 1px); background-size: 56px 56px; animation: grid-scroll 28s linear infinite; }
        @keyframes grid-scroll { to { background-position: 56px 56px; } }
        .ring { position: absolute; left: 50%; top: 50%; translate: -50% -50%; border-radius: 50%; border: 1px solid rgba(220,38,38,0.1); pointer-events: none; }
        .r1 { width: 420px; height: 420px; animation: ring-breathe 5s ease-in-out infinite; }
        .r2 { width: 650px; height: 650px; animation: ring-breathe 5s 1.5s ease-in-out infinite; opacity:.6; }
        .r3 { width: 900px; height: 900px; animation: ring-breathe 5s 3s ease-in-out infinite; opacity:.3; }
        @keyframes ring-breathe { 0%,100% { transform: translate(-50%,-50%) scale(1); opacity:.3; } 50% { transform: translate(-50%,-50%) scale(1.04); opacity:.7; } }
        .brand::after { content: ''; position: absolute; width: 380px; height: 380px; background: radial-gradient(circle, rgba(220,38,38,0.28) 0%, transparent 65%); border-radius: 50%; bottom: -80px; right: -80px; animation: corner-float 7s ease-in-out infinite alternate; }
        @keyframes corner-float { 0% { transform: scale(1) translate(0,0); } 100% { transform: scale(1.2) translate(-40px,-40px); } }
        .brand-inner { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 28px; text-align: center; opacity: 0; transform: translateX(-36px); animation: slide-in-left .9s .05s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes slide-in-left { to { opacity: 1; transform: translateX(0); } }
        .brand-logo { animation: logo-breathe 5s ease-in-out infinite; }
        @keyframes logo-breathe { 0%,100% { filter: drop-shadow(0 0 22px rgba(220,38,38,0.35)); } 50% { filter: drop-shadow(0 0 50px rgba(220,38,38,0.65)); } }
        .brand-headline { font-size: 2rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; line-height: 1.25; }
        .brand-headline em { font-style: normal; color: var(--red-light); }
        .brand-sub { font-size: .93rem; color: rgba(255,255,255,.38); letter-spacing: .06em; }
        .pills { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .pill { display: flex; align-items: center; gap: 7px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 100px; padding: 7px 15px; font-size: .78rem; color: rgba(255,255,255,.5); font-weight: 500; animation: pill-float 4s ease-in-out infinite; }
        .pill:nth-child(2) { animation-delay: 1.3s; }
        .pill:nth-child(3) { animation-delay: 2.6s; }
        @keyframes pill-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .pill-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--red); box-shadow: 0 0 7px var(--red); }

        /* ── Form panel ──────────────────────── */
        .form-side { flex: 0 0 42%; display: flex; align-items: center; justify-content: center; padding: 40px 36px; }
        .card { width: 100%; max-width: 420px; background: var(--bg-card); border: 1px solid rgba(220,38,38,0.1); border-radius: 22px; padding: 46px 40px; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); box-shadow: 0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px rgba(0,0,0,0.7); opacity: 0; transform: translateY(22px); animation: card-in .85s .15s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes card-in { to { opacity: 1; transform: translateY(0); } }
        .card-logo { margin-bottom: 22px; }
        .badge { display: inline-flex; align-items: center; gap: 7px; background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.22); color: #f87171; font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; padding: 5px 13px; border-radius: 100px; margin-bottom: 18px; }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--red); box-shadow: 0 0 8px var(--red); animation: badge-pulse 2s ease-in-out infinite; }
        @keyframes badge-pulse { 0%,100% { box-shadow: 0 0 8px var(--red); opacity: 1; } 50% { box-shadow: 0 0 3px var(--red); opacity: .5; } }
        .card h1 { font-size: 1.7rem; font-weight: 800; color: #fff; letter-spacing: -.03em; margin-bottom: 5px; }
        .card p.sub { color: var(--text-sub); font-size: .88rem; margin-bottom: 28px; }

        .alert { border-radius: 11px; padding: 12px 16px; font-size: .84rem; margin-bottom: 20px; animation: shake .5s cubic-bezier(0.36,0.07,0.19,0.97); }
        .alert-err { background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.18); color: #fca5a5; }
        .alert-ok  { background: rgba(34,197,94,0.07); border: 1px solid rgba(34,197,94,0.18); color: #86efac; }
        @keyframes shake { 0%,100% { transform: translateX(0); } 15%,45%,75% { transform: translateX(-5px); } 30%,60%,90% { transform: translateX(5px); } }

        .field { margin-bottom: 16px; }
        .field-label { display: block; font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.5); letter-spacing: .07em; text-transform: uppercase; margin-bottom: 7px; }
        .field-wrap { position: relative; }
        .field-icon { position: absolute; left: 14px; top: 50%; translate: 0 -50%; color: rgba(255,255,255,.2); pointer-events: none; transition: color .2s; }
        .field-wrap:focus-within .field-icon { color: rgba(220,38,38,.55); }
        input[type="tel"], input[type="password"], input[type="text"] { width: 100%; padding: 13px 44px; background: var(--input-bg); border: 1px solid var(--input-bdr); border-radius: 11px; color: #fff; font-size: .9rem; font-family: 'Inter', sans-serif; outline: none; transition: border-color .2s, box-shadow .2s, background .2s; -webkit-appearance: none; }
        input:focus { border-color: rgba(220,38,38,.4); background: rgba(220,38,38,.025); box-shadow: 0 0 0 3px rgba(220,38,38,.07), 0 0 18px rgba(220,38,38,.04); }
        input.has-error { border-color: rgba(220,38,38,.45) !important; }
        .pwd-btn { position: absolute; right: 13px; top: 50%; translate: 0 -50%; background: none; border: none; cursor: pointer; color: rgba(255,255,255,.25); padding: 4px; transition: color .2s; line-height: 1; }
        .pwd-btn:hover { color: rgba(255,255,255,.65); }

        .extras { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .check-wrap { display: flex; align-items: center; gap: 8px; font-size: .84rem; color: var(--text-sub); cursor: pointer; }
        input[type="checkbox"] { width: 15px; height: 15px; border-radius: 4px; accent-color: var(--red); cursor: pointer; padding: 0; }
        .forgot { font-size: .84rem; color: rgba(220,38,38,.7); text-decoration: none; font-weight: 500; transition: color .2s; }
        .forgot:hover { color: var(--red-light); }

        .btn { width: 100%; padding: 14px 24px; background: linear-gradient(135deg, var(--red-deeper) 0%, var(--red) 55%, #f87171 100%); background-size: 200% 200%; color: #fff; border: none; border-radius: 11px; font-size: .9rem; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; position: relative; overflow: hidden; transition: background-position .35s, transform .12s, box-shadow .2s; box-shadow: 0 4px 20px rgba(220,38,38,.32); letter-spacing: .015em; }
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

        .card-footer { text-align: center; color: var(--text-dim); font-size: .73rem; margin-top: 28px; }

        .f1 { animation: fade-up .5s .38s both; }
        .f2 { animation: fade-up .5s .46s both; }
        .f3 { animation: fade-up .5s .54s both; }
        .f4 { animation: fade-up .5s .62s both; }
        .f5 { animation: fade-up .5s .70s both; }
        @keyframes fade-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) { .brand { display: none; } .form-side { flex: 1; } html, body { overflow: auto; } }
        @media (max-width: 480px) { .form-side { padding: 24px 18px; } .card { padding: 32px 22px; border-radius: 16px; } .card h1 { font-size: 1.5rem; } }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .brand { border-left: 1px solid rgba(220,38,38,0.08); border-right: none; }
        html[dir="rtl"] .brand-inner { transform: translateX(36px); }
        html[dir="rtl"] .field-icon { right: 14px; left: auto; }
        html[dir="rtl"] .pwd-btn { left: 13px; right: auto; }
        html[dir="rtl"] .btn:hover .btn-arrow { transform: translateX(-4px); }
    </style>
</head>
<body>

<canvas id="cvs"></canvas>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrap">

    {{-- Brand panel --}}
    <div class="brand">
        <div class="ring r1"></div>
        <div class="ring r2"></div>
        <div class="ring r3"></div>

        <div class="brand-inner">
            <div class="brand-logo">
                <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee LogisticsServices" style="width:300px;max-width:100%;filter:drop-shadow(0 4px 24px rgba(0,0,0,.45));">
            </div>
            <div>
                <p class="brand-headline">{{ $brandHeadline }}</p>
                <p class="brand-sub">{{ $brandSubtitle }}</p>
            </div>
            <div class="pills">
                @foreach($brandPoints as $point)
                <span class="pill"><span class="pill-dot"></span>{{ $point }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="form-side">
        <div class="card">

            <span class="badge"><span class="badge-dot"></span>{{ __("Sa'ee Logistics Services Portal") }}</span>
            <h1>{{ __('Welcome back') }}</h1>
            <p class="sub">{{ __('Sign in to your account') }}</p>

            @if ($errors->any())
                <div class="alert alert-err">{{ $errors->first() }}</div>
            @endif
            @if (session('status'))
                <div class="alert alert-ok">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('portal.login') }}" id="loginForm" novalidate>
                @csrf

                {{-- Username or Phone --}}
                <div class="field f1">
                    <label class="field-label" for="loginInput">{{ __('Username or Phone Number') }}</label>
                    <div class="field-wrap{{ $errors->has('login') ? ' has-error' : '' }}">
                        <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <input id="loginInput" name="login" type="text" class="{{ $errors->has('login') ? 'has-error' : '' }}"
                               value="{{ old('login') }}" placeholder="{{ __('Username or phone number') }}" autocomplete="username" autofocus>
                    </div>
                </div>

                {{-- Password --}}
                <div class="field f2">
                    <label class="field-label" for="password">{{ __('Password') }}</label>
                    <div class="field-wrap">
                        <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input id="password" name="password" type="password" placeholder="••••••••••" autocomplete="current-password">
                        <button type="button" class="pwd-btn" id="pwdToggle" aria-label="{{ __('Toggle password') }}">
                            <svg id="eyeIcon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="extras f3">
                    <label class="check-wrap">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        {{ __('Remember me') }}
                    </label>
                    <a href="{{ route('portal.forgot-password') }}" class="forgot">{{ __('Forgot password?') }}</a>
                </div>

                <button type="submit" class="btn f4" id="submitBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">{{ __('Sign In') }}</span>
                    <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <p class="card-footer f5">&copy; {{ date('Y') }} Sa'ee LogisticsServices. {{ __('All rights reserved.') }}</p>
        </div>
    </div>

</div>

<script>
(function () {
    /* ── Particles ─────────────────────────────── */
    const canvas = document.getElementById('cvs');
    const ctx    = canvas.getContext('2d');
    let W, H, pts = [];
    const N = 70, LINK = 130;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
    resize(); window.addEventListener('resize', resize);
    class Pt {
        constructor() { this.init(); }
        init() { this.x = Math.random()*W; this.y = Math.random()*H; this.vx = (Math.random()-.5)*.35; this.vy = (Math.random()-.5)*.35; this.r = Math.random()*1.2+.4; this.a = Math.random()*.35+.08; }
        step() { this.x += this.vx; this.y += this.vy; if(this.x<0||this.x>W)this.vx*=-1; if(this.y<0||this.y>H)this.vy*=-1; }
        draw() { ctx.beginPath(); ctx.arc(this.x,this.y,this.r,0,Math.PI*2); ctx.fillStyle=`rgba(255,255,255,${this.a})`; ctx.fill(); }
    }
    for(let i=0;i<N;i++) pts.push(new Pt());
    function loop() {
        ctx.clearRect(0,0,W,H);
        pts.forEach(p=>{p.step();p.draw();});
        for(let i=0;i<N;i++) for(let j=i+1;j<N;j++){
            const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);
            if(d<LINK){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y);ctx.strokeStyle=`rgba(220,38,38,${.07*(1-d/LINK)})`;ctx.lineWidth=.6;ctx.stroke();}
        }
        requestAnimationFrame(loop);
    }
    loop();

    /* ── Password toggle ───────────────────────── */
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

    /* ── Form submit ───────────────────────────── */
    document.getElementById('loginForm').addEventListener('submit', () => {
        document.getElementById('submitBtn').classList.add('loading');
    });
})();
</script>
</body>
</html>
