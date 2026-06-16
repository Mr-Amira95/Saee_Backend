<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <script>
            (function() {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('light-theme');
                }
            })();
        </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->meta_title ?: $page->title }} — {{ $settings['site_name'] }}</title>
        <meta name="description" content="{{ $page->meta_description ?: substr(strip_tags($page->content), 0, 160) }}">

        <!-- Google Fonts: Outfit -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Custom Vanilla CSS -->
        <style>
            :root {
                --primary-gradient: linear-gradient(135deg, #760400 0%, #e51700 100%);
                --primary-color: #e51700;
                --primary-dark: #760400;
                --accent-color: #ff523d;
                --bg-dark: #0a0505;
                --bg-card: rgba(20, 10, 10, 0.6);
                --bg-input: rgba(255, 255, 255, 0.05);
                --text-light: #ffffff;
                --text-muted: #c9c9d4;
                --border-color: rgba(255, 255, 255, 0.1);
                --glow-shadow: 0 8px 32px 0 rgba(229, 23, 0, 0.2);
                --transition-fast: 0.2s ease;
                --transition-normal: 0.3s ease;
            }

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            html, body {
                font-family: 'Outfit', sans-serif;
                background-color: var(--bg-dark);
                color: var(--text-light);
                overflow-x: hidden;
                line-height: 1.6;
            }

            /* Container */
            .container {
                max-width: 900px;
                margin: 0 auto;
                padding: 0 2rem;
            }

            /* Header Section */
            header {
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid var(--border-color);
                position: sticky;
                top: 0;
                z-index: 100;
                background: rgba(10, 5, 5, 0.75);
                transition: background var(--transition-fast);
            }

            .nav-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                height: 80px;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 2rem;
            }

            .logo-link {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
                color: var(--text-light);
                font-weight: 700;
                font-size: 1.5rem;
            }

            .logo-img {
                height: 48px;
                width: auto;
                object-fit: contain;
                filter: drop-shadow(0 2px 8px rgba(229, 23, 0, 0.3));
            }

            nav ul {
                display: flex;
                list-style: none;
                gap: 2rem;
                align-items: center;
            }

            nav a {
                color: var(--text-muted);
                text-decoration: none;
                font-size: 1rem;
                font-weight: 500;
                transition: color var(--transition-fast);
            }

            nav a:hover, nav a.active {
                color: var(--text-light);
            }

            .auth-buttons {
                display: flex;
                gap: 1rem;
                align-items: center;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                text-decoration: none;
                font-size: 0.95rem;
                cursor: pointer;
                transition: all var(--transition-fast);
                border: 1px solid transparent;
            }

            .btn-secondary {
                background: var(--bg-input);
                color: var(--text-light);
                border: 1px solid var(--border-color);
            }

            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.2);
                transform: translateY(-2px);
            }

            /* Main Page Content styling */
            .page-content-wrapper {
                padding: 5rem 0 7rem 0;
            }

            .page-header {
                border-bottom: 1px solid var(--border-color);
                padding-bottom: 2rem;
                margin-bottom: 3rem;
            }

            .page-title {
                font-size: 2.75rem;
                font-weight: 800;
                letter-spacing: -1px;
                line-height: 1.2;
                background: var(--primary-gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .page-meta {
                font-size: 0.85rem;
                color: var(--text-muted);
                margin-top: 0.5rem;
            }

            .rich-text {
                font-size: 1.05rem;
                color: var(--text-muted);
                line-height: 1.8;
            }

            .rich-text p {
                margin-bottom: 1.5rem;
            }

            .rich-text h2 {
                font-size: 1.75rem;
                font-weight: 700;
                color: var(--text-light);
                margin: 2.5rem 0 1rem 0;
            }

            .rich-text h3 {
                font-size: 1.35rem;
                font-weight: 700;
                color: var(--text-light);
                margin: 2rem 0 1rem 0;
            }

            .rich-text ul, .rich-text ol {
                margin-bottom: 1.5rem;
                padding-left: 2rem;
            }

            .rich-text li {
                margin-bottom: 0.5rem;
            }

            /* Footer */
            footer {
                background: #060303;
                border-top: 1px solid var(--border-color);
                padding: 4rem 0 2rem 0;
            }

            .footer-grid {
                display: grid;
                grid-template-columns: 1.5fr 1fr 1fr 1fr;
                gap: 4rem;
                margin-bottom: 4rem;
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 2rem;
            }

            .footer-about p {
                color: var(--text-muted);
                font-size: 0.95rem;
                margin-top: 1.5rem;
                max-width: 300px;
            }

            .footer-links h4 {
                font-size: 1.1rem;
                font-weight: 700;
                margin-bottom: 1.5rem;
            }

            .footer-links ul {
                list-style: none;
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }

            .footer-links a {
                color: var(--text-muted);
                text-decoration: none;
                font-size: 0.95rem;
                transition: color var(--transition-fast);
            }

            .footer-links a:hover {
                color: var(--text-light);
            }

            .footer-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 2rem;
                border-top: 1px solid rgba(255, 255, 255, 0.05);
                font-size: 0.85rem;
                color: var(--text-muted);
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 2rem;
            }

            /* ─── Light Mode Overrides ───────────────────────── */
            html.light-theme {
                --bg-dark: #f8fafc;
                --bg-card: rgba(255, 255, 255, 0.85);
                --bg-input: rgba(15, 23, 42, 0.04);
                --text-light: #0f172a;
                --text-muted: #475569;
                --border-color: rgba(15, 23, 42, 0.08);
            }
            html.light-theme body {
                background-color: var(--bg-dark);
                color: var(--text-light);
            }
            html.light-theme header {
                background: rgba(255, 255, 255, 0.8);
            }
            html.light-theme .logo-link {
                color: #0f172a;
            }
            html.light-theme .btn-secondary {
                background: var(--bg-input);
                color: var(--text-light);
                border: 1px solid var(--border-color);
            }
            html.light-theme .btn-secondary:hover {
                background: rgba(15, 23, 42, 0.08);
            }
            html.light-theme nav a {
                color: var(--text-muted);
            }
            html.light-theme nav a:hover, html.light-theme nav a.active {
                color: var(--text-light);
            }
            html.light-theme .page-title {
                color: #0f172a;
            }
            html.light-theme .rich-text {
                color: var(--text-muted);
            }
            html.light-theme .rich-text h2, html.light-theme .rich-text h3 {
                color: #0f172a;
            }
            html.light-theme footer {
                background: #ffffff;
                color: #0f172a;
            }
            html.light-theme footer h4 {
                color: #0f172a;
            }
            html.light-theme footer a {
                color: var(--text-muted);
            }
            html.light-theme footer a:hover {
                color: #0f172a;
            }
            html.light-theme .footer-bottom {
                color: var(--text-muted);
                border-top: 1px solid rgba(15, 23, 42, 0.05);
            }

            /* ─── RTL Directional Overrides ──────────────────── */
            html[dir="rtl"] nav ul {
                flex-direction: row-reverse;
            }
            html[dir="rtl"] .footer-links ul {
                text-align: right;
            }
        </style>
    </head>
    <body>

        <!-- Header -->
        <header>
            <div class="nav-container">
                <a href="{{ route('public.home') }}" class="logo-link" id="homeLogoLink">
                    <img src="{{ asset('logo.png') }}" alt="{{ $settings['site_name'] }} Logo" class="logo-img">
                    <span style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px;">{{ $settings['site_name'] }}</span>
                </a>
                <nav aria-label="Main Navigation">
                    <ul>
                        <li><a href="{{ route('public.home') }}" id="navHome">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('public.home') }}#services" id="navServices">{{ __('Services') }}</a></li>
                        <li><a href="{{ route('public.home') }}#about" id="navAbout">{{ __('About Us') }}</a></li>
                        @foreach($headerPages as $hp)
                            <li><a href="{{ route('public.page', $hp->slug) }}" class="{{ $hp->id === $page->id ? 'active' : '' }}">{{ $hp->title }}</a></li>
                        @endforeach
                        <li><a href="{{ route('public.home') }}#contact" id="navContact">{{ __('Contact') }}</a></li>
                    </ul>
                </nav>
                <div class="auth-buttons">
                    {{-- Language Switcher --}}
                    @if(app()->getLocale() === 'en')
                        <a href="{{ route('lang.switch', 'ar') }}" class="btn btn-secondary" style="padding: 0.5rem 1rem;">عربي</a>
                    @else
                        <a href="{{ route('lang.switch', 'en') }}" class="btn btn-secondary" style="padding: 0.5rem 1rem;">EN</a>
                    @endif

                    {{-- Theme Switcher --}}
                    <button class="btn btn-secondary" id="themeToggler" onclick="toggleTheme()" style="padding: 0.5rem 1rem;" title="Toggle Theme">
                        <svg id="themeMoon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                        <svg id="themeSun" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m8.942-8.942h-2.25M4.313 12H2.063m15.122-6.938l-1.591 1.591M6.818 17.182l-1.591 1.591m12.94 0l-1.591-1.591M6.818 6.818L5.227 5.227M12 9a3 3 0 100 6 3 3 0 000-6z"/></svg>
                    </button>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/admin/dashboard') }}" class="btn btn-secondary" id="btnDashboard">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-secondary" id="btnLogIn">{{ __('Admin Login') }}</a>
                        @endauth
                    @else
                        <a href="{{ route('public.home') }}#contact" class="btn btn-primary" id="btnGetQuoteNav">{{ __('Get a Quote') }}</a>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container page-content-wrapper">
            <article>
                <header class="page-header">
                    <h1 class="page-title">{{ $page->title }}</h1>
                    <div class="page-meta">
                        Published on {{ $page->created_at->format('F d, Y') }}
                    </div>
                </header>

                <div class="rich-text">
                    {!! $page->content !!}
                </div>
            </article>
        </main>

        <!-- Footer -->
        <footer>
            <div class="footer-grid">
                <div class="footer-about">
                    <div style="display:flex; align-items:center; gap: 10px; margin-bottom:1.5rem;">
                        <img src="{{ asset('logo.png') }}" alt="{{ $settings['site_name'] }} Logo Footer" class="logo-img" style="height:38px;">
                        <span style="font-size: 1.15rem; font-weight: 800; letter-spacing: -0.5px; color:var(--text-light)">{{ $settings['site_name'] }}</span>
                    </div>
                    <p>Building paths, matching schedules, and accelerating commerce through premium logistics solutions across Jordan.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ route('public.home') }}" id="footerNavHome">Home</a></li>
                        <li><a href="{{ route('public.home') }}#services" id="footerNavServices">Services</a></li>
                        <li><a href="{{ route('public.home') }}#about" id="footerNavAbout">About Us</a></li>
                        <li><a href="{{ route('public.home') }}#contact" id="footerNavContact">Contact Support</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Social Media</h4>
                    <ul>
                        @if($settings['social_facebook']) <li><a href="{{ $settings['social_facebook'] }}" target="_blank">Facebook</a></li> @endif
                        @if($settings['social_twitter']) <li><a href="{{ $settings['social_twitter'] }}" target="_blank">Twitter / X</a></li> @endif
                        @if($settings['social_instagram']) <li><a href="{{ $settings['social_instagram'] }}" target="_blank">Instagram</a></li> @endif
                        @if($settings['social_linkedin']) <li><a href="{{ $settings['social_linkedin'] }}" target="_blank">LinkedIn</a></li> @endif
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Company Policies</h4>
                    <ul>
                        @foreach($headerPages as $hp)
                            <li><a href="{{ route('public.page', $hp->slug) }}">{{ $hp->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="container footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $settings['site_name'] }} Services. All rights reserved.</p>
                <p>Designed with absolute precision.</p>
            </div>
        </footer>
        <script>
            // Theme toggler logic
            function toggleTheme() {
                const html = document.documentElement;
                const isLight = html.classList.toggle('light-theme');
                localStorage.setItem('theme', isLight ? 'light' : 'dark');
                updateThemeIcons();
            }

            function updateThemeIcons() {
                const isLight = document.documentElement.classList.contains('light-theme');
                const sun = document.getElementById('themeSun');
                const moon = document.getElementById('themeMoon');
                if (sun && moon) {
                    if (isLight) {
                        sun.style.display = 'none';
                        moon.style.display = 'block';
                    } else {
                        sun.style.display = 'block';
                        moon.style.display = 'none';
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', updateThemeIcons);
        </script>
    </body>
</html>
