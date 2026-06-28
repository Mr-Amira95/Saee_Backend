<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — Sa'ee Portal</title>
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

        html, body { height: 100%; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); }

        #cvs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }

        .blob { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(80px); }
        .blob-1 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(185,28,28,0.2) 0%, transparent 65%); bottom: -200px; left: -120px; animation: bd 11s ease-in-out infinite alternate; }
        .blob-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(220,38,38,0.1) 0%, transparent 65%); top: -80px; right: 8%; animation: bd 8s ease-in-out infinite alternate-reverse; }
        @keyframes bd { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(50px,-50px) scale(1.15); } }

        .page { position: relative; z-index: 1; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }

        .card { width: 100%; max-width: 440px; background: var(--card-bg); border: 1px solid rgba(220,38,38,0.1); border-radius: 22px; padding: 48px 42px; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); box-shadow: 0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px rgba(0,0,0,0.65); opacity: 0; transform: translateY(22px); animation: card-in .8s .1s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes card-in { to { opacity: 1; transform: translateY(0); } }

        .logo { margin-bottom: 28px; }

        h1 { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.03em; margin-bottom: 8px; }
        .desc { color: var(--text-sub); font-size: .88rem; line-height: 1.55; margin-bottom: 28px; }

        .alert { border-radius: 11px; padding: 12px 16px; font-size: .84rem; margin-bottom: 20px; }
        .alert-err { background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.18); color: #fca5a5; animation: shake .45s cubic-bezier(0.36,0.07,0.19,0.97); }
        .alert-ok  { background: rgba(34,197,94,0.07); border: 1px solid rgba(34,197,94,0.18); color: #86efac; }
        @keyframes shake { 0%,100% { transform: translateX(0); } 15%,45%,75% { transform: translateX(-5px); } 30%,60%,90% { transform: translateX(5px); } }

        .field { margin-bottom: 20px; position: relative; z-index: 10; }
        .field-label { display: block; font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.5); letter-spacing: .07em; text-transform: uppercase; margin-bottom: 8px; }
        .field-wrap { position: relative; }
        input[type="tel"] { width: 100%; padding: 13px 44px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 11px; color: #fff; font-size: .9rem; font-family: 'Inter', sans-serif; outline: none; transition: border-color .2s, box-shadow .2s, background .2s; -webkit-appearance: none; }
        input[type="tel"]:focus { border-color: rgba(220,38,38,.4); background: rgba(220,38,38,.025); box-shadow: 0 0 0 3px rgba(220,38,38,.07); }

        /* Phone country-code picker */
        .phone-wrap { display:flex; align-items:stretch; border:1px solid var(--in-bdr); border-radius:11px; background:var(--in-bg); transition:border-color .2s,box-shadow .2s,background .2s; position:relative; overflow:visible; }
        .phone-wrap:focus-within { border-color:rgba(220,38,38,.4); background:rgba(220,38,38,.025); box-shadow:0 0 0 3px rgba(220,38,38,.07); }
        .country-btn { display:flex; align-items:center; gap:5px; padding:0 9px 0 12px; background:none; border:none; border-right:1px solid var(--in-bdr); color:#fff; cursor:pointer; font-family:'Inter',sans-serif; font-size:.82rem; font-weight:500; white-space:nowrap; transition:background .2s; border-radius:11px 0 0 11px; }
        .country-btn:hover,.country-btn.open { background:rgba(255,255,255,.06); }
        .country-flag { font-size:1rem; line-height:1; }
        .country-chevron { transition:transform .2s; margin-left:2px; }
        .country-btn.open .country-chevron { transform:rotate(180deg); }
        .phone-wrap input[type="tel"] { background:transparent !important; border:none !important; box-shadow:none !important; border-radius:0 11px 11px 0 !important; padding-left:12px !important; padding-right:12px !important; width:auto !important; flex:1; min-width:0; padding-top:13px; padding-bottom:13px; }
        .country-dropdown { position:absolute; top:calc(100% + 5px); left:0; width:215px; background:#0d0f22; border:1px solid rgba(255,255,255,.15); border-radius:11px; z-index:999; box-shadow:0 10px 28px rgba(0,0,0,.75); display:none; max-height:252px; overflow-y:auto; }
        .country-dropdown.open { display:block; }
        .country-option { display:flex; align-items:center; gap:8px; padding:9px 12px; cursor:pointer; font-size:.81rem; color:rgba(255,255,255,.92); transition:background .15s; }
        .country-option:hover { background:rgba(255,255,255,.08); color:#fff; }
        .country-option.active { background:rgba(220,38,38,.15); color:#fff; }
        .country-option .opt-flag { font-size:.95rem; flex-shrink:0; }
        .country-option .opt-name { flex:1; }
        .country-option .opt-dial { color:rgba(255,255,255,.65); font-size:.78rem; flex-shrink:0; }

        .btn { width: 100%; padding: 14px 24px; background: linear-gradient(135deg, var(--red-deep) 0%, var(--red) 55%, #f87171 100%); background-size: 200% 200%; color: #fff; border: none; border-radius: 11px; font-size: .9rem; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; position: relative; overflow: hidden; transition: background-position .35s, transform .12s, box-shadow .2s; box-shadow: 0 4px 20px rgba(220,38,38,.3); letter-spacing: .015em; margin-bottom: 20px; }
        .btn:hover { background-position: 100% 100%; transform: translateY(-2px); box-shadow: 0 10px 32px rgba(220,38,38,.48); }
        .btn:active { transform: translateY(0); }
        .btn::after { content: ''; position: absolute; top: 0; left: -100%; width: 55%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,.17), transparent); transform: skewX(-20deg); transition: left .55s; }
        .btn:hover::after { left: 160%; }
        .btn.loading { pointer-events: none; opacity: .75; }
        .spinner { width: 18px; height: 18px; display: none; border: 2px solid rgba(255,255,255,.25); border-top-color: #fff; border-radius: 50%; animation: spin .65s linear infinite; }
        .btn.loading .spinner { display: block; }
        .btn.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .back { display: flex; align-items: center; justify-content: center; gap: 7px; text-decoration: none; color: var(--text-sub); font-size: .84rem; font-weight: 500; transition: color .2s; }
        .back:hover { color: #fff; }
        .back svg { transition: transform .2s; }
        .back:hover svg { transform: translateX(-4px); }

        .divider { border: none; border-top: 1px solid rgba(255,255,255,0.06); margin: 22px 0; }
        .footer { text-align: center; color: var(--text-dim); font-size: .73rem; margin-top: 24px; }

        .f1 { animation: fu .5s .3s both; }
        .f2 { animation: fu .5s .38s both; }
        .f3 { animation: fu .5s .46s both; }
        .f4 { animation: fu .5s .54s both; }
        .f5 { animation: fu .5s .62s both; }
        @keyframes fu { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 480px) { .card { padding: 32px 22px; border-radius: 16px; } }
    </style>
</head>
<body>

<canvas id="cvs"></canvas>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="page">
    <div class="card">

        <div class="logo">
            <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="42" height="42" style="object-fit:contain;border-radius:10px;">
        </div>

        <h1 class="f1">Reset your password</h1>
        <p class="desc f1">Enter your phone number and we'll send a reset link to the email address on file.</p>

        @if ($errors->any())
            <div class="alert alert-err">{{ $errors->first() }}</div>
        @endif
        @if (session('status'))
            <div class="alert alert-ok">
                <strong>Email sent!</strong> {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('portal.forgot-password.send') }}" id="resetForm" novalidate>
            @csrf

            <div class="field f2">
                <label class="field-label" for="fpPhoneLocal">Phone Number</label>
                <div class="field-wrap phone-wrap">
                    <button type="button" class="country-btn" id="fpCountryBtn" aria-label="Select country code">
                        <span class="country-flag" id="fpCountryFlag">🇯🇴</span>
                        <span class="country-dial" id="fpCountryDial">+962</span>
                        <svg class="country-chevron" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="country-dropdown" id="fpCountryDropdown">
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
                    <input id="fpPhoneLocal" type="tel" placeholder="7xxxxxxxx" autocomplete="tel" autofocus>
                    <input type="hidden" name="phone" id="fpPhoneHidden">
                </div>
            </div>

            <button type="submit" class="btn f3" id="submitBtn">
                <div class="spinner"></div>
                <span class="btn-text">Send Reset Link</span>
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>

        <hr class="divider">

        <a href="{{ route('portal.login') }}" class="back f4">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Sign In
        </a>

        <p class="footer">&copy; {{ date('Y') }} Sa'ee LogisticsServices</p>
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
        constructor() { this.x=Math.random()*W;this.y=Math.random()*H;this.vx=(Math.random()-.5)*.32;this.vy=(Math.random()-.5)*.32;this.r=Math.random()*1.1+.4;this.a=Math.random()*.3+.07; }
        step() { this.x+=this.vx;this.y+=this.vy;if(this.x<0||this.x>W)this.vx*=-1;if(this.y<0||this.y>H)this.vy*=-1; }
        draw() { ctx.beginPath();ctx.arc(this.x,this.y,this.r,0,Math.PI*2);ctx.fillStyle=`rgba(255,255,255,${this.a})`;ctx.fill(); }
    }
    for(let i=0;i<N;i++) pts.push(new Pt());
    function loop() {
        ctx.clearRect(0,0,W,H);
        pts.forEach(p=>{p.step();p.draw();});
        for(let i=0;i<N;i++) for(let j=i+1;j<N;j++){
            const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);
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

    btn.addEventListener('click', e => {
        e.stopPropagation();
        btn.classList.toggle('open');
        dropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => {
        btn.classList.remove('open');
        dropdown.classList.remove('open');
    });
    dropdown.querySelectorAll('.country-option').forEach(opt => {
        opt.addEventListener('click', () => {
            dropdown.querySelectorAll('.country-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            flagEl.textContent = opt.dataset.flag;
            dialEl.textContent = opt.dataset.dial;
            btn.classList.remove('open');
            dropdown.classList.remove('open');
        });
    });

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
