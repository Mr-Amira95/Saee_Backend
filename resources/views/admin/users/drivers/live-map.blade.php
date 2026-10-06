@extends('admin.layouts.app')

@section('title', __('Live Driver Map'))
@section('page-title', __('Live Driver Map'))

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.index') }}">{{ __('Drivers') }}</a>
    <span>/</span>
    <span>{{ __('Live Map') }}</span>
@endsection

@section('head')
<style>
/* Page fills the viewport below the topbar (58px) minus the content padding (2 × 24px) */
.live-map-page {
    display: flex; flex-direction: column; gap: 12px;
    height: calc(100vh - 106px); height: calc(100dvh - 106px);
    min-height: 520px;
}
.map-wrap { position: relative; flex: 1; min-height: 0; }
#map {
    width: 100%; height: 100%;
    border-radius: 12px;
    border: 1px solid var(--bdr);
    background: #0c1230;
    overflow: hidden;
    z-index: 0;
}
html.light-theme #map { background: #e5e3df; }

.gmap-driver-marker {
    position: absolute; transform: translate(-50%, -50%); cursor: pointer;
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg,#7f1d1d,#dc2626); border: 2px solid #f87171;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff; font-family: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,.5);
}
.gmap-popup { font-size: .82rem; line-height: 1.7; color: #0f172a; }
.gmap-popup b { color: #dc2626; }
.gmap-popup .ts { color: #64748b; font-size: .75em; }
html:not(.light-theme) .gm-style .gm-style-iw-c { background: #0c1230; border: 1px solid rgba(255,255,255,.1); box-shadow: 0 8px 30px rgba(0,0,0,.6); }
html:not(.light-theme) .gm-style .gm-style-iw-d { overflow: auto !important; }
html:not(.light-theme) .gm-style .gm-style-iw-tc::after { background: #0c1230; }
html:not(.light-theme) .gm-style .gm-ui-hover-effect > span { background-color: #94a3b8 !important; }
html:not(.light-theme) .gmap-popup { color: #f1f5f9; }
html:not(.light-theme) .gmap-popup b { color: #fca5a5; }

.live-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 100px;
    background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2);
    font-size: .72rem; font-weight: 600; color: #4ade80;
}
.live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e; box-shadow: 0 0 5px #22c55e;
    animation: dot-p 1.6s infinite;
}
@keyframes dot-p { 0%,100%{opacity:1;} 50%{opacity:.3;} }

.live-map-toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex-shrink: 0; }
.live-map-toolbar .section-label {
    font-size: .68rem; font-weight: 700; color: var(--text-dim);
    letter-spacing: .1em; text-transform: uppercase; margin-inline-start: auto;
}

/* Driver cards strip: grid on top of the map, scrolls vertically after ~2 rows */
.driver-list {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px;
    max-height: 152px; overflow-y: auto; flex-shrink: 0;
    padding-inline-end: 2px;
}
.driver-list::-webkit-scrollbar { width: 4px; }
.driver-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 2px; }

.driver-card {
    background: var(--card); border: 1px solid var(--bdr);
    border-radius: 10px; padding: 10px 12px; cursor: pointer;
    transition: border-color .15s, background .15s;
    display: flex; align-items: center; gap: 10px;
}
.driver-card:hover { border-color: rgba(220,38,38,.25); background: rgba(220,38,38,.04); }
.driver-card.active { border-color: rgba(220,38,38,.45); background: rgba(220,38,38,.08); }
.driver-avatar {
    width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
    background: linear-gradient(135deg,#7f1d1d,#dc2626);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; color: white;
}
.driver-info { flex: 1; min-width: 0; }
.driver-name { font-size: .82rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.driver-time { font-size: .7rem; color: var(--text-dim); margin-top: 1px; }
.driver-coords { font-size: .68rem; font-family: monospace; color: var(--text-dim); margin-top: 2px; }
.driver-pulse {
    width: 8px; height: 8px; border-radius: 50%;
    background: #22c55e; box-shadow: 0 0 5px #22c55e;
    flex-shrink: 0;
    animation: dot-p 1.6s infinite;
}
.no-driver-msg {
    grid-column: 1 / -1;
    text-align: center; padding: 14px 16px;
    font-size: .82rem; color: var(--text-dim);
    background: var(--card); border: 1px dashed var(--bdr); border-radius: 10px;
}

@media(max-width:600px) {
    /* content padding drops to 16px on phones */
    .live-map-page { height: calc(100vh - 90px); height: calc(100dvh - 90px); }
    .driver-list { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); max-height: 140px; }
}
</style>
@endsection

@section('content')

<div class="live-map-page">
    {{-- Toolbar --}}
    <div class="live-map-toolbar">
        <span class="live-badge"><span class="live-dot"></span>{{ __('Live') }}</span>
        <span style="font-size:.78rem;color:var(--text-dim);" id="driverCount">
            {{ $drivers->count() }} {{ __('driver(s) with known location') }}
        </span>
        <span class="section-label">{{ __('Active Drivers') }}</span>
    </div>

    {{-- Driver cards (scroll vertically) --}}
    <div class="driver-list" id="driverList">
        @forelse($drivers as $driver)
        <div class="driver-card" id="card-{{ $driver->id }}"
             onclick="focusDriver({{ $driver->id }})">
            <div class="driver-avatar">{{ strtoupper(substr($driver->user->name ?? '?', 0, 2)) }}</div>
            <div class="driver-info">
                <div class="driver-name">{{ $driver->user->name ?? '—' }}</div>
                <div class="driver-time" id="time-{{ $driver->id }}">
                    {{ $driver->location_updated_at ? $driver->location_updated_at->diffForHumans() : '—' }}
                </div>
                <div class="driver-coords" id="coords-{{ $driver->id }}">
                    {{ number_format((float)$driver->current_latitude, 5) }},
                    {{ number_format((float)$driver->current_longitude, 5) }}
                </div>
            </div>
            <div class="driver-pulse"></div>
        </div>
        @empty
        <div class="no-driver-msg">
            {{ __('No drivers with location data yet.') }}
        </div>
        @endforelse
    </div>

    {{-- Map (fills the remaining height) --}}
    <div class="map-wrap">
        <div id="map"></div>
        @if(! config('services.google.maps_api_key') || $drivers->isEmpty())
        <div id="mapEmpty" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;z-index:5;">
            <div style="background:var(--card);backdrop-filter:blur(6px);border:1px solid var(--bdr);border-radius:12px;padding:22px 32px;text-align:center;">
                <svg width="32" height="32" fill="none" stroke="var(--text-dim)" stroke-width="1.4" viewBox="0 0 24 24" style="margin-bottom:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @if(! config('services.google.maps_api_key'))
                <div style="font-size:.85rem;color:var(--text-sub);">{{ __('Google Maps API key is not configured.') }}</div>
                @else
                <div style="font-size:.85rem;color:var(--text-sub);">{{ __('No drivers with a known location yet.') }}</div>
                <div style="font-size:.75rem;color:var(--text-dim);margin-top:6px;">{{ __('Markers will appear once drivers start sending updates.') }}</div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
@if(config('services.google.maps_api_key'))
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@php
$seedDrivers = $drivers->map(function ($d) {
    return [
        'id'   => $d->id,
        'name' => $d->user->name ?? '—',
        'lat'  => (float) $d->current_latitude,
        'lng'  => (float) $d->current_longitude,
        'ts'   => $d->location_updated_at ? $d->location_updated_at->diffForHumans() : '—',
    ];
})->values()->all();
@endphp
<script>
// Sidebar cards can be clicked before the Google Maps script finishes loading
window.focusDriver = function () {};

window.initLiveMap = function () {
    var i18nLiveMap = {
        lat: @json(__('Lat:')),
        lng: @json(__('Lng:')),
        justNow: @json(__('Just now')),
        driverCountTpl: @json(__(':count driver(s) with known location')),
    };
    var DEFAULT_CENTER = { lat: 31.9454, lng: 35.9284 }, DEFAULT_ZOOM = 9, MAX_FIT_ZOOM = 15;

    var BASE_STYLES = [
        { featureType: 'poi', stylers: [{ visibility: 'off' }] },
        { featureType: 'transit', stylers: [{ visibility: 'off' }] },
    ];
    var DARK_STYLES = BASE_STYLES.concat([
        { elementType: 'geometry', stylers: [{ color: '#0f1a3a' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#0c1230' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#8a94b0' }] },
        { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#2b3a6b' }] },
        { featureType: 'administrative.locality', elementType: 'labels.text.fill', stylers: [{ color: '#cbd5e1' }] },
        { featureType: 'landscape.natural', elementType: 'geometry', stylers: [{ color: '#111c40' }] },
        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#1e2a50' }] },
        { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#141d3d' }] },
        { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#94a3b8' }] },
        { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#2b3a6b' }] },
        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#070b1f' }] },
        { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#475569' }] },
    ]);

    function isLight() {
        return document.documentElement.classList.contains('light-theme');
    }

    var map = new google.maps.Map(document.getElementById('map'), {
        center: DEFAULT_CENTER,
        zoom: DEFAULT_ZOOM,
        gestureHandling: 'greedy',
        mapTypeControl: false,
        streetViewControl: false,
        clickableIcons: false,
        backgroundColor: isLight() ? '#e5e3df' : '#0c1230',
        styles: isLight() ? BASE_STYLES : DARK_STYLES,
    });

    document.addEventListener('themechange', function () {
        map.setOptions({ styles: isLight() ? BASE_STYLES : DARK_STYLES });
    });

    var infoWindow = new google.maps.InfoWindow({ pixelOffset: new google.maps.Size(0, -20) });
    var openMarkerId = null;
    google.maps.event.addListener(infoWindow, 'closeclick', function () { openMarkerId = null; });

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function popupHtml(m) {
        return '<div class="gmap-popup"><b>' + escapeHtml(m.name) + '</b><br>'
             + i18nLiveMap.lat + ' ' + m.position.lat().toFixed(5) + '<br>'
             + i18nLiveMap.lng + ' ' + m.position.lng().toFixed(5) + '<br>'
             + '<span class="ts">' + escapeHtml(m.ts) + '</span></div>';
    }

    function openPopup(m) {
        openMarkerId = m.id;
        infoWindow.setContent(popupHtml(m));
        infoWindow.setPosition(m.position);
        infoWindow.open({ map: map });
    }

    // HTML marker (initials avatar) drawn as a map overlay
    function DriverMarker(id, name, lat, lng, ts) {
        this.id = id;
        this.name = name;
        this.ts = ts;
        this.position = new google.maps.LatLng(lat, lng);
        this.div = null;
        this.setMap(map);
    }
    DriverMarker.prototype = new google.maps.OverlayView();
    DriverMarker.prototype.onAdd = function () {
        var self = this;
        this.div = document.createElement('div');
        this.div.className = 'gmap-driver-marker';
        this.div.textContent = (this.name || '?').substring(0, 2).toUpperCase();
        this.div.title = this.name || '';
        this.div.addEventListener('click', function () { window.focusDriver(self.id); });
        google.maps.OverlayView.preventMapHitsAndGesturesFrom(this.div);
        this.getPanes().overlayMouseTarget.appendChild(this.div);
    };
    DriverMarker.prototype.draw = function () {
        var projection = this.getProjection();
        if (!this.div || !projection) return;
        var point = projection.fromLatLngToDivPixel(this.position);
        if (point) {
            this.div.style.left = point.x + 'px';
            this.div.style.top = point.y + 'px';
        }
    };
    DriverMarker.prototype.onRemove = function () {
        if (this.div) this.div.remove();
        this.div = null;
    };
    DriverMarker.prototype.update = function (name, lat, lng, ts) {
        this.name = name;
        this.ts = ts;
        this.position = new google.maps.LatLng(lat, lng);
        this.draw();
        if (openMarkerId === this.id) openPopup(this);
    };

    // markers keyed by driver_profile id
    var markers = {};

    function addOrUpdateMarker(id, name, lat, lng, ts) {
        if (markers[id]) {
            markers[id].update(name, lat, lng, ts);
        } else {
            markers[id] = new DriverMarker(id, name, lat, lng, ts);
        }
    }

    // Seed existing drivers
    var SEED = @json($seedDrivers);
    if (SEED.length > 0) {
        var bounds = new google.maps.LatLngBounds();
        SEED.forEach(function (d) {
            addOrUpdateMarker(d.id, d.name, d.lat, d.lng, d.ts);
            bounds.extend({ lat: d.lat, lng: d.lng });
        });
        map.fitBounds(bounds, 40);
        google.maps.event.addListenerOnce(map, 'idle', function () {
            if (map.getZoom() > MAX_FIT_ZOOM) map.setZoom(MAX_FIT_ZOOM);
        });
    }

    // Focus a driver when clicking sidebar card or marker
    window.focusDriver = function (id) {
        document.querySelectorAll('.driver-card').forEach(function (c) {
            c.classList.remove('active');
        });
        var card = document.getElementById('card-' + id);
        if (card) {
            card.classList.add('active');
            card.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        if (markers[id]) {
            map.panTo(markers[id].position);
            map.setZoom(15);
            openPopup(markers[id]);
        }
    };

    // ── Pusher real-time listener ──────────────────────────────────
    var pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
        forceTLS: true,
    });

    var channel = pusher.subscribe('admin.drivers');

    channel.bind('location.updated', function (data) {
        var id   = data.driver_id;
        var name = data.name;
        var lat  = parseFloat(data.latitude);
        var lng  = parseFloat(data.longitude);
        var ts   = i18nLiveMap.justNow;

        // Update or create marker
        addOrUpdateMarker(id, name, lat, lng, ts);

        var emptyEl = document.getElementById('mapEmpty');
        if (emptyEl) emptyEl.remove();

        // Update sidebar card
        var card = document.getElementById('card-' + id);
        if (card) {
            var coordEl = document.getElementById('coords-' + id);
            var timeEl  = document.getElementById('time-' + id);
            if (coordEl) coordEl.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
            if (timeEl)  timeEl.textContent  = ts;
        } else {
            // New driver appeared — add a card dynamically
            var list = document.getElementById('driverList');
            var emptyMsg = list.querySelector('.no-driver-msg');
            if (emptyMsg) emptyMsg.remove();

            var initials = (name || '?').substring(0, 2).toUpperCase();
            var div = document.createElement('div');
            div.className = 'driver-card';
            div.id = 'card-' + id;
            div.addEventListener('click', function () { window.focusDriver(id); });
            div.innerHTML =
                '<div class="driver-avatar">' + escapeHtml(initials) + '</div>' +
                '<div class="driver-info">' +
                    '<div class="driver-name">' + escapeHtml(name) + '</div>' +
                    '<div class="driver-time" id="time-' + id + '">' + escapeHtml(ts) + '</div>' +
                    '<div class="driver-coords" id="coords-' + id + '">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</div>' +
                '</div>' +
                '<div class="driver-pulse"></div>';
            list.appendChild(div);

            // Update count badge
            var countEl = document.getElementById('driverCount');
            if (countEl) {
                var n = document.querySelectorAll('.driver-card').length;
                countEl.textContent = i18nLiveMap.driverCountTpl.replace(':count', n);
            }
        }
    });
};
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google.maps_api_key')) }}&callback=initLiveMap&loading=async&language={{ app()->getLocale() }}"></script>
@endif
@endsection
