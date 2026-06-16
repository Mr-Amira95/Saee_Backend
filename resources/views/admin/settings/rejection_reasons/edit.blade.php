@extends('admin.layouts.app')

@section('title', 'Edit Rejection Reason')
@section('page-title', 'Edit Rejection Reason')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.rejection-reasons.index') }}">Rejection Reasons</a>
    <span class="sep">/</span>
    <span class="current">Edit</span>
@endsection

@section('content')
<div>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.rejection-reasons.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Edit Rejection Reason</h1>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#86efac;font-size:.88rem;">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.rejection-reasons.update', $rejectionReason) }}">
        @csrf
        @method('PUT')
        <div class="form-section">
            <div class="form-section-title">Reason Details</div>
            <div class="form-grid-2">

                <div class="form-group">
                    <label class="form-label">Reason (EN) <span class="req">*</span></label>
                    <input type="text" name="reason" class="form-input @error('reason') err @enderror"
                           value="{{ old('reason', $rejectionReason->reason) }}" placeholder="e.g. Address not found" required>
                    @error('reason')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Reason (AR)</label>
                    <input type="text" name="reason_ar" class="form-input" dir="rtl"
                           value="{{ old('reason_ar', $rejectionReason->reason_ar) }}" placeholder="مثال: العنوان غير موجود">
                </div>

                <div class="form-group" style="justify-content:flex-end;">
                    <label class="form-label">Status</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding-top:4px;">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $rejectionReason->is_active) ? 'checked' : '' }}
                               style="accent-color:var(--red);width:16px;height:16px;">
                        <span style="font-size:.88rem;color:var(--text-sub);">Active</span>
                    </label>
                </div>

            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.rejection-reasons.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Reason</button>
        </div>
    </form>

</div>
@endsection
