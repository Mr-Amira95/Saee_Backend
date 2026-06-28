<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sa\'ee Logistics — Customer Portal') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red:      #dc2626;
            --red-lt:   #ef4444;
            --red-glow: rgba(220,38,38,.25);
            --bg:       #080c1e;
            --bg-grad:  radial-gradient(circle at top, #0c1230 0%, #080c1e 100%);
            --card:     rgba(12,18,48,.8);
            --bdr:      rgba(255,255,255,.07);
            --text:     #f1f5f9;
            --text-sub: #94a3b8;
            --text-dim: #475569;
            --success:  #22c55e;
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            background-image: var(--bg-grad);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 460px;
            background: var(--card);
            border: 1px solid var(--bdr);
            border-radius: 20px;
            backdrop-filter: blur(12px);
            padding: 30px 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,.5);
            text-align: center;
            animation: slide-up .5s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo-wrap {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .logo-text {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #fff;
        }
        .header {
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -.025em;
            margin-bottom: 8px;
        }
        .header p {
            font-size: .88rem;
            color: var(--text-sub);
            line-height: 1.5;
        }
        .order-card {
            background: rgba(255,255,255,.02);
            border: 1px solid var(--bdr);
            border-radius: 12px;
            padding: 16px;
            text-align: left;
            margin-bottom: 24px;
        }
        .order-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: .84rem;
        }
        .order-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid rgba(255,255,255,.04);
            padding-top: 8px;
            margin-top: 8px;
        }
        .order-lbl {
            color: var(--text-sub);
        }
        .order-val {
            font-weight: 600;
            color: #fff;
        }
        .driver-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .driver-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7f1d1d, #dc2626);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: .85rem;
        }
        .driver-details {
            flex: 1;
        }
        .driver-name {
            font-weight: 600;
            font-size: .86rem;
        }
        .driver-phone {
            font-size: .76rem;
            color: var(--text-sub);
        }
        .call-btn {
            background: rgba(255,255,255,.05);
            border: 1px solid var(--bdr);
            color: var(--text);
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 500;
            transition: background .2s;
        }
        .call-btn:hover {
            background: rgba(255,255,255,.1);
        }
        .action-box {
            margin-bottom: 12px;
        }
        .pulse-icon {
            width: 72px;
            height: 72px;
            background: rgba(220,38,38,.12);
            border: 1px solid rgba(220,38,38,.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--red-lt);
            position: relative;
        }
        .pulse-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid var(--red);
            opacity: .6;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: .6; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .btn-share {
            width: 100%;
            background: linear-gradient(135deg, #991b1b, #dc2626);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-size: .92rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(220,38,38,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform .1s, box-shadow .2s;
        }
        .btn-share:hover {
            box-shadow: 0 8px 30px rgba(220,38,38,.45);
        }
        .btn-share:active {
            transform: scale(.98);
        }
        .status-msg {
            margin-top: 14px;
            font-size: .84rem;
            line-height: 1.5;
            display: none;
        }
        .status-msg.info {
            color: var(--text-sub);
            display: block;
        }
        .status-msg.error {
            color: #f87171;
            background: rgba(220,38,38,.08);
            border: 1px solid rgba(220,38,38,.18);
            padding: 10px;
            border-radius: 8px;
            display: block;
        }
        .status-msg.success {
            color: #86efac;
            background: rgba(34,197,94,.08);
            border: 1px solid rgba(34,197,94,.2);
            padding: 18px 14px;
            border-radius: 12px;
            display: block;
        }
        .status-msg.success svg {
            margin: 0 auto 10px;
            display: block;
            color: var(--success);
        }
        .footer {
            margin-top: 24px;
            font-size: .74rem;
            color: var(--text-dim);
        }

        /* Star ratings */
        .rating-stars {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin: 10px 0 20px;
        }
        .rating-stars input {
            display: none;
        }
        .rating-stars label {
            font-size: 2.5rem;
            color: var(--text-dim);
            cursor: pointer;
            transition: color .15s, transform .1s;
        }
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input:checked ~ label {
            color: #fbbf24;
        }
        .rating-stars label:active {
            transform: scale(.9);
        }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .order-card { text-align: right; }
        html[dir="rtl"] .driver-info { flex-direction: row-reverse; }
        html[dir="rtl"] .rating-stars { flex-direction: row; }
    </style>
</head>
<body>
    <div class="container" id="mainContainer">
        {{-- Logo --}}
        <div class="logo-wrap">
            <svg viewBox="0 0 200 200" width="26" xmlns="http://www.w3.org/2000/svg">
                <defs><radialGradient id="sg" cx="38%" cy="35%" r="65%"><stop offset="0%" stop-color="#f87171"/><stop offset="50%" stop-color="#dc2626"/><stop offset="100%" stop-color="#7f1d1d"/></radialGradient></defs>
                <circle cx="100" cy="100" r="95" fill="url(#sg)"/>
                <path d="M22,158 C38,88 90,52 172,46 L171,68 C96,74 52,108 44,165 Z" fill="white" opacity=".96"/>
                <path d="M40,132 C56,78 100,53 172,64 L171,83 C108,73 64,95 57,142 Z" fill="white" opacity=".89"/>
                <path d="M60,108 C74,69 114,55 172,78 L171,95 C118,73 80,88 72,120 Z" fill="white" opacity=".82"/>
            </svg>
            <span class="logo-text">Sa'ee Logistics</span>
        </div>

        {{-- Dynamic Header --}}
        @if(in_array($order->status, ['delivered', 'rejected']))
            <div class="header">
                <h1>{{ __('Rate Your Delivery') }}</h1>
                <p>{{ __('We value your feedback. Please rate your delivery agent,') }} {{ $order->driver->name ?? __('our driver') }}.</p>
            </div>
        @else
            <div class="header">
                <h1>{{ __('Delivery Location Sharing') }}</h1>
                <p>{{ __('Please share your exact GPS location so our delivery driver can reach you quickly.') }}</p>
            </div>
        @endif

        {{-- Order Summary Card --}}
        <div class="order-card">
            <div class="order-row">
                <span class="order-lbl">{{ __('Order Number:') }}</span>
                <span class="order-val">#{{ $order->order_number }}</span>
            </div>
            <div class="order-row">
                <span class="order-lbl">{{ __('Recipient:') }}</span>
                <span class="order-val">{{ $order->receiver_name }}</span>
            </div>
            <div class="order-row">
                <span class="order-lbl">{{ __('City / Area:') }}</span>
                <span class="order-val">{{ $order->city->name }} / {{ $order->area->name }}</span>
            </div>
            <div class="order-row">
                <span class="order-lbl">{{ __('Address:') }}</span>
                <span class="order-val">{{ $order->address_text }}</span>
            </div>
            @if($order->driver)
            <div class="order-row">
                <div class="driver-info">
                    <div class="driver-avatar">{{ strtoupper(substr($order->driver->name,0,2)) }}</div>
                    <div class="driver-details">
                        <div class="driver-name">{{ $order->driver->name }}</div>
                        <div class="driver-phone">{{ __('Delivery Agent') }}</div>
                    </div>
                    <a href="tel:{{ $order->driver->phone }}" class="call-btn">{{ __('Call Driver') }}</a>
                </div>
            </div>
            @endif
        </div>

        {{-- Dynamic Action Box --}}
        <div class="action-box" id="actionBox">
            @if(in_array($order->status, ['delivered', 'rejected']))
                @if($order->driverRating)
                    {{-- Already Rated --}}
                    <div class="status-msg success" style="display:block;">
                        <div style="font-size: 2.2rem; color: #fbbf24; margin-bottom: 12px; letter-spacing: 2px;">
                            @for($i = 1; $i <= 5; $i++)
                                {!! $i <= $order->driverRating->rating ? '&#9733;' : '&#9734;' !!}
                            @endfor
                        </div>
                        <h3>{{ __('Feedback Submitted!') }}</h3>
                        <p style="margin-top:8px;font-size:.82rem;color:var(--text-sub)">
                            {{ __('You rated our driver') }} {{ $order->driverRating->rating }} {{ __('out of 5 stars.') }}
                            @if($order->driverRating->comment)
                                <br><em style="display:block;margin-top:8px;color:#fff;font-style:normal;background:rgba(255,255,255,.03);padding:8px;border-radius:6px;">"{{ $order->driverRating->comment }}"</em>
                            @endif
                        </p>
                    </div>
                @else
                    {{-- Rating Form --}}
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars">&#9733;</label>
                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">&#9733;</label>
                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">&#9733;</label>
                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">&#9733;</label>
                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">&#9733;</label>
                    </div>
                    
                    <div style="margin-bottom:18px; display:flex; flex-direction:column; gap:6px; text-align:{{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">
                        <label style="font-size:.7rem; font-weight:700; color:var(--text-sub); text-transform:uppercase; letter-spacing:.05em;" for="comment">{{ __('Comments (Optional)') }}</label>
                        <textarea id="comment" placeholder="{{ __('Describe your experience with our driver...') }}" style="width:100%; min-height:80px; background:var(--in-bg); border:1px solid var(--bdr); border-radius:9px; padding:10px; color:var(--text); font-family:inherit; font-size:.84rem; outline:none; resize:vertical; transition:border-color .2s;"></textarea>
                    </div>

                    <button class="btn-share" id="submitRatingBtn" onclick="submitRating()">
                        {{ __('Submit Feedback') }}
                    </button>
                    <div class="status-msg info" id="statusMsg">{{ __('Your feedback helps us monitor and improve driver delivery quality.') }}</div>
                @endif
            @else
                {{-- Location Sharing --}}
                <div class="pulse-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <button class="btn-share" id="shareBtn" onclick="requestLocation()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.432 0c1.34 0 2.01.67 2.01 2.01v4.302c3.086.536 5.432 3.216 5.432 6.488s-2.346 5.952-5.432 6.488v2.702c0 1.34-.67 2.01-2.01 2.01h-.864c-1.34 0-2.01-.67-2.01-2.01v-2.702c-3.086-.536-5.432-3.216-5.432-6.488s2.346-5.952 5.432-6.488v-4.302c0-1.34.67-2.01 2.01-2.01h.864zM12 10a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    {{ __('Share My Location') }}
                </button>
                <div class="status-msg info" id="statusMsg">{{ __('We only use this to ensure prompt delivery of your package.') }}</div>
            @endif
        </div>

        <div class="footer">
            {{ __('Powered by Sa\'ee Logistics System') }}
        </div>
    </div>

    <script>
        /* Blade-to-JS translation map */
        const _t = {
            'Please select a star rating first.': '{{ __("Please select a star rating first.") }}',
            'Submitting...': '{{ __("Submitting...") }}',
            'Recording your review...': '{{ __("Recording your review...") }}',
            'Submit Feedback': '{{ __("Submit Feedback") }}',
            'Failed to submit rating.': '{{ __("Failed to submit rating.") }}',
            'Network error. Please try again.': '{{ __("Network error. Please try again.") }}',
            'Feedback Received!': '{{ __("Feedback Received!") }}',
            'Thank you for rating our delivery agent. Your review has been saved successfully.': '{{ __("Thank you for rating our delivery agent. Your review has been saved successfully.") }}',
            'Locating...': '{{ __("Locating...") }}',
            'Requesting browser location permission...': '{{ __("Requesting browser location permission...") }}',
            'Share My Location': '{{ __("Share My Location") }}',
            'Location permission denied. Please allow location access in your browser settings to share.': '{{ __("Location permission denied. Please allow location access in your browser settings to share.") }}',
            'Location information is unavailable. Please try again.': '{{ __("Location information is unavailable. Please try again.") }}',
            'The request to get user location timed out. Please try again.': '{{ __("The request to get user location timed out. Please try again.") }}',
            'An unknown error occurred.': '{{ __("An unknown error occurred.") }}',
            'Saving coordinates to our secure system...': '{{ __("Saving coordinates to our secure system...") }}',
            'Failed to update location.': '{{ __("Failed to update location.") }}',
            'Coordinates Received!': '{{ __("Coordinates Received!") }}',
            'Thank you for sharing your coordinates. Our delivery agent has been notified and will proceed to your location.': '{{ __("Thank you for sharing your coordinates. Our delivery agent has been notified and will proceed to your location.") }}',
        };

        /* Submit Driver Rating */
        function submitRating() {
            const star = document.querySelector('input[name="rating"]:checked');
            const comment = document.getElementById('comment').value;
            const status = document.getElementById('statusMsg');
            const submitBtn = document.getElementById('submitRatingBtn');

            if (!star) {
                showError(_t['Please select a star rating first.']);
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `${_t['Submitting...']} `;
            status.className = 'status-msg info';
            status.textContent = _t['Recording your review...'];

            fetch(window.location.pathname, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ rating: star.value, comment: comment })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showRatingSuccess(star.value, comment);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `${_t['Submit Feedback']}`;
                    showError(data.message || _t['Failed to submit rating.']);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `${_t['Submit Feedback']}`;
                showError(_t['Network error. Please try again.']);
            });
        }

        function showRatingSuccess(stars, comment) {
            const actionBox = document.getElementById('actionBox');
            let starsHtml = '';
            for(let i=1; i<=5; i++) {
                starsHtml += i <= stars ? '&#9733;' : '&#9734;';
            }
            actionBox.innerHTML = `
                <div class="status-msg success" style="display:block;">
                    <div style="font-size: 2.2rem; color: #fbbf24; margin-bottom: 12px; letter-spacing: 2px;">${starsHtml}</div>
                    <h3>${_t['Feedback Received!']}</h3>
                    <p style="margin-top:8px;font-size:.82rem;color:var(--text-sub)">
                        ${_t['Thank you for rating our delivery agent. Your review has been saved successfully.']}
                        \${comment ? `<br><em style="display:block;margin-top:8px;color:#fff;font-style:normal;background:rgba(255,255,255,.03);padding:8px;border-radius:6px;">"\${comment}"</em>` : ''}
                    </p>
                </div>
            `;
        }

        /* Location coordinates sharing */
        function requestLocation() {
            const btn = document.getElementById('shareBtn');
            const status = document.getElementById('statusMsg');

            if (!navigator.geolocation) {
                showError('{{ __("Geolocation is not supported by your browser. Please copy and share your coordinates manually.") }}');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<svg style="animation: spin 1s linear infinite" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18"/></svg> ${_t['Locating...']} `;
            status.className = 'status-msg info';
            status.textContent = _t['Requesting browser location permission...'];

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    submitLocation(lat, lng);
                },
                (error) => {
                    btn.disabled = false;
                    btn.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.432 0c1.34 0 2.01.67 2.01 2.01v4.302c3.086.536 5.432 3.216 5.432 6.488s-2.346 5.952-5.432 6.488v2.702c0 1.34-.67 2.01-2.01 2.01h-.864c-1.34 0-2.01-.67-2.01-2.01v-2.702c-3.086-.536-5.432-3.216-5.432-6.488s2.346-5.952 5.432-6.488v-4.302c0-1.34.67-2.01 2.01-2.01h.864zM12 10a2 2 0 100 4 2 2 0 000-4z"/></svg> ${_t['Share My Location']}`;

                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            showError(_t['Location permission denied. Please allow location access in your browser settings to share.']);
                            break;
                        case error.POSITION_UNAVAILABLE:
                            showError(_t['Location information is unavailable. Please try again.']);
                            break;
                        case error.TIMEOUT:
                            showError(_t['The request to get user location timed out. Please try again.']);
                            break;
                        default:
                            showError(_t['An unknown error occurred.']);
                            break;
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        function submitLocation(lat, lng) {
            const status = document.getElementById('statusMsg');
            status.className = 'status-msg info';
            status.textContent = _t['Saving coordinates to our secure system...'];

            fetch(window.location.pathname, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess();
                } else {
                    showError(data.message || _t['Failed to update location.']);
                }
            })
            .catch(err => {
                showError(_t['Network error. Please try again.']);
            });
        }

        function showError(msg) {
            const status = document.getElementById('statusMsg');
            status.className = 'status-msg error';
            status.textContent = msg;
        }

        function showSuccess() {
            const actionBox = document.getElementById('actionBox');
            actionBox.innerHTML = `
                <div class="status-msg success" style="display:block;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3>${_t['Coordinates Received!']}</h3>
                    <p style="margin-top:8px;font-size:.82rem;color:var(--text-sub)">${_t['Thank you for sharing your coordinates. Our delivery agent has been notified and will proceed to your location.']}</p>
                </div>
            `;
        }
    </script>
    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</body>
</html>
