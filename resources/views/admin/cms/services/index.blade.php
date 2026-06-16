@extends('admin.layouts.app')

@section('title', 'Manage Services')
@section('page-title', 'Manage Services')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Services</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.services.create') }}" class="btn-primary">+ Add New Service</a>
</div>

@if($services->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr>
                    <td>
                        <span style="font-size: 1.5rem; background: rgba(255,255,255,.05); width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                            {{ $service->icon ?: '📦' }}
                        </span>
                    </td>
                    <td><div class="cell-main">{{ $service->title }}</div></td>
                    <td><div class="cell-sub" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $service->description }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $service->sort_order }}</div></td>
                    <td>
                        @if($service->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.services.edit', $service) }}" class="act-btn act-edit" title="Edit Service">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Service"
                                onclick="confirmDelete('{{ route('admin.cms.services.destroy', $service) }}','{{ addslashes($service->title) }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
    <h3>No logistical services found</h3>
    <p><a href="{{ route('admin.cms.services.create') }}" style="color:var(--red-lt);">Create the first service.</a></p>
</div>
@endif

@endsection
