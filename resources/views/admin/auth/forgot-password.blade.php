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
    <title>Reset Password — Sa'ee LogisticsServices</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:       #dc2626;
            --red-dark:  #991b1b;
            --red-deep:  #7f1d1d;
            --red-light: #ef4444;
            --bg:        #07091a;
            --card-bg:   rgba(7,10,28,0.9);
            --text:      #f1f5f9;
            --text-sub:  #94a3b8;
            --text-dim:  #475569;
            --in-bg:     rgba(255,255,255,0.035);
            --in-bdr:    rgba(255,255,255,0.07);
        }

        html, body {
            height: 100%; min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ── Canvas ─────────── */
        #cvs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }

        /* ── Blobs ──────────── */
        .blob { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(80px); }
        .blob-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(185,28,28,0.2) 0%, transparent 65%);
            bottom: -200px; left: -120px;
            animation: bd 11s ease-in-out infinite alternate;
        }
        .blob-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(220,38,38,0.1) 0%, transparent 65%);
            top: -80px; right: 8%;
            animation: bd 8s ease-in-out infinite alternate-reverse;
        }
        @keyframes bd { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(50px,-50px) scale(1.15); } }

        /* ── Page layout ────── */
        .page {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 20px;
        }

        /* ── Card ────────────── */
        .card {
            width: 100%; max-width: 440px;
            background: var(--card-bg);
            border: 1px solid rgba(220,38,38,0.1);
            border-radius: 22px;
            padding: 48px 42px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px rgba(0,0,0,0.65);
            opacity: 0; transform: translateY(22px);
            animation: card-in .8s .1s cubic-bezier(0.16,1,0.3,1) forwards;
        }
        @keyframes card-in { to { opacity: 1; transform: translateY(0); } }

        /* Logo */
        .logo { margin-bottom: 28px; }

        /* Icon container */
        .icon-wrap {
            width: 56px; height: 56px;
            background: rgba(220,38,38,0.1);
            border: 1px solid rgba(220,38,38,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            animation: icon-glow 3s ease-in-out infinite;
        }
        @keyframes icon-glow {
            0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,0); }
            50%      { box-shadow: 0 0 0 6px rgba(220,38,38,0.12); }
        }

        h1 { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.03em; margin-bottom: 8px; }
        .desc { color: var(--text-sub); font-size: .88rem; line-height: 1.55; margin-bottom: 28px; }

        /* ── Alert ────────────── */
        .alert {
            border-radius: 11px; padding: 12px 16px;
            font-size: .84rem; margin-bottom: 20px;
        }
        .alert-err {
            background: rgba(220,38,38,0.08);
            border: 1px solid rgba(220,38,38,0.18);
            color: #fca5a5;
            animation: shake .45s cubic-bezier(0.36,0.07,0.19,0.97);
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

        /* ── Field ─────────────── */
        .field { margin-bottom: 20px; }
        .field-label {
            display: block; font-size: .72rem; font-weight: 600;
            color: rgba(255,255,255,.5); letter-spacing: .07em;
            text-transform: uppercase; margin-bottom: 8px;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 14px; top: 50%; translate: 0 -50%;
            color: rgba(255,255,255,.22); pointer-events: none; transition: color .2s;
        }
        .field-wrap:focus-within .field-icon { color: rgba(220,38,38,.55); }
        input[type="email"] {
            width: 100%; padding: 13px 44px;
            background: var(--in-bg);
            border: 1px solid var(--in-bdr);
            border-radius: 11px; color: #fff; font-size: .9rem;
            font-family: 'Inter', sans-serif; outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }
        input[type="email"]:focus {
            border-color: rgba(220,38,38,.4);
            background: rgba(220,38,38,.025);
            box-shadow: 0 0 0 3px rgba(220,38,38,.07);
        }

        /* ── Button ─────────────── */
        .btn {
            width: 100%; padding: 14px 24px;
            background: linear-gradient(135deg, var(--red-deep) 0%, var(--red) 55%, #f87171 100%);
            background-size: 200% 200%;
            color: #fff; border: none; border-radius: 11px;
            font-size: .9rem; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            position: relative; overflow: hidden;
            transition: background-position .35s, transform .12s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(220,38,38,.3);
            letter-spacing: .015em; margin-bottom: 20px;
        }
        .btn:hover {
            background-position: 100% 100%;
            transform: translateY(-2px);
            box-shadow: 0 10px 32px rgba(220,38,38,.48);
        }
        .btn:active { transform: translateY(0); }
        .btn::after {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 55%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.17), transparent);
            transform: skewX(-20deg); transition: left .55s;
        }
        .btn:hover::after { left: 160%; }
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

        /* ── Back link ─────────── */
        .back {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            text-decoration: none; color: var(--text-sub);
            font-size: .84rem; font-weight: 500;
            transition: color .2s;
        }
        .back:hover { color: #fff; }
        .back svg { transition: transform .2s; }
        .back:hover svg { transform: translateX(-4px); }

        /* ── Divider ────────────── */
        .divider {
            border: none; border-top: 1px solid rgba(255,255,255,0.06);
            margin: 22px 0;
        }

        /* ── Footer ─────────────── */
        .footer { text-align: center; color: var(--text-dim); font-size: .73rem; margin-top: 24px; }

        /* Stagger */
        .f1 { animation: fu .5s .3s both; }
        .f2 { animation: fu .5s .38s both; }
        .f3 { animation: fu .5s .46s both; }
        .f4 { animation: fu .5s .54s both; }
        .f5 { animation: fu .5s .62s both; }
        @keyframes fu { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 480px) {
            .card { padding: 32px 22px; border-radius: 16px; }
        }
    </style>
</head>
<body>

<canvas id="cvs"></canvas>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="page">
    <div class="card">

        {{-- Logo mark --}}
        <div class="logo">
            <svg viewBox="0 0 200 200" width="42" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <radialGradient id="rg3" cx="38%" cy="35%" r="65%">
                        <stop offset="0%" stop-color="#f87171"/>
                        <stop offset="50%" stop-color="#dc2626"/>
                        <stop offset="100%" stop-color="#7f1d1d"/>
                    </radialGradient>
                </defs>
                <circle cx="100" cy="100" r="95" fill="url(#rg3)"/>
                <path d="M22,158 C38,88 90,52 172,46 L171,68 C96,74 52,108 44,165 Z" fill="white" opacity=".96"/>
                <path d="M40,132 C56,78 100,53 172,64 L171,83 C108,73 64,95 57,142 Z" fill="white" opacity=".89"/>
                <path d="M60,108 C74,69 114,55 172,78 L171,95 C118,73 80,88 72,120 Z" fill="white" opacity=".82"/>
            </svg>
        </div>

        {{-- Icon --}}
        <div class="icon-wrap f1">
            <svg width="26" height="26" fill="none" stroke="#ef4444" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>

        <h1 class="f2">Reset your password</h1>
        <p class="desc f2">Enter your phone number and we'll send a verification code to your WhatsApp.</p>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="alert alert-err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.password.email') }}" id="resetForm" novalidate>
            @csrf

            <div class="field f3">
                <label class="field-label" for="fpPhoneLocal">Phone Number</label>
                <div class="field-wrap phone-wrap" style="display:flex;align-items:stretch;border:1px solid var(--in-bdr);border-radius:11px;background:var(--in-bg);transition:border-color .2s,box-shadow .2s;">
                    <button type="button" class="country-btn" id="fpCountryBtn" aria-label="Select country code" style="display:flex;align-items:center;gap:5px;padding:0 9px 0 12px;background:none;border:none;border-right:1px solid var(--in-bdr);color:#fff;cursor:pointer;font-family:'Inter',sans-serif;font-size:.82rem;font-weight:500;white-space:nowrap;border-radius:11px 0 0 11px;">
                        <span id="fpCountryFlag">🇯🇴</span>
                        <span id="fpCountryDial">+962</span>
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="fpCountryDropdown" style="position:absolute;top:calc(100% + 5px);left:0;width:215px;background:#0d0f22;border:1px solid rgba(255,255,255,.15);border-radius:11px;z-index:999;box-shadow:0 10px 28px rgba(0,0,0,.75);display:none;max-height:252px;overflow-y:auto;">
                        <div class="country-option active" data-dial="+962" data-flag="🇯🇴" style="display:flex;align-items:center;gap:8px;padding:9px 12px;cursor:pointer;font-size:.81rem;color:rgba(255,255,255,.92);">🇯🇴 Jordan <span style="margin-left:auto;color:rgba(255,255,255,.65);font-size:.78rem;">+962</span></div>
                        <div class="country-option" data-dial="+966" data-flag="🇸🇦" style="display:flex;align-items:center;gap:8px;padding:9px 12px;cursor:pointer;font-size:.81rem;color:rgba(255,255,255,.92);">🇸🇦 Saudi Arabia <span style="margin-left:auto;color:rgba(255,255,255,.65);font-size:.78rem;">+966</span></div>
                        <div class="country-option" data-dial="+971" data-flag="🇦🇪" style="display:flex;align-items:center;gap:8px;padding:9px 12px;cursor:pointer;font-size:.81rem;color:rgba(255,255,255,.92);">🇦🇪 UAE <span style="margin-left:auto;color:rgba(255,255,255,.65);font-size:.78rem;">+971</span></div>
                    </div>
                    <input id="fpPhoneLocal" type="tel" placeholder="7xxxxxxxx" autocomplete="tel" autofocus style="background:transparent;border:none;box-shadow:none;border-radius:0 11px 11px 0;padding:13px 12px;flex:1;color:#fff;font-size:.9rem;font-family:'Inter',sans-serif;outline:none;">
                    <input type="hidden" name="phone" id="fpPhoneHidden">
                </div>
            </div>

            <button type="submit" class="btn f4" id="submitBtn">
                <div class="spinner"></div>
                <span class="btn-text">Send WhatsApp Code</span>
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>

        <hr class="divider">

        <a href="{{ route('admin.login') }}" class="back f5">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Admin Login
        </a>

        <p class="footer">© {{ date('Y') }} Sa'ee LogisticsServices</p>
    </div>
</div>

<script>
(function () {
    /* ── Particles ── */
    const cvs = document.getElementById('cvs');
    const ctx = cvs.getContext('2d');
    let W, H, pts = [];
    const N = 55, LINK = 115;
    function resize() { W = cvs.width = window.innerWidth; H = cvs.height = window.innerHeight; }
    resize(); window.addEventListener('resize', resize);
    class Pt {
        constructor() {
            this.x = Math.random()*W; this.y = Math.random()*H;
            this.vx = (Math.random()-.5)*.32; this.vy = (Math.random()-.5)*.32;
            this.r = Math.random()*1.1+.4; this.a = Math.random()*.3+.07;
        }
        step() {
            this.x += this.vx; this.y += this.vy;
            if (this.x<0||this.x>W) this.vx*=-1;
            if (this.y<0||this.y>H) this.vy*=-1;
        }
        draw() {
            ctx.beginPath(); ctx.arc(this.x,this.y,this.r,0,Math.PI*2);
            ctx.fillStyle = `rgba(255,255,255,${this.a})`; ctx.fill();
        }
    }
    for (let i=0;i<N;i++) pts.push(new Pt());
    function loop() {
        ctx.clearRect(0,0,W,H);
        pts.forEach(p=>{p.step();p.draw();});
        for(let i=0;i<N;i++) for(let j=i+1;j<N;j++){
            const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y, d=Math.sqrt(dx*dx+dy*dy);
            if(d<LINK){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y);ctx.strokeStyle=`rgba(220,38,38,${.065*(1-d/LINK)})`;ctx.lineWidth=.55;ctx.stroke();}
        }
        requestAnimationFrame(loop);
    }
    loop();

    /* ── Country dropdown ── */
    const btn      = document.getElementById('fpCountryBtn');
    const dropdown = document.getElementById('fpCountryDropdown');
    const flagEl   = document.getElementById('fpCountryFlag');
    const dialEl   = document.getElementById('fpCountryDial');
    if (btn && dropdown) {
        btn.addEventListener('click', e => { e.stopPropagation(); dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block'; });
        document.addEventListener('click', () => { dropdown.style.display = 'none'; });
        dropdown.querySelectorAll('.country-option').forEach(opt => {
            opt.addEventListener('click', () => {
                flagEl.textContent = opt.dataset.flag;
                dialEl.textContent = opt.dataset.dial;
                dropdown.style.display = 'none';
            });
        });
    }

    /* ── Submit ── */
    document.getElementById('resetForm').addEventListener('submit', () => {
        let local = document.getElementById('fpPhoneLocal').value.trim();
        if (local.startsWith('0')) local = local.slice(1);
        document.getElementById('fpPhoneHidden').value = dialEl.textContent + local;
        document.getElementById('submitBtn').classList.add('loading');
    });
})();
</script>
</body>
</html>
