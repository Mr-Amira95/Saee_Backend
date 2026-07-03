@extends('admin.layouts.app')

@section('title', 'Customer Testimonials')
@section('page-title', 'Customer Testimonials')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.customer-stories-page.index') }}">Customer Stories</a>
    <span class="sep">/</span>
    <span class="current">Testimonials</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.customer-testimonials.create') }}" class="btn-primary">+ Add New Testimonial</a>
</div>

@if($testimonials->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Feedback (EN)</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testimonials as $testimonial)
                <tr>
                    <td><div class="cell-main">{{ $testimonial->client }}</div></td>
                    <td><div class="cell-sub" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $testimonial->feedback['en'] ?? '' }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $testimonial->sort_order }}</div></td>
                    <td>
                        @if($testimonial->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.customer-testimonials.edit', $testimonial) }}" class="act-btn act-edit" title="Edit Testimonial">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Testimonial"
                                onclick="confirmDelete('{{ route('admin.cms.customer-testimonials.destroy', $testimonial) }}','{{ addslashes($testimonial->client) }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z"/></svg>
    <h3>No testimonials found</h3>
    <p><a href="{{ route('admin.cms.customer-testimonials.create') }}" style="color:var(--red-lt);">Create the first testimonial.</a></p>
</div>
@endif

@endsection
