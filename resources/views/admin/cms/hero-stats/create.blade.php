@extends('admin.layouts.app')

@section('title', 'Add Stat')
@section('page-title', 'Add Stat')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.hero-stats.index') }}">Hero Stats</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.hero-stats.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add Hero Stat</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.hero-stats.store') }}">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Stat Key</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Key (English) <span class="req">*</span></label>
                    <input type="text" name="key[en]" class="form-input @error('key.en') err @enderror"
                           value="{{ old('key.en') }}" placeholder="e.g. Years of Experience" required>
                    @error('key.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Key (Arabic) <span class="req">*</span></label>
                    <input type="text" name="key[ar]" dir="rtl" class="form-input @error('key.ar') err @enderror"
                           value="{{ old('key.ar') }}" required>
                    @error('key.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Stat Value</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Value (English) <span class="req">*</span></label>
                    <input type="text" name="value[en]" class="form-input @error('value.en') err @enderror"
                           value="{{ old('value.en') }}" placeholder="e.g. 10+" required>
                    @error('value.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Value (Arabic) <span class="req">*</span></label>
                    <input type="text" name="value[ar]" dir="rtl" class="form-input @error('value.ar') err @enderror"
                           value="{{ old('value.ar') }}" required>
                    @error('value.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Ordering</div>
            <div class="form-group">
                <label class="form-label">Sort Order <span class="req">*</span></label>
                <input type="number" name="sort_order" class="form-input"
                       value="{{ old('sort_order', 0) }}" min="0" required>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.hero-stats.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Stat</button>
        </div>
    </form>
</div>
@endsection
