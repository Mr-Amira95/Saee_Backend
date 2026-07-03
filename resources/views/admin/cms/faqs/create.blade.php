@extends('admin.layouts.app')

@section('title', 'Add FAQ')
@section('page-title', 'Add FAQ')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.faqs.index') }}">FAQs</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.faqs.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add FAQ</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.faqs.store') }}">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Question</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Question (English) <span class="req">*</span></label>
                    <input type="text" name="question[en]" class="form-input @error('question.en') err @enderror"
                           value="{{ old('question.en') }}" placeholder="e.g. How can I track my package?" required>
                    @error('question.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Question (Arabic) <span class="req">*</span></label>
                    <input type="text" name="question[ar]" dir="rtl" class="form-input @error('question.ar') err @enderror"
                           value="{{ old('question.ar') }}" required>
                    @error('question.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Answer</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Answer (English) <span class="req">*</span></label>
                    <textarea name="answer[en]" class="form-input @error('answer.en') err @enderror" rows="5"
                              placeholder="Provide the detailed answer to the question..." required style="height: auto;">{{ old('answer.en') }}</textarea>
                    @error('answer.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Answer (Arabic) <span class="req">*</span></label>
                    <textarea name="answer[ar]" dir="rtl" class="form-input @error('answer.ar') err @enderror" rows="5"
                              required style="height: auto;">{{ old('answer.ar') }}</textarea>
                    @error('answer.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Ordering & Visibility</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Sort Order <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', 0) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.faqs.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save FAQ</button>
        </div>
    </form>
</div>
@endsection
