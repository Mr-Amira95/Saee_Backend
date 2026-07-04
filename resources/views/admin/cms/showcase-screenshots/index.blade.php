@extends('admin.layouts.app')

@section('title', 'Screenshots')
@section('page-title', 'Screenshots')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.showcase-page.index') }}">Showcases</a>
    <span class="sep">/</span>
    <span class="current">Screenshots</span>
@endsection

@section('head')
<style>
    .shot-tabs-nav {
        display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--bdr); padding-bottom: 12px;
    }
    .shot-tab-btn {
        background: rgba(255,255,255,.03); border: 1px solid var(--bdr); border-radius: 8px;
        padding: 10px 20px; font-size: .84rem; font-weight: 600; color: var(--text-sub);
        cursor: pointer; transition: all .15s; outline: none;
    }
    .shot-tab-btn:hover { background: rgba(255,255,255,.07); color: var(--text); }
    .shot-tab-btn.active { background: rgba(220,38,38,.12); color: #fca5a5; border-color: rgba(220,38,38,.3); }
    .shot-tab-pane { display: none; }
    .shot-tab-pane.active { display: block; animation: fade-in .25s ease-out both; }
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('content')

<div class="shot-tabs-nav">
    <button type="button" class="shot-tab-btn active" onclick="switchShotTab('application')">Application</button>
    <button type="button" class="shot-tab-btn" onclick="switchShotTab('portal')">Portals</button>
</div>

@php
    $tabs = [
        'application' => ['label' => 'Application', 'items' => $applicationScreenshots],
        'portal' => ['label' => 'Portals', 'items' => $portalScreenshots],
    ];
@endphp

@foreach($tabs as $key => $tab)
<div class="shot-tab-pane {{ $key === 'application' ? 'active' : '' }}" id="shot-tab-{{ $key }}">
    <div class="filter-bar" style="justify-content: flex-end;">
        <a href="{{ route('admin.cms.showcase-screenshots.create', ['category' => $key]) }}" class="btn-primary">+ Add {{ $tab['label'] }} Screenshot</a>
    </div>

    @if($tab['items']->count())
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Screenshot</th>
                        <th>Caption (EN)</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tab['items'] as $shot)
                    <tr>
                        <td>
                            <span style="background: rgba(255,255,255,.05); width: 90px; height: 64px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; overflow: hidden;">
                                @if($shot->image_path)
                                    <img src="{{ $shot->image_path }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                @endif
                            </span>
                        </td>
                        <td><div class="cell-main">{{ $shot->title['en'] ?? '—' }}</div></td>
                        <td><div class="cell-main" style="font-weight: 700;">{{ $shot->sort_order }}</div></td>
                        <td>
                            @if($shot->status === 'active')
                                <span class="badge-active">Active</span>
                            @else
                                <span class="badge-suspended">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.cms.showcase-screenshots.edit', $shot) }}" class="act-btn act-edit" title="Edit Screenshot">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button class="act-btn act-delete" title="Delete Screenshot"
                                    onclick="confirmDelete('{{ route('admin.cms.showcase-screenshots.destroy', $shot) }}','{{ addslashes($shot->title['en'] ?? 'this screenshot') }}')">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="empty-state">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <h3>No {{ strtolower($tab['label']) }} screenshots yet</h3>
        <p><a href="{{ route('admin.cms.showcase-screenshots.create', ['category' => $key]) }}" style="color:var(--red-lt);">Upload the first screenshot.</a></p>
    </div>
    @endif
</div>
@endforeach

@endsection

@section('scripts')
<script>
    function switchShotTab(tabName) {
        document.querySelectorAll('.shot-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.shot-tab-pane').forEach(pane => pane.classList.remove('active'));

        const activeBtn = Array.from(document.querySelectorAll('.shot-tab-btn'))
            .find(btn => btn.getAttribute('onclick').includes(tabName));
        if (activeBtn) activeBtn.classList.add('active');

        const activePane = document.getElementById('shot-tab-' + tabName);
        if (activePane) activePane.classList.add('active');
    }
</script>
@endsection
