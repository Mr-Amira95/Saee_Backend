@extends('admin.layouts.app')

@section('title', 'Live Driver Map')
@section('page-title', 'Live Driver Map')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.index') }}">Drivers</a>
    <span>/</span>
    <span>Live Map</span>
@endsection

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
#map {
    width: 100%; height: 580px;
    border-radius: 12px;
    border: 1px solid var(--bdr);
    background: #0c1230;
    z-index: 0;
}
.leaflet-popup-content-wrapper {
    background: #0c1230; border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px; color: #f1f5f9;
    box-shadow: 0 8px 30px rgba(0,0,0,.6);
}
.leaflet-popup-tip { background: #0c1230; }
.leaflet-popup-content { margin: 10px 14px; font-size: .82rem; line-height: 1.7; }
.leaflet-popup-content b { color: #fca5a5; }
.leaflet-attribution-flag { display: none !important; }
.leaflet-control-attribution { background: rgba(0,0,0,.5) !important; color: #64748b !important; font-size: .68rem; }
.leaflet-control-attribution a { color: #94a3b8 !important; }

html.light-theme #map { background: #e5e3df; }
html.light-theme .leaflet-popup-content-wrapper {
    background: #ffffff; border: 1px solid rgba(15,23,42,.08);
    color: #0f172a; box-shadow: 0 8px 30px rgba(15,23,42,.15);
}
html.light-theme .leaflet-popup-tip { background: #ffffff; }
html.light-theme .leaflet-popup-content b { color: #dc2626; }
html.light-theme .leaflet-control-attribution { background: rgba(255,255,255,.7) !important; color: #64748b !important; }
html.light-theme .leaflet-control-attribution a { color: #475569 !important; }

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

.driver-list {
    display: flex; flex-direction: column; gap: 6px;
    max-height: 580px; overflow-y: auto; padding-right: 2px;
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
    text-align: center; padding: 40px 16px;
    font-size: .82rem; color: var(--text-dim);
}

.map-layout {
    display: grid; grid-template-columns: 1fr 260px; gap: 14px;
    align-items: start;
}
@media(max-width:900px) {
    .map-layout { grid-template-columns: 1fr; }
    .driver-list { max-height: 220px; }
}
</style>
@endsection

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
    <span class="live-badge"><span class="live-dot"></span>Live</span>
    <span style="font-size:.78rem;color:var(--text-dim);" id="driverCount">
        {{ $drivers->count() }} driver{{ $drivers->count() !== 1 ? 's' : '' }} with known location
    </span>
</div>

<div class="map-layout">
    {{-- Map --}}
    <div style="position:relative;">
        <div id="map"></div>
        @if($drivers->isEmpty())
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;z-index:5;">
            <div style="background:var(--card);backdrop-filter:blur(6px);border:1px solid var(--bdr);border-radius:12px;padding:22px 32px;text-align:center;">
                <svg width="32" height="32" fill="none" stroke="var(--text-dim)" stroke-width="1.4" viewBox="0 0 24 24" style="margin-bottom:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <div style="font-size:.85rem;color:var(--text-sub);">No drivers with a known location yet.</div>
                <div style="font-size:.75rem;color:var(--text-dim);margin-top:6px;">Markers will appear once drivers start sending updates.</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Driver sidebar list --}}
    <div>
        <div style="font-size:.68rem;font-weight:700;color:var(--text-dim);letter-spacing:.1em;text-transform:uppercase;margin-bottom:8px;">
            Active Drivers
        </div>
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
                No drivers with location data yet.
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
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
(function () {
    var DEFAULT_LAT = 31.9454, DEFAULT_LNG = 35.9284, DEFAULT_ZOOM = 9;

    var map = L.map('map', { zoomControl: true });

    var tileLayer = L.tileLayer(getTileUrl(), {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    function getTileUrl() {
        var isLight = document.documentElement.classList.contains('light-theme');
        return 'https://{s}.basemaps.cartocdn.com/' + (isLight ? 'light_all' : 'dark_all') + '/{z}/{x}/{y}{r}.png';
    }

    document.addEventListener('themechange', function () {
        tileLayer.setUrl(getTileUrl());
    });

    var SEED = @json($seedDrivers);

    // markers keyed by driver_profile id
    var markers = {};

    function makeIcon(name) {
        var initials = (name || '?').substring(0, 2).toUpperCase();
        return L.divIcon({
            className: '',
            html: '<div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7f1d1d,#dc2626);border:2px solid #f87171;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.5);">' + initials + '</div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -20],
        });
    }

    function popupHtml(name, lat, lng, ts) {
        return '<b>' + name + '</b><br>'
             + 'Lat: ' + lat.toFixed(5) + '<br>'
             + 'Lng: ' + lng.toFixed(5) + '<br>'
             + '<span style="color:#64748b;font-size:.75em;">' + ts + '</span>';
    }

    function addOrUpdateMarker(id, name, lat, lng, ts) {
        if (markers[id]) {
            markers[id].setLatLng([lat, lng]);
            markers[id].getPopup().setContent(popupHtml(name, lat, lng, ts));
        } else {
            markers[id] = L.marker([lat, lng], { icon: makeIcon(name) })
                .addTo(map)
                .bindPopup(popupHtml(name, lat, lng, ts));
        }
    }

    // Seed existing drivers
    if (SEED.length > 0) {
        var bounds = [];
        SEED.forEach(function (d) {
            addOrUpdateMarker(d.id, d.name, d.lat, d.lng, d.ts);
            bounds.push([d.lat, d.lng]);
        });
        map.fitBounds(L.latLngBounds(bounds), { padding: [40, 40] });
    } else {
        map.setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
    }

    // Focus a driver when clicking sidebar card
    window.focusDriver = function (id) {
        document.querySelectorAll('.driver-card').forEach(function (c) {
            c.classList.remove('active');
        });
        var card = document.getElementById('card-' + id);
        if (card) card.classList.add('active');

        if (markers[id]) {
            map.setView(markers[id].getLatLng(), 15, { animate: true });
            markers[id].openPopup();
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
        var ts   = 'Just now';

        // Update or create marker
        addOrUpdateMarker(id, name, lat, lng, ts);

        // Update sidebar card
        var card = document.getElementById('card-' + id);
        if (card) {
            // Update coords and time
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
            div.setAttribute('onclick', 'focusDriver(' + id + ')');
            div.innerHTML =
                '<div class="driver-avatar">' + initials + '</div>' +
                '<div class="driver-info">' +
                    '<div class="driver-name">' + name + '</div>' +
                    '<div class="driver-time" id="time-' + id + '">' + ts + '</div>' +
                    '<div class="driver-coords" id="coords-' + id + '">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</div>' +
                '</div>' +
                '<div class="driver-pulse"></div>';
            list.appendChild(div);

            // Update count badge
            var countEl = document.getElementById('driverCount');
            if (countEl) {
                var n = document.querySelectorAll('.driver-card').length;
                countEl.textContent = n + ' driver' + (n !== 1 ? 's' : '') + ' with known location';
            }
        }
    });
})();
</script>
@endsection
