@extends('admin.layouts.app')

@section('title', 'Submission — ' . $submission->name)
@section('page-title', 'Submission Detail')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.contact-submissions.index') }}">Contact Form Submissions</a>
    <span class="sep">/</span>
    <span class="current">{{ $submission->name }}</span>
@endsection

@section('content')

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#86efac;font-size:.88rem;">
    {{ session('success') }}
</div>
@endif

<div class="page-hd">
    <div class="page-hd-left">
        <h1>{{ $submission->name }}</h1>
        <p>Submitted {{ $submission->created_at->format('d M Y H:i') }}
            &nbsp;·&nbsp; <span style="text-transform:capitalize;">{{ $submission->type }}</span> inquiry
        </p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <button type="button" class="btn-secondary" style="color:#f87171;border-color:rgba(220,38,38,.3);"
            onclick="confirmDelete('{{ route('admin.cms.contact-submissions.destroy', $submission) }}', '{{ addslashes($submission->name) }}')">
            Delete
        </button>
        <a href="{{ route('admin.cms.contact-submissions.index') }}" class="btn-secondary">&larr; Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="table-card" style="height:fit-content;">
        <div style="padding:16px;border-bottom:1px solid var(--bdr);">
            <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Submission Details</h3>
        </div>
        <div style="padding:16px;">
            <div class="info-rows">
                <div class="info-row">
                    <span class="info-row-key">Type</span>
                    <span class="info-row-val" style="text-transform:capitalize;">{{ $submission->type }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Name</span>
                    <span class="info-row-val">{{ $submission->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Company</span>
                    <span class="info-row-val">{{ $submission->company ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Monthly Volume</span>
                    <span class="info-row-val">{{ $submission->monthly_volume ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Email</span>
                    <span class="info-row-val">{{ $submission->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Phone</span>
                    <span class="info-row-val">{{ $submission->phone ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Submitted At</span>
                    <span class="info-row-val">{{ $submission->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="info-row" style="display:block;">
                    <span class="info-row-key">Message</span>
                    <div style="margin-top:6px;font-size:.87rem;color:var(--text-sub);line-height:1.5;white-space:pre-line;">{{ $submission->message }}</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="table-card">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Status</h3>
            </div>
            <div style="padding:16px;">
                <div style="margin-bottom:14px;">
                    @if($submission->status === 'new')
                        <span class="badge-suspended">New</span>
                    @elseif($submission->status === 'contacted')
                        <span class="badge-active">Contacted</span>
                    @else
                        <span class="badge-active">Closed</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.cms.contact-submissions.update-status', $submission) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">Change Status</label>
                        <select name="status" class="form-input">
                            <option value="new" @selected($submission->status === 'new')>New</option>
                            <option value="contacted" @selected($submission->status === 'contacted')>Contacted</option>
                            <option value="closed" @selected($submission->status === 'closed')>Closed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top:10px;">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
