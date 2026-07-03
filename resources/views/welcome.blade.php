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
        <title>{{ $settings['meta_title'] }}</title>
        <meta name="description" content="{{ $settings['meta_description'] }}">

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
                --transition-slow: 0.5s ease;
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
                scroll-behavior: smooth;
                line-height: 1.6;
            }

            /* Background Grid and Glow */
            body::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1000px;
                background: radial-gradient(circle at 50% -20%, rgba(229, 23, 0, 0.15) 0%, rgba(118, 4, 0, 0.03) 50%, transparent 100%);
                z-index: -1;
                pointer-events: none;
            }

            body::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: 
                 linear-gradient(rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                 linear-gradient(90deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
                background-size: 40px 40px;
                z-index: -2;
                pointer-events: none;
            }

            /* Container */
            .container {
                max-width: 1200px;
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
                position: relative;
            }

            nav a:hover, nav a.active {
                color: var(--text-light);
            }

            nav a::after {
                content: '';
                position: absolute;
                bottom: -6px;
                left: 0;
                width: 0;
                height: 2px;
                background: var(--primary-gradient);
                transition: width var(--transition-fast);
            }

            nav a:hover::after {
                width: 100%;
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

            .btn-primary {
                background: var(--primary-gradient);
                color: var(--text-light);
                box-shadow: var(--glow-shadow);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 40px 0 rgba(229, 23, 0, 0.35);
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

            /* Hero / Slider Section */
            .hero {
                padding: 6rem 0 5rem 0;
                text-align: center;
                position: relative;
            }

            .hero-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(229, 23, 0, 0.1);
                border: 1px solid rgba(229, 23, 0, 0.25);
                padding: 6px 16px;
                border-radius: 100px;
                font-size: 0.9rem;
                font-weight: 600;
                color: var(--accent-color);
                margin-bottom: 2rem;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(229, 23, 0, 0.4); }
                70% { box-shadow: 0 0 0 10px rgba(229, 23, 0, 0); }
                100% { box-shadow: 0 0 0 0 rgba(229, 23, 0, 0); }
            }

            .hero h1 {
                font-size: 3.5rem;
                font-weight: 800;
                line-height: 1.2;
                margin-bottom: 1.5rem;
                letter-spacing: -1px;
            }

            .hero h1 span {
                background: var(--primary-gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .hero p {
                font-size: 1.25rem;
                color: var(--text-muted);
                max-width: 700px;
                margin: 0 auto 3rem auto;
            }

            .hero-actions {
                display: flex;
                gap: 1.5rem;
                justify-content: center;
                margin-bottom: 5rem;
            }

            /* Banner Slider specific styles */
            .banner-slider {
                position: relative;
                width: 100%;
                min-height: 550px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #000;
                border-bottom: 1px solid var(--border-color);
                overflow: hidden;
            }

            .banner-slider .slide {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.8s ease-in-out;
                z-index: 1;
            }

            .banner-slider .slide.active {
                opacity: 1;
                z-index: 2;
            }

            .banner-slider .slide-bg {
                position: absolute;
                inset: 0;
                background-size: cover;
                background-position: center;
                filter: brightness(0.4) contrast(1.1);
            }

            .banner-slider .slide-content {
                position: relative;
                z-index: 3;
                text-align: center;
                padding: 0 2rem;
            }

            .slider-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                border: none;
                background: rgba(255, 255, 255, 0.3);
                cursor: pointer;
                transition: all 0.3s;
            }

            .slider-dot.active {
                background: var(--primary-color) !important;
                width: 24px !important;
                border-radius: 6px !important;
            }

            /* Glassmorphic Interactive Dashboard */
            .interactive-dashboard {
                display: grid;
                grid-template-columns: 1.2fr 0.8fr;
                gap: 2.5rem;
                background: var(--bg-card);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid var(--border-color);
                border-radius: 24px;
                padding: 3rem;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
                text-align: left;
                margin-bottom: 6rem;
            }

            .dashboard-card {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .card-title {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .card-title svg {
                color: var(--primary-color);
            }

            .card-description {
                color: var(--text-muted);
                font-size: 0.95rem;
                margin-bottom: 2rem;
            }

            /* Tracking Form */
            .tracking-wrapper {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .input-group {
                position: relative;
                display: flex;
            }

            .input-group input {
                flex: 1;
                background: var(--bg-input);
                border: 1px solid var(--border-color);
                padding: 1.2rem 1.5rem;
                border-radius: 12px 0 0 12px;
                color: var(--text-light);
                font-family: inherit;
                font-size: 1rem;
                outline: none;
                transition: all var(--transition-fast);
            }

            .input-group input:focus {
                border-color: var(--primary-color);
                background: rgba(255, 255, 255, 0.08);
                box-shadow: 0 0 0 4px rgba(229, 23, 0, 0.15);
            }

            .input-group button {
                border-radius: 0 12px 12px 0;
                border: none;
                padding: 0 2rem;
                font-family: inherit;
            }

            /* Tracking Status Stepper */
            .tracking-results {
                margin-top: 1.5rem;
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: 12px;
                padding: 1.5rem;
                display: none;
            }

            .tracking-results.active {
                display: block;
                animation: fadeIn 0.4s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .status-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            .status-id {
                font-weight: 700;
                color: var(--text-light);
            }

            .status-badge {
                font-size: 0.8rem;
                font-weight: 600;
                background: rgba(46, 213, 115, 0.15);
                color: #2ed573;
                padding: 4px 10px;
                border-radius: 100px;
            }

            .stepper {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                position: relative;
                padding-left: 2rem;
            }

            .stepper::before {
                content: '';
                position: absolute;
                left: 6px;
                top: 8px;
                bottom: 8px;
                width: 2px;
                background: rgba(255, 255, 255, 0.1);
            }

            .stepper-progress {
                content: '';
                position: absolute;
                left: 6px;
                top: 8px;
                height: 66%;
                width: 2px;
                background: var(--primary-color);
                transition: height var(--transition-slow);
            }

            .step {
                position: relative;
            }

            .step::before {
                content: '';
                position: absolute;
                left: -22px;
                top: 6px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: #333;
                border: 2px solid var(--bg-dark);
                z-index: 1;
            }

            .step.completed::before {
                background: var(--primary-color);
                box-shadow: 0 0 8px var(--primary-color);
            }

            .step.active::before {
                background: #2ed573;
                box-shadow: 0 0 8px #2ed573;
            }

            .step-title {
                font-weight: 600;
                font-size: 0.95rem;
                color: var(--text-light);
            }

            .step-desc {
                font-size: 0.85rem;
                color: var(--text-muted);
            }

            /* Calculator Card */
            .calculator-card {
                border-left: 1px solid var(--border-color);
                padding-left: 2.5rem;
            }

            .calc-group {
                display: flex;
                flex-direction: column;
                gap: 1.2rem;
            }

            .select-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .select-group label {
                font-size: 0.85rem;
                font-weight: 600;
                color: var(--text-muted);
            }

            .select-group select, .select-group input {
                background: var(--bg-input);
                border: 1px solid var(--border-color);
                padding: 0.9rem;
                border-radius: 8px;
                color: var(--text-light);
                font-family: inherit;
                outline: none;
                transition: border var(--transition-fast);
            }

            .select-group select:focus, .select-group input:focus {
                border-color: var(--primary-color);
            }

            .select-group select option {
                background-color: var(--bg-dark);
                color: var(--text-light);
            }

            .calc-result {
                margin-top: 1.5rem;
                padding: 1.5rem;
                background: rgba(229, 23, 0, 0.05);
                border: 1px dashed rgba(229, 23, 0, 0.2);
                border-radius: 12px;
                text-align: center;
            }

            .calc-price {
                font-size: 1.8rem;
                font-weight: 800;
                color: var(--text-light);
                margin-top: 0.25rem;
            }

            .calc-price span {
                color: var(--accent-color);
            }

            /* Services Section */
            .services {
                padding: 6rem 0;
            }

            .section-header {
                text-align: center;
                margin-bottom: 4rem;
            }

            .section-header h2 {
                font-size: 2.5rem;
                font-weight: 800;
                margin-bottom: 1rem;
            }

            .section-header p {
                color: var(--text-muted);
                font-size: 1.1rem;
                max-width: 600px;
                margin: 0 auto;
            }

            .services-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 2rem;
            }

            .service-card {
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 16px;
                padding: 2.5rem 2rem;
                transition: all var(--transition-normal);
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .service-card:hover {
                transform: translateY(-8px);
                border-color: rgba(229, 23, 0, 0.3);
                box-shadow: 0 15px 30px rgba(229, 23, 0, 0.05);
            }

            .service-icon {
                width: 60px;
                height: 60px;
                border-radius: 12px;
                background: rgba(229, 23, 0, 0.08);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--accent-color);
                transition: all var(--transition-fast);
            }

            .service-card:hover .service-icon {
                background: var(--primary-gradient);
                color: var(--text-light);
                transform: scale(1.05);
            }

            .service-card h3 {
                font-size: 1.25rem;
                font-weight: 700;
            }

            .service-card p {
                color: var(--text-muted);
                font-size: 0.95rem;
                line-height: 1.6;
            }

            /* About/Fleet Section with visual focus */
            .fleet-section {
                padding: 6rem 0;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
                align-items: center;
            }

            .fleet-content h2 {
                font-size: 2.5rem;
                font-weight: 800;
                line-height: 1.2;
                margin-bottom: 1.5rem;
            }

            .fleet-content p {
                color: var(--text-muted);
                margin-bottom: 2rem;
                font-size: 1.05rem;
            }

            .stats-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }

            .stat-box {
                border-left: 3px solid var(--primary-color);
                padding-left: 1.2rem;
            }

            .stat-number {
                font-size: 2.2rem;
                font-weight: 800;
                color: var(--text-light);
                line-height: 1;
                margin-bottom: 0.5rem;
            }

            .stat-label {
                font-size: 0.9rem;
                color: var(--text-muted);
                font-weight: 500;
            }

            .fleet-visual {
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .visual-bg-glow {
                position: absolute;
                width: 350px;
                height: 350px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(229, 23, 0, 0.2) 0%, transparent 70%);
                z-index: -1;
            }

            /* Main Premium Visual Asset Frame */
            .logo-showcase-card {
                background: linear-gradient(145deg, rgba(20, 10, 10, 0.8), rgba(40, 15, 15, 0.4));
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 24px;
                padding: 3rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 2rem;
                box-shadow: 0 30px 60px rgba(0,0,0,0.4);
                width: 100%;
                max-width: 440px;
                transition: transform var(--transition-normal);
            }

            .logo-showcase-card:hover {
                transform: scale(1.02);
            }

            .showcase-img {
                width: 100%;
                height: auto;
                object-fit: contain;
                filter: drop-shadow(0 10px 20px rgba(229, 23, 0, 0.25));
            }

            .showcase-tag {
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: var(--accent-color);
                border-bottom: 2px solid var(--primary-color);
                padding-bottom: 6px;
            }

            /* Contact Form / CTA Section */
            .cta-contact {
                padding: 6rem 0;
                background: linear-gradient(180deg, transparent 0%, rgba(118, 4, 0, 0.08) 100%);
            }

            .contact-box {
                background: linear-gradient(135deg, rgba(30, 10, 10, 0.8) 0%, rgba(10, 5, 5, 0.9) 100%);
                border: 1px solid var(--border-color);
                border-radius: 24px;
                padding: 4rem;
                display: grid;
                grid-template-columns: 1fr 1.2fr;
                gap: 4rem;
            }

            .contact-info h2 {
                font-size: 2.5rem;
                font-weight: 800;
                margin-bottom: 1.5rem;
            }

            .contact-info p {
                color: var(--text-muted);
                margin-bottom: 3rem;
            }

            .info-item {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 1.5rem;
            }

            .info-icon {
                width: 44px;
                height: 44px;
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid var(--border-color);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--accent-color);
            }

            .info-text {
                font-size: 0.95rem;
            }

            .info-label {
                color: var(--text-muted);
                font-size: 0.8rem;
            }

            .contact-form {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .form-group label {
                font-size: 0.85rem;
                font-weight: 600;
                color: var(--text-muted);
            }

            .form-group input, .form-group textarea {
                background: var(--bg-input);
                border: 1px solid var(--border-color);
                padding: 1rem;
                border-radius: 8px;
                color: var(--text-light);
                font-family: inherit;
                outline: none;
                transition: border var(--transition-fast);
            }

            .form-group input:focus, .form-group textarea:focus {
                border-color: var(--primary-color);
            }

            .form-group textarea {
                resize: none;
                height: 120px;
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
            }

            /* Responsive Adjustments */
            @media (max-width: 992px) {
                .interactive-dashboard {
                    grid-template-columns: 1fr;
                    padding: 2rem;
                }
                .calculator-card {
                    border-left: none;
                    border-top: 1px solid var(--border-color);
                    padding-left: 0;
                    padding-top: 2rem;
                }
                .fleet-section {
                    grid-template-columns: 1fr;
                    gap: 3rem;
                    text-align: center;
                }
                .stats-row {
                    justify-content: center;
                }
                .stat-box {
                    border-left: none;
                    border-top: 3px solid var(--primary-color);
                    padding-top: 0.5rem;
                    padding-left: 0;
                }
                .fleet-visual {
                    order: -1;
                }
                .contact-box {
                    grid-template-columns: 1fr;
                    gap: 3rem;
                    padding: 2.5rem;
                }
                .footer-grid {
                    grid-template-columns: 1fr 1fr;
                    gap: 3rem;
                }
            }

            @media (max-width: 768px) {
                .hero h1 {
                    font-size: 2.5rem;
                }
                nav {
                    display: none; /* In a real project, we'd add a mobile hamburger menu */
                }
                .form-row {
                    grid-template-columns: 1fr;
                }
                .footer-grid {
                    grid-template-columns: 1fr;
                    gap: 2.5rem;
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
            html.light-theme body::before {
                background: radial-gradient(circle at 50% -20%, rgba(229, 23, 0, 0.08) 0%, rgba(118, 4, 0, 0.02) 50%, transparent 100%);
            }
            html.light-theme body::after {
                background-image: 
                 linear-gradient(rgba(15, 23, 42, 0.015) 1px, transparent 1px),
                 linear-gradient(90deg, rgba(15, 23, 42, 0.015) 1px, transparent 1px);
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
            html.light-theme .hero h1 {
                color: #0f172a;
            }
            html.light-theme .hero p {
                color: var(--text-muted);
            }
            html.light-theme .card-title {
                color: #0f172a;
            }
            html.light-theme .card-description {
                color: var(--text-muted);
            }
            html.light-theme .select-group label {
                color: var(--text-muted);
            }
            html.light-theme .select-group select, html.light-theme .select-group input {
                color: #0f172a;
                background: #ffffff;
            }
            html.light-theme .calc-result {
                background: rgba(229, 23, 0, 0.03);
            }
            html.light-theme .calc-price {
                color: #0f172a;
            }
            html.light-theme .service-card h3 {
                color: #0f172a;
            }
            html.light-theme .service-card p {
                color: var(--text-muted);
            }
            html.light-theme .fleet-content h2 {
                color: #0f172a;
            }
            html.light-theme .fleet-content p {
                color: var(--text-muted);
            }
            html.light-theme .stat-number {
                color: #0f172a;
            }
            html.light-theme .stat-label {
                color: var(--text-muted);
            }
            html.light-theme .logo-showcase-card {
                background: linear-gradient(145deg, rgba(255, 255, 255, 0.8), rgba(240, 240, 240, 0.4));
            }
            html.light-theme .contact-info h2 {
                color: #0f172a;
            }
            html.light-theme .contact-info p {
                color: var(--text-muted);
            }
            html.light-theme .info-icon {
                background: rgba(15, 23, 42, 0.02);
                color: var(--accent-color);
            }
            html.light-theme .info-text {
                color: #0f172a;
            }
            html.light-theme .info-label {
                color: var(--text-muted);
            }
            html.light-theme .contact-form input, html.light-theme .contact-form textarea {
                color: #0f172a;
                background: #ffffff;
            }
            html.light-theme .faq-item h3 button {
                color: #0f172a;
            }
            html.light-theme .faq-answer {
                color: var(--text-muted);
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

            /* ─── Chatbot widget styles moved to inline <style> tag below ─── */

            /* ─── [placeholder to preserve structure] ──────────── */
            #_chatbot_placeholder {
                display: none;
                transition: transform var(--transition-fast), box-shadow var(--transition-fast);
            }
            /* styles removed — see inline <style> tag in chatbot widget HTML */

            /* ─── RTL Directional Overrides ──────────────────── */
            html[dir="rtl"] .stepper {
                padding-right: 2rem;
                padding-left: 0;
            }
            html[dir="rtl"] .stepper::before {
                right: 6px;
                left: auto;
            }
            html[dir="rtl"] .stepper-progress {
                right: 6px;
                left: auto;
            }
            html[dir="rtl"] .step::before {
                right: -22px;
                left: auto;
            }
            html[dir="rtl"] .calculator-card {
                border-right: 1px solid var(--border-color);
                border-left: none;
                padding-right: 2.5rem;
                padding-left: 0;
            }
            html[dir="rtl"] .stat-box {
                border-right: 3px solid var(--primary-color);
                border-left: none;
                padding-right: 1.2rem;
                padding-left: 0;
            }
        </style>
    </head>
    <body>

        <!-- Header -->
        <header>
            <div class="container nav-container">
                <a href="{{ route('public.home') }}" class="logo-link" id="homeLogoLink">
                    <img src="{{ asset('saee_logo_dark.png') }}" alt="{{ $settings['site_name'] }} Logo" class="logo-img">
                    <span style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px;">{{ $settings['site_name'] }}</span>
                </a>
                <nav aria-label="Main Navigation">
                    <ul>
                        <li><a href="{{ route('public.home') }}" class="active" id="navHome">{{ __('Home') }}</a></li>
                        <li><a href="#services" id="navServices">{{ __('Services') }}</a></li>
                        <li><a href="#about" id="navAbout">{{ __('About Us') }}</a></li>
                        @if($faqs->count())
                            <li><a href="#faqs">{{ __('FAQs') }}</a></li>
                        @endif
                        @foreach($headerPages as $hp)
                            <li><a href="{{ route('public.page', $hp->slug) }}">{{ $hp->title }}</a></li>
                        @endforeach
                        <li><a href="#contact" id="navContact">{{ __('Contact') }}</a></li>
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
                        <a href="#contact" class="btn btn-primary" id="btnGetQuoteNav">{{ __('Get a Quote') }}</a>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            
            <!-- Hero Section -->
            @if($banners->count())
                <section class="banner-slider">
                    @foreach($banners as $index => $banner)
                        <div class="slide {{ $index === 0 ? 'active' : '' }}">
                            <div class="slide-bg" style="background-image: url('{{ $banner->image_path }}');"></div>
                            <div class="slide-content container">
                                <div class="hero-badge" style="animation: none;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                                    Premium LogisticsSolutions
                                </div>
                                <h1 style="font-size: 3.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 1.5rem; letter-spacing: -1px;">
                                    {!! preg_replace('/(\S+)$/', '<span>$1</span>', $banner->title) !!}
                                </h1>
                                @if($banner->subtitle)
                                    <p style="font-size: 1.25rem; color: var(--text-muted); max-width: 700px; margin: 0 auto 3rem auto;">{{ $banner->subtitle }}</p>
                                @endif
                                <div class="hero-actions" style="margin-bottom: 0;">
                                    @if($banner->link_url)
                                        <a href="{{ $banner->link_url }}" class="btn btn-primary">{{ $banner->link_text ?: 'Ship Now' }}</a>
                                    @endif
                                    <a href="#tracker" class="btn btn-secondary">Track Shipment</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($banners->count() > 1)
                        <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
                            @foreach($banners as $index => $banner)
                                <button onclick="setSlide({{ $index }})" class="slider-dot {{ $index === 0 ? 'active' : '' }}"></button>
                            @endforeach
                        </div>
                    @endif
                </section>
            @else
                <section class="hero" aria-labelledby="heroTitle">
                    <div class="container">
                        <div class="hero-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                            Smart & Swift Delivery Network
                        </div>
                        <h1 id="heroTitle">Logistics Redefined.<br>Speed <span>Perfected.</span></h1>
                        <p>Experience seamless supply chain management, cargo transportation, and real-time shipping solutions designed to keep your business moving ahead.</p>
                        <div class="hero-actions">
                            <a href="#tracker" class="btn btn-primary" id="btnTrackShipmentHero">Track Shipment</a>
                            <a href="#about" class="btn btn-secondary" id="btnLearnMoreHero">Learn More</a>
                        </div>
                    </div>
                </section>
            @endif

            <!-- Glassmorphic Interactive Dashboard (Calculator + Tracker) -->
            <div class="container" style="margin-top: -2rem; position: relative; z-index: 10;">
                <div class="interactive-dashboard" id="tracker">
                    
                    <!-- Tracking Card -->
                    <div class="dashboard-card">
                        <div>
                            <h2 class="card-title">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                Shipment Tracking
                            </h2>
                            <p class="card-description">Check the live status of your domestic and international shipments instantly.</p>
                        </div>
                        
                        <div class="tracking-wrapper">
                            <div class="input-group">
                                <input type="text" id="trackingInput" placeholder="Enter Tracking Number (e.g. SAEE-8742-XP)" aria-label="Tracking Number">
                                <button class="btn btn-primary" id="btnTrackSubmit" onclick="simulateTracking()">Track</button>
                            </div>

                            <!-- Dynamic Results Stepper -->
                            <div class="tracking-results" id="trackingResults">
                                <div class="status-header">
                                    <span class="status-id" id="resultTrackingId">SAEE-8742-XP</span>
                                    <span class="status-badge" id="resultStatus">In Transit</span>
                                </div>
                                <div class="stepper">
                                    <div class="stepper-progress" id="stepperProgress"></div>
                                    <div class="step completed">
                                        <div class="step-title">Order Processed</div>
                                        <div class="step-desc">Sender created shipment record. Riyadh, SA</div>
                                    </div>
                                    <div class="step completed">
                                        <div class="step-title">In Transit</div>
                                        <div class="step-desc">Parcel sorted at main hub. Jeddah sorting center</div>
                                    </div>
                                    <div class="step active" id="latestStep">
                                        <div class="step-title">Out for Delivery</div>
                                        <div class="step-desc">Courier assigned for final destination. Dammam, SA</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculator Card -->
                    <div class="dashboard-card calculator-card">
                        <div>
                            <h2 class="card-title">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="10" x2="16" y2="10"></line><line x1="8" y1="14" x2="16" y2="14"></line></svg>
                                Rate Estimator
                            </h2>
                            <p class="card-description">Instant estimated quote based on weight and package destination.</p>
                        </div>

                        <div class="calc-group">
                            <div class="select-group">
                                <label for="destinationSelect">Destination Region</label>
                                <select id="destinationSelect" onchange="calculateRate()">
                                    <option value="5">Local Delivery (Same City)</option>
                                    <option value="15">Central Region (Amman)</option>
                                    <option value="25">Northern Region (Irbid, Jerash)</option>
                                    <option value="30">Southern Region (Aqaba, Karak)</option>
                                </select>
                            </div>
                            <div class="select-group">
                                <label for="weightInput">Weight (kg)</label>
                                <input type="number" id="weightInput" value="1" min="1" max="1000" oninput="calculateRate()">
                            </div>
                            <div class="calc-result">
                                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight:600;">ESTIMATED TOTAL</div>
                                <div class="calc-price" id="calcPrice">JD <span>5.00</span></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Services Section -->
            <section class="services" id="services" aria-labelledby="servicesTitle">
                <div class="container">
                    <div class="section-header">
                        <h2 id="servicesTitle">Our Logistics Services</h2>
                        <p>Providing custom, scalable freight and courier solutions tailored to support businesses and individuals worldwide.</p>
                    </div>
                    <div class="services-grid">
                        @forelse($services as $service)
                            <article class="service-card">
                                <div class="service-icon" style="font-size: 1.8rem; display: flex; align-items: center; justify-content: center;">
                                    {{ $service->icon ?: '📦' }}
                                </div>
                                <h3>{{ $service->trans('title') }}</h3>
                                <p>{{ $service->trans('subtitle') }}</p>
                            </article>
                        @empty
                            <!-- Service 1 -->
                            <article class="service-card">
                                <div class="service-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                                </div>
                                <h3>Express Delivery</h3>
                                <p>Fast, door-to-door courier service prioritizing swift transits for your time-sensitive consignments locally and globally.</p>
                            </article>

                            <!-- Service 2 -->
                            <article class="service-card">
                                <div class="service-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline><polyline points="7.5 19.79 7.5 14.6 3 12"></polyline><polyline points="16.5 19.79 16.5 14.6 21 12"></polyline><polyline points="12 22.08 12 12.5 3 7.29"></polyline><polyline points="12 12.5 21 7.29"></polyline></svg>
                                </div>
                                <h3>Smart Warehousing</h3>
                                <p>State-of-the-art secure storage centers strategically located with fully managed inventory and fulfillment systems.</p>
                            </article>

                            <!-- Service 3 -->
                            <article class="service-card">
                                <div class="service-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 16.242V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-1.758a2 2 0 0 0-.586-1.414l-4.364-4.364A2 2 0 0 0 15.636 10H8.364a2 2 0 0 0-1.414.586l-4.364 4.364A2 2 0 0 0 2 16.242z"></path><circle cx="12" cy="5" r="2"></circle></svg>
                                </div>
                                <h3>Enterprise Freight</h3>
                                <p>End-to-end global shipping solutions including air, sea, and land cargo management tailored to your enterprise demand.</p>
                            </article>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- About Section (featuring the visual custom asset) -->
            <section class="fleet-section" id="about" aria-labelledby="aboutTitle">
                <div class="container fleet-section">
                    <div class="fleet-content">
                        <h2 id="aboutTitle">Empowering Commerce with Premier Logistics</h2>
                        <p>{{ $settings['site_name'] }} is built on accuracy, reliability, and custom-driven supply chain management. We utilize advanced fleet analytics to offer maximum visibility and speed for every package.</p>
                        <div class="stats-row">
                            <div class="stat-box">
                                <div class="stat-number">99.8%</div>
                                <div class="stat-label">On-Time Delivery</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-number">5M+</div>
                                <div class="stat-label">Packages Shipped</div>
                            </div>
                        </div>
                    </div>
                    <div class="fleet-visual">
                        <div class="visual-bg-glow"></div>
                        <div class="logo-showcase-card">
                            <div class="showcase-tag">Verified Brand</div>
                            <img src="{{ asset('saee_logo_dark.png') }}" alt="Saee Logo Showcase" class="showcase-img">
                            <p style="text-align: center; font-size: 0.9rem; color: var(--text-muted)">Your premium logistics partner across {{ $settings['site_address'] }}.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQs Section -->
            @if($faqs->count())
            <section class="faqs-section" id="faqs" style="padding: 6rem 0; background: rgba(255,255,255,0.01); border-top: 1px solid var(--border-color);" aria-labelledby="faqsTitle">
                <div class="container">
                    <div class="section-header">
                        <h2 id="faqsTitle" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;">Frequently Asked Questions</h2>
                        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Find quick answers to common questions about our express delivery, warehousing, and corporate shipping solutions.</p>
                    </div>

                    <div style="max-width: 800px; margin: 4rem auto 0 auto; display: flex; flex-direction: column; gap: 1.2rem;">
                        @foreach($faqs as $faq)
                            <div class="faq-item" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; transition: all var(--transition-normal);">
                                <button onclick="toggleFaq(this)" class="faq-question" style="width: 100%; text-align: left; background: none; border: none; padding: 1.25rem 1.5rem; font-size: 1.05rem; font-weight: 600; color: var(--text-light); cursor: pointer; display: flex; justify-content: space-between; align-items: center; outline: none; gap: 15px;">
                                    <span>{{ $faq->trans('question') }}</span>
                                    <svg class="faq-icon" style="width: 16px; height: 16px; transition: transform var(--transition-normal); opacity: 0.6; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="faq-answer-container" style="max-height: 0; overflow: hidden; transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                    <div style="padding: 0 1.5rem 1.5rem 1.5rem; font-size: 0.95rem; color: var(--text-muted); line-height: 1.6;">
                                        {!! nl2br(e($faq->trans('answer'))) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            <!-- Contact/Quote Section -->
            <section class="cta-contact" id="contact" aria-labelledby="contactTitle">
                <div class="container">
                    <div class="contact-box">
                        <div class="contact-info">
                            <h2 id="contactTitle">Ready to Optimize Your Shipping?</h2>
                            <p>Get in touch with our supply chain specialists to design custom corporate logistics plans or ask questions about our express couriers.</p>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Customer Support</div>
                                    <strong>{{ $settings['site_phone'] }}</strong>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                </div>
                                <div class="info-text">
                                    <div class="info-label">Email Queries</div>
                                    <strong>{{ $settings['site_email'] }}</strong>
                                </div>
                            </div>
                        </div>

                        <form class="contact-form" onsubmit="event.preventDefault(); alert('Thank you for your message! Our logistics experts will contact you shortly.');" aria-label="Contact Form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactName">Full Name</label>
                                    <input type="text" id="contactName" required placeholder="John Doe">
                                </div>
                                <div class="form-group">
                                    <label for="contactEmail">Email Address</label>
                                    <input type="email" id="contactEmail" required placeholder="john@example.com">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="contactSubject">Subject</label>
                                <input type="text" id="contactSubject" required placeholder="Corporate Rate Query">
                            </div>
                            <div class="form-group">
                                <label for="contactMessage">Your Message</label>
                                <textarea id="contactMessage" required placeholder="Describe your shipping requirements..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btnContactSubmit" style="align-self: flex-start;">Send Message</button>
                        </form>
                    </div>
                </div>
            </section>

        </main>

        <!-- Footer -->
        <footer>
            <div class="container footer-grid">
                <div class="footer-about">
                    <div style="display:flex; align-items:center; gap: 10px; margin-bottom:1.5rem;">
                        <img src="{{ asset('saee_logo_dark.png') }}" alt="{{ $settings['site_name'] }} Logo Footer" class="logo-img" style="height:38px;">
                        <span style="font-size: 1.15rem; font-weight: 800; letter-spacing: -0.5px; color:var(--text-light)">{{ $settings['site_name'] }}</span>
                    </div>
                    <p>Building paths, matching schedules, and accelerating commerce through premium logistics solutions across Jordan.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ route('public.home') }}" id="footerNavHome">Home</a></li>
                        <li><a href="#services" id="footerNavServices">Services</a></li>
                        <li><a href="#about" id="footerNavAbout">About Us</a></li>
                        <li><a href="#contact" id="footerNavContact">Contact Support</a></li>
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

        <!-- ═══ SAEE AI Chatbot Widget ══════════════════════════════════ -->
        <style>
        /* Scoped to saee-chat-root; lives outside the main <style> to avoid media-query scope issues */
        #saee-chat-root { font-family: 'Outfit', sans-serif; }

        /* ── FAB pill ──────────────────────────────────────────────────── */
        #saee-chat-fab {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9999;
            background: linear-gradient(135deg, #760400 0%, #e51700 100%);
            border: none;
            border-radius: 100px;
            padding: 0 18px 0 13px;
            height: 46px;
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(229,23,0,0.45), 0 1px 4px rgba(0,0,0,0.5);
            color: #fff;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        #saee-chat-fab:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(229,23,0,0.6), 0 2px 6px rgba(0,0,0,0.5); }
        #saee-chat-fab:focus-visible { outline: 2px solid #e51700; outline-offset: 3px; }

        .fab-dot {
            width: 9px; height: 9px;
            border-radius: 50%;
            background: #fff;
            flex-shrink: 0;
            position: relative;
        }
        .fab-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,0.45);
            animation: fab-ring 1.9s ease-out infinite;
        }
        @keyframes fab-ring {
            0%   { opacity: 1; transform: scale(0.5); }
            100% { opacity: 0; transform: scale(2.4); }
        }
        .fab-lbl {
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .fab-x {
            display: none;
            width: 15px; height: 15px;
        }
        #saee-chat-fab.open .fab-dot  { display: none; }
        #saee-chat-fab.open .fab-lbl  { display: none; }
        #saee-chat-fab.open .fab-x    { display: block; }

        /* ── Window ────────────────────────────────────────────────────── */
        #saee-chat-win {
            position: fixed;
            bottom: 88px;
            right: 28px;
            width: 400px;
            max-width: calc(100vw - 36px);
            height: 590px;
            max-height: calc(100dvh - 110px);
            background: #0e0606;
            border: 1px solid rgba(229,23,0,0.18);
            border-radius: 18px;
            display: none;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 28px 64px rgba(0,0,0,0.75), 0 0 0 1px rgba(229,23,0,0.06);
            z-index: 9998;
        }
        #saee-chat-win.open {
            display: flex;
            animation: cw-in 0.24s cubic-bezier(0.34,1.15,0.64,1) both;
        }
        @keyframes cw-in {
            from { opacity: 0; transform: translateY(14px) scale(0.95); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @media (prefers-reduced-motion: reduce) {
            #saee-chat-win.open { animation: none; }
            .fab-dot::after { animation: none; }
        }

        /* ── Header ────────────────────────────────────────────────────── */
        .cw-hdr {
            flex-shrink: 0;
            padding: 13px 16px;
            background: #130707;
            border-bottom: 1px solid rgba(229,23,0,0.12);
            display: flex;
            align-items: center;
            gap: 11px;
            position: relative;
            overflow: hidden;
        }
        .cw-hdr::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(229,23,0,0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(229,23,0,0.045) 1px, transparent 1px);
            background-size: 22px 22px;
            pointer-events: none;
        }
        .cw-hdr-mark {
            position: relative;
            z-index: 1;
            width: 34px; height: 34px;
            border-radius: 8px;
            background: linear-gradient(140deg, #760400, #e51700);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 800;
            font-size: 0.8rem;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .cw-hdr-info {
            flex: 1;
            min-width: 0;
            position: relative;
            z-index: 1;
        }
        .cw-hdr-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #f0ebe9;
            line-height: 1.2;
        }
        .cw-hdr-sub {
            font-size: 0.68rem;
            color: #9a8888;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 2px;
        }
        .cw-hdr-sub::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #4ade80;
            flex-shrink: 0;
        }
        .cw-btn-new {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            color: #9a8888;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.4px;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
            font-family: inherit;
            white-space: nowrap;
        }
        .cw-btn-new:hover { background: rgba(229,23,0,0.1); color: #f0ebe9; border-color: rgba(229,23,0,0.28); }

        /* ── Messages ──────────────────────────────────────────────────── */
        .cw-msgs {
            flex: 1;
            overflow-y: auto;
            padding: 14px 14px 8px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            scroll-behavior: smooth;
        }
        .cw-msgs::-webkit-scrollbar { width: 3px; }
        .cw-msgs::-webkit-scrollbar-thumb { background: rgba(229,23,0,0.22); border-radius: 3px; }

        /* user — right-aligned warm pill */
        .cw-u {
            align-self: flex-end;
            max-width: 78%;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            animation: slide-r 0.17s ease both;
        }
        @keyframes slide-r { from { opacity:0; transform: translateX(8px); } to { opacity:1; transform: translateX(0); } }
        .cw-u-bbl {
            background: linear-gradient(140deg, #760400, #e51700);
            color: #fff;
            padding: 9px 13px;
            border-radius: 13px 13px 2px 13px;
            font-size: 0.865rem;
            line-height: 1.5;
            word-break: break-word;
        }
        .cw-u-meta { font-size: 0.65rem; color: #6b5b5b; margin-top: 3px; }

        /* bot — left-aligned dispatch card */
        .cw-b {
            align-self: flex-start;
            max-width: 88%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            animation: slide-l 0.17s ease both;
        }
        @keyframes slide-l { from { opacity:0; transform: translateX(-8px); } to { opacity:1; transform: translateX(0); } }
        .cw-b-card {
            background: #1a0b0b;
            border: 1px solid rgba(255,255,255,0.055);
            border-left: 2.5px solid #e51700;
            border-radius: 0 10px 10px 0;
            padding: 10px 13px;
            font-size: 0.865rem;
            line-height: 1.56;
            color: #e8e0de;
            word-break: break-word;
        }
        .cw-b-foot {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }
        .cw-ts { font-size: 0.63rem; color: #6b5b5b; }
        .cw-badge {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: #e51700;
            background: rgba(229,23,0,0.1);
            border: 1px solid rgba(229,23,0,0.2);
            padding: 1px 6px;
            border-radius: 3px;
        }

        /* suggestion chips */
        .cw-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 2px 0;
        }
        .cw-chip {
            background: transparent;
            border: 1px solid rgba(229,23,0,0.28);
            color: rgba(229,23,0,0.85);
            font-family: inherit;
            font-size: 0.73rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 100px;
            cursor: pointer;
            transition: background 0.14s, color 0.14s, border-color 0.14s;
            line-height: 1.3;
        }
        .cw-chip:hover { background: rgba(229,23,0,0.1); border-color: #e51700; color: #fff; }
        .cw-chip:focus-visible { outline: 2px solid #e51700; outline-offset: 2px; }

        /* ── Scanner (loading) ─────────────────────────────────────────── */
        .cw-scan {
            align-self: flex-start;
            margin: 0 14px 10px;
            width: 56%;
            height: 2px;
            background: #1a0b0b;
            border-radius: 2px;
            overflow: hidden;
            display: none;
        }
        .cw-scan.on { display: block; }
        .cw-scan-bar {
            height: 100%;
            width: 38%;
            background: linear-gradient(90deg, transparent, #e51700, transparent);
            animation: scan 1.1s ease-in-out infinite;
        }
        @keyframes scan {
            0%   { transform: translateX(-120%); }
            100% { transform: translateX(380%); }
        }
        @media (prefers-reduced-motion: reduce) { .cw-scan-bar { animation: none; background: #e51700; width: 100%; } }

        /* ── Input area ────────────────────────────────────────────────── */
        .cw-inp-wrap {
            flex-shrink: 0;
            padding: 10px 14px 9px;
            border-top: 1px solid rgba(255,255,255,0.05);
            background: #130707;
            display: flex;
            align-items: flex-end;
            gap: 9px;
        }
        .cw-inp {
            flex: 1;
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255,255,255,0.09);
            padding: 5px 0;
            color: #f0ebe9;
            font-family: inherit;
            font-size: 0.865rem;
            outline: none;
            resize: none;
            height: 30px;
            max-height: 88px;
            overflow-y: auto;
            line-height: 1.5;
            transition: border-color 0.14s;
        }
        .cw-inp::placeholder { color: rgba(255,255,255,0.22); }
        .cw-inp:focus { border-bottom-color: #e51700; }
        .cw-send-btn {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #760400, #e51700);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            transition: opacity 0.14s, transform 0.14s;
        }
        .cw-send-btn:hover:not(:disabled) { opacity: 0.85; transform: translateY(-1px); }
        .cw-send-btn:disabled { opacity: 0.28; cursor: not-allowed; }
        .cw-send-btn:focus-visible { outline: 2px solid #e51700; outline-offset: 2px; }

        /* ── Status bar ────────────────────────────────────────────────── */
        .cw-bar {
            flex-shrink: 0;
            padding: 4px 14px 7px;
            background: #130707;
            border-top: 1px solid rgba(255,255,255,0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cw-bar-lbl { font-size: 0.6rem; color: rgba(255,255,255,0.18); font-weight: 500; letter-spacing: 0.3px; }
        .cw-bar-sid { font-family: 'Courier New', monospace; font-size: 0.58rem; color: rgba(229,23,0,0.28); letter-spacing: 0.2px; }

        /* ── Light mode overrides ──────────────────────────────────────── */
        html.light-theme #saee-chat-win {
            background: #fdf7f7;
            border-color: rgba(229,23,0,0.15);
        }
        html.light-theme .cw-hdr     { background: #fff8f8; border-bottom-color: rgba(229,23,0,0.1); }
        html.light-theme .cw-hdr-name { color: #1a0505; }
        html.light-theme .cw-hdr-sub  { color: #7a6a6a; }
        html.light-theme .cw-b-card  { background: #fff; border-color: rgba(0,0,0,0.06); color: #1a0505; }
        html.light-theme .cw-inp-wrap { background: #fff8f8; border-top-color: rgba(0,0,0,0.06); }
        html.light-theme .cw-inp     { color: #1a0505; border-bottom-color: rgba(0,0,0,0.14); }
        html.light-theme .cw-inp::placeholder { color: rgba(0,0,0,0.24); }
        html.light-theme .cw-bar     { background: #fff8f8; border-top-color: rgba(0,0,0,0.04); }
        html.light-theme .cw-bar-lbl { color: rgba(0,0,0,0.22); }
        html.light-theme .cw-u-meta, html.light-theme .cw-ts { color: #9a8888; }
        html.light-theme .cw-btn-new { color: #7a6a6a; }
        html.light-theme .cw-chip { color: #e51700; }
        html.light-theme .cw-b-card { border-left-color: #e51700; }

        /* ── Mobile ────────────────────────────────────────────────────── */
        @media (max-width: 480px) {
            #saee-chat-win {
                right: 0;
                bottom: 0;
                width: 100vw;
                max-width: 100vw;
                border-radius: 20px 20px 0 0;
                height: 72dvh;
                max-height: 72dvh;
            }
            #saee-chat-fab { right: 16px; bottom: 16px; }
        }
        </style>

        <div id="saee-chat-root">

            <!-- FAB pill -->
            <button id="saee-chat-fab" onclick="chatToggle()" aria-label="Open Sa'ee AI Assistant">
                <span class="fab-dot"></span>
                <span class="fab-lbl">AI Assistant</span>
                <svg class="fab-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            <!-- Chat window -->
            <div id="saee-chat-win" role="dialog" aria-label="Sa'ee Logistics AI Assistant" aria-modal="true">

                <!-- Header -->
                <div class="cw-hdr">
                    <div class="cw-hdr-mark" aria-hidden="true">SA</div>
                    <div class="cw-hdr-info">
                        <div class="cw-hdr-name">Sa'ee Logistics AI</div>
                        <div class="cw-hdr-sub">Available · Test environment</div>
                    </div>
                    <button class="cw-btn-new" onclick="chatClear()">New session</button>
                </div>

                <!-- Messages -->
                <div class="cw-msgs" id="cw-msgs" role="log" aria-live="polite" aria-label="Chat messages"></div>

                <!-- Scanner / loading -->
                <div class="cw-scan" id="cw-scan" aria-hidden="true"><div class="cw-scan-bar"></div></div>

                <!-- Input -->
                <div class="cw-inp-wrap">
                    <textarea
                        class="cw-inp"
                        id="cw-inp"
                        placeholder="Ask a question or send an order number…"
                        rows="1"
                        onkeydown="chatKey(event)"
                        oninput="chatGrow(this)"
                        aria-label="Your message"
                    ></textarea>
                    <button class="cw-send-btn" id="cw-send" onclick="chatSend()" aria-label="Send message">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </div>

                <!-- Status bar -->
                <div class="cw-bar">
                    <span class="cw-bar-lbl">Sa'ee Logistics · AI Testing</span>
                    <span class="cw-bar-sid" id="cw-sid"></span>
                </div>

            </div>
        </div>

        <!-- Javascript Operations for Interactive UI elements -->
        <script>
            // Slide controller
            let currentSlide = 0;
            const slides = document.querySelectorAll('.banner-slider .slide');
            const dots = document.querySelectorAll('.slider-dot');
            
            function setSlide(index) {
                if (slides.length === 0) return;
                slides[currentSlide].classList.remove('active');
                if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
                currentSlide = (index + slides.length) % slides.length;
                slides[currentSlide].classList.add('active');
                if (dots[currentSlide]) dots[currentSlide].classList.add('active');
            }
            
            if (slides.length > 1) {
                setInterval(() => {
                    setSlide(currentSlide + 1);
                }, 5000);
            }

            // Shipment tracking simulator
            function simulateTracking() {
                const inputVal = document.getElementById('trackingInput').value.trim();
                const trackingResults = document.getElementById('trackingResults');
                const trackingIdSpan = document.getElementById('resultTrackingId');
                const statusBadge = document.getElementById('resultStatus');
                const stepperProgress = document.getElementById('stepperProgress');
                const latestStep = document.getElementById('latestStep');

                if (inputVal === '') {
                    alert('Please enter a valid tracking number.');
                    return;
                }

                // Make the tracking result visible
                trackingIdSpan.textContent = inputVal.toUpperCase();
                trackingResults.classList.add('active');

                // Simulate progress based on the tracking number format
                if (inputVal.toLowerCase().includes('del') || inputVal.toLowerCase().includes('done')) {
                    statusBadge.textContent = 'Delivered';
                    statusBadge.style.color = '#2ed573';
                    statusBadge.style.background = 'rgba(46, 213, 115, 0.15)';
                    stepperProgress.style.height = '100%';
                    latestStep.className = 'step completed';
                } else {
                    statusBadge.textContent = 'In Transit';
                    statusBadge.style.color = '#ffa502';
                    statusBadge.style.background = 'rgba(255, 165, 2, 0.15)';
                    stepperProgress.style.height = '66%';
                    latestStep.className = 'step active';
                }
            }

            // Logistics Rate Calculator
            function calculateRate() {
                const basePrice = parseFloat(document.getElementById('destinationSelect').value);
                const weightVal = parseFloat(document.getElementById('weightInput').value) || 1;
                const priceContainer = document.getElementById('calcPrice');

                // Formulate cost: base rate + (weight-1) * rate factor
                const weightFactor = weightVal > 1 ? (weightVal - 1) * 1.5 : 0;
                const finalPrice = basePrice + weightFactor;

                // Update UI text
                priceContainer.innerHTML = `JD <span>${finalPrice.toFixed(2)}</span>`;
            }

            // FAQ accordion toggle helper
            function toggleFaq(button) {
                const faqItem = button.closest('.faq-item');
                const answer = faqItem.querySelector('.faq-answer-container');
                const icon = faqItem.querySelector('.faq-icon');
                
                if (answer.style.maxHeight && answer.style.maxHeight !== '0px') {
                    answer.style.maxHeight = '0px';
                    icon.style.transform = 'rotate(0deg)';
                    faqItem.style.borderColor = 'var(--border-color)';
                } else {
                    document.querySelectorAll('.faq-answer-container').forEach(a => a.style.maxHeight = '0px');
                    document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = 'rotate(0deg)');
                    document.querySelectorAll('.faq-item').forEach(item => item.style.borderColor = 'var(--border-color)');
                    
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    icon.style.transform = 'rotate(180deg)';
                    faqItem.style.borderColor = 'rgba(229, 23, 0, 0.3)';
                }
            }
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

            // ─── SAEE AI Chatbot ──────────────────────────────────────────
            (function () {
                var API    = '/api/chatbot/message';
                var CHIPS  = ['Track my shipment', 'What services do you offer?', 'Do you offer COD?'];
                var sid    = localStorage.getItem('saee_sid_v2');
                if (!sid) {
                    sid = 'web-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,7);
                    localStorage.setItem('saee_sid_v2', sid);
                }
                var open   = false;
                var busy   = false;
                var chips  = false;

                document.getElementById('cw-sid').textContent = sid.slice(0, 22) + '…';

                /* public handles for onclick= attributes */
                window.chatToggle = function () {
                    open = !open;
                    document.getElementById('saee-chat-win').classList.toggle('open', open);
                    document.getElementById('saee-chat-fab').classList.toggle('open', open);
                    if (open) {
                        if (!document.getElementById('cw-msgs').firstChild) greet();
                        document.getElementById('cw-inp').focus();
                        scrollDown();
                    }
                };

                window.chatKey = function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); window.chatSend(); }
                };

                window.chatGrow = function (el) {
                    el.style.height = 'auto';
                    el.style.height = Math.min(el.scrollHeight, 88) + 'px';
                };

                window.chatSend = async function (prefill) {
                    if (busy) return;
                    var inp  = document.getElementById('cw-inp');
                    var text = (prefill || inp.value).trim();
                    if (!text) return;

                    dropChips();
                    inp.value = '';
                    inp.style.height = 'auto';
                    userBubble(text);
                    scrollDown();
                    setBusy(true);

                    try {
                        var r = await fetch(API, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ session_id: sid, message: text }),
                        });
                        var d = await r.json();
                        if (d.success) {
                            botCard(d.reply, d.intent);
                        } else {
                            var msg = d.errors
                                ? Object.values(d.errors).flat().join(' ')
                                : (d.message || 'Unexpected error.');
                            botCard('⚠ ' + msg, null);
                        }
                    } catch (_) {
                        botCard('⚠ Could not reach the server. Check your connection and try again.', null);
                    } finally {
                        setBusy(false);
                        scrollDown();
                    }
                };

                window.chatClear = function () {
                    sid = 'web-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,7);
                    localStorage.setItem('saee_sid_v2', sid);
                    document.getElementById('cw-sid').textContent = sid.slice(0, 22) + '…';
                    document.getElementById('cw-msgs').innerHTML = '';
                    chips = false;
                    greet();
                };

                /* ── Render helpers ────────────────────────────────── */
                function greet() {
                    botCard('Hello. I\'m the SAEE Logistics AI.\n\nI can track shipments and answer questions about our services — try one of the options below, or type your own question.', null, true);
                }

                function userBubble(text) {
                    var el = document.createElement('div');
                    el.className = 'cw-u';
                    el.innerHTML = '<div class="cw-u-bbl">' + esc(text) + '</div>'
                                 + '<div class="cw-u-meta">' + ts() + '</div>';
                    msgs().appendChild(el);
                }

                function botCard(text, intent, showChips) {
                    var el = document.createElement('div');
                    el.className = 'cw-b';
                    var badge = intent ? '<span class="cw-badge">' + esc(intent.replace('_',' ')) + '</span>' : '';
                    el.innerHTML = '<div class="cw-b-card">' + esc(text).replace(/\n/g,'<br>') + '</div>'
                                 + '<div class="cw-b-foot"><span class="cw-ts">' + ts() + '</span>' + badge + '</div>';
                    msgs().appendChild(el);

                    if (showChips && !chips) {
                        chips = true;
                        var row = document.createElement('div');
                        row.className = 'cw-chips';
                        row.id = 'cw-chips-row';
                        CHIPS.forEach(function (c) {
                            var btn = document.createElement('button');
                            btn.className = 'cw-chip';
                            btn.textContent = c;
                            btn.onclick = function () { window.chatSend(c); };
                            row.appendChild(btn);
                        });
                        msgs().appendChild(row);
                    }
                }

                function dropChips() {
                    var row = document.getElementById('cw-chips-row');
                    if (row) row.remove();
                }

                function setBusy(b) {
                    busy = b;
                    document.getElementById('cw-send').disabled = b;
                    document.getElementById('cw-inp').disabled  = b;
                    document.getElementById('cw-scan').classList.toggle('on', b);
                    if (b) scrollDown();
                }

                function scrollDown() {
                    var m = msgs();
                    setTimeout(function () { m.scrollTop = m.scrollHeight; }, 55);
                }

                function msgs() { return document.getElementById('cw-msgs'); }

                function ts() {
                    var d = new Date();
                    return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
                }

                function esc(s) {
                    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                }
            })();
        </script>
    </body>
</html>
