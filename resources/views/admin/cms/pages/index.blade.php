@extends('admin.layouts.app')

@section('title', 'Manage Pages')
@section('page-title', 'Manage Pages')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Pages</span>
@endsection

@section('content')

<div class="filter-bar">
    <form method="GET" action="{{ route('admin.cms.pages.index') }}" class="filter-form">
        <input class="filter-search" type="text" name="search"
               value="{{ request('search') }}" placeholder="Search page title or content…">
        <button class="btn-secondary" type="submit">Filter</button>
        @if(request('search'))
            <a href="{{ route('admin.cms.pages.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.cms.pages.create') }}" class="btn-primary">+ Add New Page</a>
</div>

@if($pages->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug / Route</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pages as $page)
                <tr>
                    <td><div class="cell-main">{{ $page->title }}</div></td>
                    <td>
                        <a href="{{ url('/page/' . $page->slug) }}" target="_blank" style="color: var(--info); font-size: .84rem; text-decoration: none;">
                            /page/{{ $page->slug }} ↗
                        </a>
                    </td>
                    <td>
                        @if($page->status === 'published')
                            <span class="badge-active">Published</span>
                        @else
                            <span class="badge-suspended">Draft</span>
                        @endif
                    </td>
                    <td><div class="cell-sub">{{ $page->created_at->format('M d, Y H:i') }}</div></td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.pages.edit', $page) }}" class="act-btn act-edit" title="Edit Page">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Page"
                                onclick="confirmDelete('{{ route('admin.cms.pages.destroy', $page) }}','{{ addslashes($page->title) }}')">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($pages->hasPages())
    <div class="pagination-wrap">
        <span class="pag-info">Showing {{ $pages->firstItem() }}–{{ $pages->lastItem() }} of {{ $pages->total() }}</span>
        <div class="pag-links">{{ $pages->links() }}</div>
    </div>
    @endif
</div>
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
    <h3>No pages found</h3>
    <p><a href="{{ route('admin.cms.pages.create') }}" style="color:var(--red-lt);">Create the first page.</a></p>
</div>
@endif

@endsection
