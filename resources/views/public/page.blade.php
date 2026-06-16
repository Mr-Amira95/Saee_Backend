<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
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
                        <li><a href="{{ route('public.home') }}" id="navHome">Home</a></li>
                        <li><a href="{{ route('public.home') }}#services" id="navServices">Services</a></li>
                        <li><a href="{{ route('public.home') }}#about" id="navAbout">About Us</a></li>
                        @foreach($headerPages as $hp)
                            <li><a href="{{ route('public.page', $hp->slug) }}" class="{{ $hp->id === $page->id ? 'active' : '' }}">{{ $hp->title }}</a></li>
                        @endforeach
                        <li><a href="{{ route('public.home') }}#contact" id="navContact">Contact</a></li>
                    </ul>
                </nav>
                <div class="auth-buttons">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/admin/dashboard') }}" class="btn btn-secondary" id="btnDashboard">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-secondary" id="btnLogIn">Log in</a>
                        @endauth
                    @else
                        <a href="{{ route('public.home') }}#contact" class="btn btn-primary" id="btnGetQuoteNav">Get a Quote</a>
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
    </body>
</html>
