@extends('admin.layouts.app')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.services.index') }}">Services</a>
    <span class="sep">/</span>
    <span class="current">Edit</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.services.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Edit Service: {{ $service->title }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.services.update', $service) }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Service Details</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Service Title <span class="req">*</span></label>
                    <input type="text" name="title" class="form-input @error('title') err @enderror"
                           value="{{ old('title', $service->title) }}" placeholder="e.g. Next-Day Delivery" required>
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Icon (Emoji or CSS class)</label>
                    <input type="text" name="icon" class="form-input"
                           value="{{ old('icon', $service->icon) }}" placeholder="e.g. ⚡, ✈️, 📦, truck">
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">Description <span class="req">*</span></label>
                <textarea name="description" class="form-input @error('description') err @enderror" rows="5" 
                          placeholder="Explain what the service covers, rates, SLA, etc..." required style="height: auto;">{{ old('description', $service->description) }}</textarea>
                @error('description')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Ordering & Visibility</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Sort Order <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $service->sort_order) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', $service->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $service->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.services.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Service</button>
        </div>
    </form>
</div>
@endsection
