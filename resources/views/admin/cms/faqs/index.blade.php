@extends('admin.layouts.app')

@section('title', 'Manage FAQs')
@section('page-title', 'Manage FAQs')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">FAQs</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.faqs.create') }}" class="btn-primary">+ Add New FAQ</a>
</div>

@if($faqs->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Answer Summary</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($faqs as $faq)
                <tr>
                    <td><div class="cell-main" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $faq->question['en'] ?? '' }}</div></td>
                    <td><div class="cell-sub" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $faq->answer['en'] ?? '' }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $faq->sort_order }}</div></td>
                    <td>
                        @if($faq->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.faqs.edit', $faq) }}" class="act-btn act-edit" title="Edit FAQ">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete FAQ"
                                onclick="confirmDelete('{{ route('admin.cms.faqs.destroy', $faq) }}','{{ addslashes($faq->question['en'] ?? '') }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3>No FAQs found</h3>
    <p><a href="{{ route('admin.cms.faqs.create') }}" style="color:var(--red-lt);">Create the first FAQ.</a></p>
</div>
@endif

@endsection
