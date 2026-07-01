@extends('admin.layouts.app')

@section('title', 'Location History – '.($driver->user->name ?? 'Driver'))
@section('page-title', 'Location History')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.index') }}">Drivers</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.show', $driver) }}">{{ $driver->user->name ?? '—' }}</a>
    <span>/</span>
    <span>Location History</span>
@endsection

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
#map {
    width: 100%; height: 520px;
    border-radius: 12px;
    border: 1px solid var(--bdr);
    background: #0c1230;
    z-index: 0;
}
/* Override Leaflet popup to match dark theme */
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

.stat-bar {
    display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;
}
.stat-chip {
    display: flex; flex-direction: column;
    padding: 10px 18px; background: var(--card);
    border: 1px solid var(--bdr); border-radius: 10px;
    min-width: 130px;
}
.stat-chip-label { font-size: .68rem; color: var(--text-dim); font-weight: 600; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 3px; }
.stat-chip-val   { font-size: 1.05rem; font-weight: 700; color: var(--text); }

.map-legend {
    display: flex; gap: 16px; align-items: center;
    padding: 8px 14px; background: var(--card);
    border: 1px solid var(--bdr); border-radius: 8px;
    font-size: .78rem; color: var(--text-sub); margin-top: 10px;
}
.legend-dot {
    width: 11px; height: 11px; border-radius: 50%; display: inline-block; margin-right: 5px;
}

.points-table-wrap {
    overflow-x: auto; margin-top: 6px;
    border: 1px solid var(--bdr); border-radius: 10px;
}
.points-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.points-table th {
    padding: 10px 14px; text-align: left;
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--text-dim);
    border-bottom: 1px solid var(--bdr); white-space: nowrap;
}
.points-table td {
    padding: 9px 14px; border-bottom: 1px solid var(--bdr);
    color: var(--text-sub); white-space: nowrap;
}
.points-table tr:last-child td { border-bottom: none; }
.points-table tr:hover td { background: rgba(255,255,255,.02); }
.points-table td.mono { font-family: monospace; font-size: .8rem; color: var(--text); }
.no-data-msg {
    text-align: center; padding: 60px 20px;
    font-size: .85rem; color: var(--text-dim);
}
</style>
@endsection

@section('content')
@php
    $count        = $points->count();
    $duration     = $count >= 2
        ? $points->first()->recorded_at->diffForHumans($points->last()->recorded_at, true)
        : '—';
    $distanceStr  = $distanceKm >= 1
        ? number_format($distanceKm, 2).' km'
        : number_format($distanceKm * 1000).' m';
@endphp

{{-- ── Date-range filter ── --}}
<div class="form-section" style="margin-bottom:18px;">
    <form method="GET" action="{{ route('admin.drivers.location-history', $driver) }}">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                <label class="form-label" style="font-size:.72rem;">From</label>
                <input class="form-input" type="datetime-local" name="from"
                       value="{{ $from->format('Y-m-d\TH:i') }}" style="height:38px;">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                <label class="form-label" style="font-size:.72rem;">To</label>
                <input class="form-input" type="datetime-local" name="to"
                       value="{{ $to->format('Y-m-d\TH:i') }}" style="height:38px;">
            </div>
            <button type="submit" class="btn-primary" style="height:38px;padding:0 20px;flex-shrink:0;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:5px;vertical-align:middle;"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
                Show
            </button>
            <a href="{{ route('admin.drivers.location-history', $driver) }}" class="btn-secondary"
               style="height:38px;line-height:38px;padding:0 16px;flex-shrink:0;display:inline-block;">Today</a>
        </div>
    </form>
</div>

{{-- ── Stats bar ── --}}
<div class="stat-bar">
    <div class="stat-chip">
        <span class="stat-chip-label">Points</span>
        <span class="stat-chip-val">{{ number_format($count) }}</span>
    </div>
    <div class="stat-chip">
        <span class="stat-chip-label">Distance</span>
        <span class="stat-chip-val">{{ $count >= 2 ? $distanceStr : '—' }}</span>
    </div>
    <div class="stat-chip">
        <span class="stat-chip-label">Duration</span>
        <span class="stat-chip-val">{{ $duration }}</span>
    </div>
    <div class="stat-chip">
        <span class="stat-chip-label">Period</span>
        <span class="stat-chip-val" style="font-size:.82rem;font-weight:500;">
            {{ $from->format('d M H:i') }} → {{ $to->format('d M H:i') }}
        </span>
    </div>
</div>

{{-- ── Map ── --}}
<div style="position:relative;">
    <div id="map"></div>
    @if($count === 0)
    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;z-index:5;">
        <div style="background:rgba(8,12,30,.88);border:1px solid var(--bdr);border-radius:12px;padding:22px 32px;text-align:center;">
            <svg width="32" height="32" fill="none" stroke="var(--text-dim)" stroke-width="1.4" viewBox="0 0 24 24" style="margin-bottom:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <div style="font-size:.85rem;color:var(--text-sub);">No location data for this period.</div>
        </div>
    </div>
    @endif
</div>

<div class="map-legend">
    <span><span class="legend-dot" style="background:#22c55e;"></span>Start</span>
    <span><span class="legend-dot" style="background:#dc2626;"></span>End</span>
    <span><span class="legend-dot" style="background:#dc2626;width:28px;border-radius:2px;height:3px;"></span>Path</span>
    @if($count > 0)
    <span style="margin-left:auto;color:var(--text-dim);font-size:.73rem;">
        {{ $from->format('d M Y, H:i') }} — {{ $to->format('d M Y, H:i') }}
    </span>
    @endif
</div>

{{-- ── Points table (last 200 rows) ── --}}
@if($count > 0)
<div style="margin-top:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <div style="font-size:.82rem;font-weight:600;color:var(--text-sub);">
            Waypoints
            @if($count > 200)
                <span style="color:var(--text-dim);font-weight:400;"> — showing latest 200 of {{ number_format($count) }}</span>
            @endif
        </div>
    </div>
    <div class="points-table-wrap">
        <table class="points-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Speed</th>
                    <th>Accuracy</th>
                </tr>
            </thead>
            <tbody>
                @foreach($points->take(-200) as $i => $point)
                <tr>
                    <td style="color:var(--text-dim);">{{ $count - 200 + $loop->iteration > 0 ? $count - 200 + $loop->iteration : $loop->iteration }}</td>
                    <td>{{ $point->recorded_at->format('H:i:s') }}
                        <span style="color:var(--text-dim);font-size:.76rem;display:block;">{{ $point->recorded_at->format('d M Y') }}</span>
                    </td>
                    <td class="mono">{{ number_format((float)$point->latitude, 6) }}</td>
                    <td class="mono">{{ number_format((float)$point->longitude, 6) }}</td>
                    <td>{{ $point->speed !== null ? number_format((float)$point->speed, 1).' km/h' : '—' }}</td>
                    <td>{{ $point->accuracy !== null ? number_format((float)$point->accuracy, 0).' m' : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@php
$mapPoints = $points->map(function ($p) {
    return [
        'lat' => (float) $p->latitude,
        'lng' => (float) $p->longitude,
        'ts'  => $p->recorded_at->format('d M Y, H:i:s'),
        'spd' => $p->speed    !== null ? number_format((float) $p->speed, 1)   .' km/h' : null,
        'acc' => $p->accuracy !== null ? number_format((float) $p->accuracy, 0).' m'    : null,
    ];
})->values()->all();
@endphp
<script>
(function () {
    // All recorded points passed from PHP
    var RAW_POINTS = @json($mapPoints);

    // Default centre — Jordan
    var DEFAULT_LAT = 31.9454, DEFAULT_LNG = 35.9284, DEFAULT_ZOOM = 8;

    var map = L.map('map', { zoomControl: true });

    // CartoDB tile layer — follows the current admin theme
    function getTileUrl() {
        var isLight = document.documentElement.classList.contains('light-theme');
        return 'https://{s}.basemaps.cartocdn.com/' + (isLight ? 'light_all' : 'dark_all') + '/{z}/{x}/{y}{r}.png';
    }

    var tileLayer = L.tileLayer(getTileUrl(), {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    document.addEventListener('themechange', function () {
        tileLayer.setUrl(getTileUrl());
    });

    if (RAW_POINTS.length === 0) {
        map.setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);
        return;
    }

    // Build LatLng array
    var latlngs = RAW_POINTS.map(function (p) { return [p.lat, p.lng]; });

    // Draw path
    L.polyline(latlngs, {
        color: '#dc2626',
        weight: 3,
        opacity: 0.85,
        lineJoin: 'round'
    }).addTo(map);

    // Start marker — green
    var greenIcon = L.circleMarker(latlngs[0], {
        radius: 9, fillColor: '#22c55e', color: '#fff',
        weight: 2, opacity: 1, fillOpacity: 1
    }).addTo(map)
      .bindPopup('<b>Start</b><br>' + RAW_POINTS[0].ts);

    // End marker — red (only if >1 point)
    if (latlngs.length > 1) {
        var last = latlngs[latlngs.length - 1];
        var lastP = RAW_POINTS[RAW_POINTS.length - 1];
        L.circleMarker(last, {
            radius: 9, fillColor: '#dc2626', color: '#fff',
            weight: 2, opacity: 1, fillOpacity: 1
        }).addTo(map)
          .bindPopup('<b>End</b><br>' + lastP.ts
              + (lastP.spd ? '<br>Speed: ' + lastP.spd : ''));
    }

    // Clickable intermediate waypoints (thin circles, every Nth to avoid clutter)
    var STEP = Math.max(1, Math.floor(RAW_POINTS.length / 150));
    for (var i = 1; i < RAW_POINTS.length - 1; i += STEP) {
        (function (p) {
            var popup = p.ts
                + (p.spd ? '<br>Speed: <b>' + p.spd + '</b>' : '')
                + (p.acc ? '<br>Accuracy: ' + p.acc : '');
            L.circleMarker([p.lat, p.lng], {
                radius: 4, fillColor: '#f87171', color: 'transparent',
                weight: 0, opacity: 1, fillOpacity: 0.7
            }).addTo(map).bindPopup(popup);
        })(RAW_POINTS[i]);
    }

    // Fit map to path
    map.fitBounds(L.latLngBounds(latlngs), { padding: [40, 40] });
})();
</script>
@endsection
