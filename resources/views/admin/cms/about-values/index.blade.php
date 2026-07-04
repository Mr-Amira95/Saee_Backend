@extends('admin.layouts.app')

@section('title', 'Manage About Values')
@section('page-title', 'Manage About Values')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.about-page.index') }}">About Page</a>
    <span class="sep">/</span>
    <span class="current">Values</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.about-values.create') }}" class="btn-primary">+ Add New Value</a>
</div>

@if($values->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Text</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($values as $value)
                <tr>
                    <td><div class="cell-main">{{ $value->text['en'] ?? '' }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $value->sort_order }}</div></td>
                    <td>
                        @if($value->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.about-values.edit', $value) }}" class="act-btn act-edit" title="Edit Value">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Value"
                                onclick="confirmDelete('{{ route('admin.cms.about-values.destroy', $value) }}','{{ addslashes($value->text['en'] ?? '') }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3>No values found</h3>
    <p><a href="{{ route('admin.cms.about-values.create') }}" style="color:var(--red-lt);">Create the first value.</a></p>
</div>
@endif

@endsection
