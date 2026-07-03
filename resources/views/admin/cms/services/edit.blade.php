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
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Edit Service: {{ $service->title['en'] ?? '' }}</h1>
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
            <div class="form-section-title">Service Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Title (English) <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en', $service->title['en'] ?? '') }}" placeholder="e.g. Next-Day Delivery" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $service->title['ar'] ?? '') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Service Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Subtitle (English)</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="5"
                              placeholder="Explain what the service covers, rates, SLA, etc..." style="height: auto;">{{ old('subtitle.en', $service->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="5" style="height: auto;">{{ old('subtitle.ar', $service->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Icon</div>
            <div class="form-group">
                <label class="form-label">Icon (Emoji or CSS class)</label>
                <input type="text" name="icon" class="form-input"
                       value="{{ old('icon', $service->icon) }}" placeholder="e.g. ⚡, ✈️, 📦, truck">
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
