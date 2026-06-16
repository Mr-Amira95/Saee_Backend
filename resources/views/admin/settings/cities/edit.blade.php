@extends('admin.layouts.app')

@section('title', 'Edit – '.$city->name)
@section('page-title', 'Edit City')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cities.index') }}">Cities &amp; Areas</a>
    <span class="sep">/</span>
    <span class="current">{{ $city->name }}</span>
@endsection

@section('head')
<style>
.area-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--bdr);
}
.area-row:last-child { border-bottom: none; }
.area-name  { flex: 1; font-size: .88rem; font-weight: 500; color: var(--text); }
.area-name-ar { font-size: .8rem; color: var(--text-sub); direction: rtl; }
.area-status { font-size: .75rem; }
.area-delete form { margin: 0; }
.area-delete button {
    background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.2);
    color: #f87171; border-radius: 6px; padding: 5px 10px;
    font-size: .78rem; cursor: pointer; transition: background .15s;
}
.area-delete button:hover { background: rgba(220,38,38,.22); }
.add-area-row {
    display: grid; grid-template-columns: 1fr 1fr auto;
    gap: 10px; align-items: center; padding: 14px;
    background: rgba(220,38,38,.04); border-top: 1px solid rgba(220,38,38,.12);
}
</style>
@endsection

@section('content')
<div>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cities.index') }}" class="btn-secondary">&#8592; Back</a>
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Edit City</h1>
            <p style="font-size:.82rem;color:var(--text-sub);margin:2px 0 0;">{{ $city->name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="flash flash-ok" style="margin-bottom:18px;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
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

    {{-- ── City Fields ── --}}
    <form method="POST" action="{{ route('admin.cities.update', $city) }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">City Details</div>
            <div class="form-grid-2">

                <div class="form-group">
                    <label class="form-label">Name (EN) <span class="req">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') err @enderror"
                           value="{{ old('name', $city->name) }}" required>
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Name (AR)</label>
                    <input type="text" name="name_ar" class="form-input" dir="rtl"
                           value="{{ old('name_ar', $city->name_ar) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Default Delivery Price</label>
                    <div style="display:flex;align-items:stretch;border:1px solid var(--bdr);border-radius:8px;overflow:hidden;background:var(--in-bg);">
                        <input type="number" name="delivery_price" class="form-input @error('delivery_price') err @enderror"
                               value="{{ old('delivery_price', $city->delivery_price) }}" min="0" step="0.01"
                               style="border:none;background:transparent;flex:1;padding:0 12px;height:42px;outline:none;">
                        <span style="display:flex;align-items:center;padding:0 14px;font-size:.82rem;font-weight:700;color:var(--red);background:rgba(220,38,38,.06);border-left:1px solid var(--bdr);flex-shrink:0;">JD</span>
                    </div>
                    @error('delivery_price')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="justify-content:flex-end;">
                    <label class="form-label">Status</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding-top:4px;">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $city->is_active) ? 'checked' : '' }}
                               style="accent-color:var(--red);width:16px;height:16px;">
                        <span style="font-size:.88rem;color:var(--text-sub);">Active</span>
                    </label>
                </div>

            </div>
        </div>

        <div class="form-actions" style="margin-bottom:28px;">
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>

    {{-- ── Areas Management ── --}}
    <div class="form-section" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;">
            <div class="form-section-title" style="margin:0;padding:0;border:none;">
                Areas / Districts
                <span style="font-size:.72rem;font-weight:400;color:var(--text-dim);text-transform:none;margin-left:8px;">
                    ({{ $city->areas->count() }} areas)
                </span>
            </div>
        </div>

        {{-- Existing areas --}}
        @if($city->areas->count())
            @foreach($city->areas->sortBy('name') as $area)
            <div class="area-row">
                <div style="flex:1;">
                    <div class="area-name">{{ $area->name }}</div>
                    @if($area->name_ar)
                    <div class="area-name-ar">{{ $area->name_ar }}</div>
                    @endif
                </div>
                @if($area->is_active)
                    <span class="badge-active area-status">Active</span>
                @else
                    <span class="badge-suspended area-status">Inactive</span>
                @endif
                <div class="area-delete">
                    <form method="POST"
                          action="{{ route('admin.cities.areas.destroy', [$city, $area]) }}"
                          onsubmit="return confirm('Delete area \'{{ addslashes($area->name) }}\'?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">&#10005; Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        @else
        <div style="padding:24px;text-align:center;color:var(--text-dim);font-size:.85rem;">
            No areas yet. Add one below.
        </div>
        @endif

        {{-- Add area form --}}
        <form method="POST" action="{{ route('admin.cities.areas.store', $city) }}">
            @csrf
            <div class="add-area-row">
                <div>
                    <input type="text" name="name" class="form-input"
                           placeholder="Area name (EN) *" style="height:38px;" required>
                </div>
                <div>
                    <input type="text" name="name_ar" class="form-input"
                           placeholder="اسم المنطقة (AR)" style="height:38px;" dir="rtl">
                </div>
                <button type="submit" class="btn-primary" style="height:38px;padding:0 16px;font-size:.85rem;">
                    + Add Area
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
