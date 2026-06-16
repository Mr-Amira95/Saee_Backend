@extends('admin.layouts.app')

@section('title', 'Cities & Areas')
@section('page-title', 'Cities & Areas')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Cities &amp; Areas</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="mini-stats">
    <div class="mini-stat">
        <div>
            <div class="mini-stat-val">{{ \App\Models\City::count() }}</div>
            <div class="mini-stat-lbl">Total Cities</div>
        </div>
    </div>
    <div class="mini-stat">
        <div>
            <div class="mini-stat-val">{{ \App\Models\City::where('is_active', true)->count() }}</div>
            <div class="mini-stat-lbl">Active</div>
        </div>
    </div>
    <div class="mini-stat">
        <div>
            <div class="mini-stat-val">{{ \App\Models\Area::count() }}</div>
            <div class="mini-stat-lbl">Total Areas</div>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.cities.index') }}" class="filter-form">
        <input class="filter-search" type="text" name="search"
               value="{{ request('search') }}" placeholder="Search city name…">
        <button class="btn-secondary" type="submit">Filter</button>
        @if(request('search'))
            <a href="{{ route('admin.cities.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.cities.create') }}" class="btn-primary">+ Add City</a>
</div>

{{-- Table --}}
@if($cities->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>City Name</th>
                    <th>Arabic Name</th>
                    <th>Country</th>
                    <th>Areas</th>
                    <th>Delivery Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cities as $city)
                <tr>
                    <td><div class="cell-main">{{ $city->name }}</div></td>
                    <td><div class="cell-sub" style="font-size:.88rem;color:var(--text);" dir="rtl">{{ $city->name_ar ?: '—' }}</div></td>
                    <td><span style="font-size:.8rem;background:rgba(255,255,255,.05);padding:3px 8px;border-radius:6px;">{{ $city->country_code }}</span></td>
                    <td>
                        <span style="font-weight:700;color:var(--text);">{{ $city->areas_count }}</span>
                        <span style="color:var(--text-dim);font-size:.78rem;"> areas</span>
                    </td>
                    <td>
                        <span style="font-weight:600;color:var(--text);">{{ number_format($city->delivery_price, 2) }}</span>
                        <span style="color:var(--text-dim);font-size:.78rem;"> JD</span>
                    </td>
                    <td>
                        @if($city->is_active)
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <form method="POST" action="{{ route('admin.cities.toggle', $city) }}" style="display:contents;">
                                @csrf
                                @method('PATCH')
                                <label class="toggle-switch" title="{{ $city->is_active ? 'Deactivate' : 'Activate' }}">
                                    <input type="checkbox" onchange="this.form.submit()" {{ $city->is_active ? 'checked' : '' }}>
                                    <span class="toggle-track"><span class="toggle-thumb"></span></span>
                                </label>
                            </form>
                            <a href="{{ route('admin.cities.edit', $city) }}" class="act-btn act-edit" title="Edit & Manage Areas">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete"
                                onclick="confirmDelete('{{ route('admin.cities.destroy', $city) }}','{{ addslashes($city->name) }}')">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($cities->hasPages())
    <div class="pagination-wrap">
        <span class="pag-info">Showing {{ $cities->firstItem() }}–{{ $cities->lastItem() }} of {{ $cities->total() }}</span>
        <div class="pag-links">{{ $cities->links() }}</div>
    </div>
    @endif
</div>
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <h3>No cities found</h3>
    <p><a href="{{ route('admin.cities.create') }}" style="color:var(--red-lt);">Add the first city.</a></p>
</div>
@endif

@endsection
