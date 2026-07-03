@extends('admin.layouts.app')

@section('title', 'Flow Steps')
@section('page-title', 'Flow Steps')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.flow.index') }}">Flow Section</a>
    <span class="sep">/</span>
    <span class="current">Steps</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.flow-steps.create') }}" class="btn-primary">+ Add New Step</a>
</div>

@if($steps->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title (EN)</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($steps as $step)
                <tr>
                    <td>
                        @if($step->image_path)
                            <img src="{{ $step->image_path }}" alt="{{ $step->title['en'] ?? '' }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--bdr);">
                        @else
                            <span style="font-size:.78rem;color:var(--text-dim);">No image</span>
                        @endif
                    </td>
                    <td><div class="cell-main">{{ $step->title['en'] ?? '' }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $step->sort_order }}</div></td>
                    <td>
                        @if($step->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.flow-steps.edit', $step) }}" class="act-btn act-edit" title="Edit Step">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Step"
                                onclick="confirmDelete('{{ route('admin.cms.flow-steps.destroy', $step) }}','{{ addslashes($step->title['en'] ?? '') }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
    <h3>No flow steps found</h3>
    <p><a href="{{ route('admin.cms.flow-steps.create') }}" style="color:var(--red-lt);">Create the first step.</a></p>
</div>
@endif

@endsection
