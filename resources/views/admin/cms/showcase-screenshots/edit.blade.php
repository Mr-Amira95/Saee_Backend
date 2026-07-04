@extends('admin.layouts.app')

@section('title', 'Edit Screenshot')
@section('page-title', 'Edit Screenshot')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.showcase-screenshots.index') }}">Screenshots</a>
    <span class="sep">/</span>
    <span class="current">Edit</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.showcase-screenshots.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Edit Screenshot</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.showcase-screenshots.update', $screenshot) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Screenshot Details</div>

            <div class="form-group">
                <label class="form-label">Category <span class="req">*</span></label>
                <select name="category" class="form-input" style="background: var(--in-bg); color: var(--text);">
                    <option value="application" {{ old('category', $screenshot->category) === 'application' ? 'selected' : '' }}>Application</option>
                    <option value="portal" {{ old('category', $screenshot->category) === 'portal' ? 'selected' : '' }}>Portal</option>
                </select>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">Screenshot Image</label>
                @if($screenshot->image_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ $screenshot->image_path }}" alt="Current screenshot" style="width: 160px; height: 110px; object-fit: cover; border-radius: 8px; border: 1px solid var(--bdr); background: rgba(255,255,255,.05);">
                    </div>
                @endif
                <input type="file" name="image_file" class="form-input @error('image_file') err @enderror"
                       accept="image/*" style="height: auto; padding: 8px;">
                <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">Leave blank to keep current screenshot. Max size: 4MB.</span>
                @error('image_file')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Caption (English)</label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en', $screenshot->title['en'] ?? '') }}">
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Caption (Arabic)</label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $screenshot->title['ar'] ?? '') }}">
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Description (English)</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en', $screenshot->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Arabic)</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar', $screenshot->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Ordering & Visibility</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Sort Order <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $screenshot->sort_order) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', $screenshot->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $screenshot->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.showcase-screenshots.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Screenshot</button>
        </div>
    </form>
</div>
@endsection
