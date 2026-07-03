@extends('admin.layouts.app')

@section('title', 'Contact Form Submissions')
@section('page-title', 'Contact Form Submissions')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Contact Form Submissions</span>
@endsection

@section('content')

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#86efac;font-size:.88rem;">
    {{ session('success') }}
</div>
@endif

@if($submissions->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $submission)
                <tr>
                    <td>
                        <span style="font-size:.78rem;background:rgba(255,255,255,.05);padding:3px 8px;border-radius:6px;text-transform: capitalize;">
                            {{ $submission->type }}
                        </span>
                    </td>
                    <td><div class="cell-main">{{ $submission->name }}</div></td>
                    <td><div class="cell-sub">{{ $submission->company ?: '—' }}</div></td>
                    <td><div class="cell-sub">{{ $submission->email }}</div></td>
                    <td><div class="cell-sub">{{ $submission->phone ?: '—' }}</div></td>
                    <td>
                        @if($submission->status === 'new')
                            <span class="badge-suspended">New</span>
                        @elseif($submission->status === 'contacted')
                            <span class="badge-active">Contacted</span>
                        @else
                            <span class="badge-active">Closed</span>
                        @endif
                    </td>
                    <td><div class="cell-sub">{{ $submission->created_at->format('M d, Y H:i') }}</div></td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.contact-submissions.show', $submission) }}" class="act-btn act-edit" title="View Submission">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Submission"
                                onclick="confirmDelete('{{ route('admin.cms.contact-submissions.destroy', $submission) }}','{{ addslashes($submission->name) }}')">
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
{{ $submissions->links() }}
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    <h3>No submissions found</h3>
    <p>Contact form submissions from the public website will appear here.</p>
</div>
@endif

@endsection
