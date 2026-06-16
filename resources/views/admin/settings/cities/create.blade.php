@extends('admin.layouts.app')

@section('title', 'Add City')
@section('page-title', 'Add City')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cities.index') }}">Cities &amp; Areas</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('head')
<style>
.area-row {
    display: grid; grid-template-columns: 1fr 1fr auto;
    gap: 10px; align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid var(--bdr);
}
.area-row:last-child { border-bottom: none; }
.area-remove {
    background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.2);
    color: #f87171; border-radius: 6px; padding: 5px 10px;
    font-size: .78rem; cursor: pointer; transition: background .15s; white-space: nowrap;
}
.area-remove:hover { background: rgba(220,38,38,.22); }
.add-area-row {
    padding: 14px;
    background: rgba(220,38,38,.04); border-top: 1px solid rgba(220,38,38,.12);
}
</style>
@endsection

@section('content')
<div>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cities.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add City / Governorate</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cities.store') }}">
        @csrf

        {{-- City Details --}}
        <div class="form-section">
            <div class="form-section-title">City Details</div>
            <div class="form-grid-2">

                <div class="form-group">
                    <label class="form-label">Name (EN) <span class="req">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') err @enderror"
                           value="{{ old('name') }}" placeholder="e.g. Amman" required>
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Name (AR)</label>
                    <input type="text" name="name_ar" class="form-input" dir="rtl"
                           value="{{ old('name_ar') }}" placeholder="عمّان">
                </div>

                <div class="form-group">
                    <label class="form-label">Default Delivery Price</label>
                    <div style="display:flex;align-items:stretch;border:1px solid var(--bdr);border-radius:8px;overflow:hidden;background:var(--in-bg);">
                        <input type="number" name="delivery_price" class="form-input @error('delivery_price') err @enderror"
                               value="{{ old('delivery_price', 0) }}" min="0" step="0.01"
                               style="border:none;background:transparent;flex:1;padding:0 12px;height:42px;outline:none;">
                        <span style="display:flex;align-items:center;padding:0 14px;font-size:.82rem;font-weight:700;color:var(--red);background:rgba(220,38,38,.06);border-left:1px solid var(--bdr);flex-shrink:0;">JD</span>
                    </div>
                    @error('delivery_price')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="justify-content:flex-end;">
                    <label class="form-label">Status</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding-top:4px;">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', '1') ? 'checked' : '' }}
                               style="accent-color:var(--red);width:16px;height:16px;">
                        <span style="font-size:.88rem;color:var(--text-sub);">Active</span>
                    </label>
                </div>

            </div>
        </div>

        {{-- Areas --}}
        <div class="form-section" style="padding:0;overflow:hidden;margin-top:20px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;">
                <div class="form-section-title" style="margin:0;padding:0;border:none;">
                    Areas / Districts
                    <span style="font-size:.72rem;font-weight:400;color:var(--text-dim);text-transform:none;margin-left:8px;">
                        (optional — can also be added after saving)
                    </span>
                </div>
            </div>

            <div id="areas-list">
                @if(old('areas'))
                    @foreach(old('areas') as $i => $oldArea)
                    <div class="area-row">
                        <input type="text" name="areas[{{ $i }}][name]" class="form-input"
                               placeholder="Area name (EN) *" style="height:38px;"
                               value="{{ $oldArea['name'] ?? '' }}" required>
                        <input type="text" name="areas[{{ $i }}][name_ar]" class="form-input"
                               placeholder="اسم المنطقة (AR)" style="height:38px;" dir="rtl"
                               value="{{ $oldArea['name_ar'] ?? '' }}">
                        <button type="button" class="area-remove" onclick="removeArea(this)">&#10005; Remove</button>
                    </div>
                    @endforeach
                @endif
            </div>

            <div class="add-area-row">
                <button type="button" class="btn-secondary" onclick="addArea()" style="font-size:.85rem;">
                    + Add Area
                </button>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cities.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save City</button>
        </div>
    </form>

</div>

<script>
let areaIndex = {{ old('areas') ? count(old('areas')) : 0 }};

function addArea() {
    const list = document.getElementById('areas-list');
    const row = document.createElement('div');
    row.className = 'area-row';
    row.innerHTML = `
        <input type="text" name="areas[${areaIndex}][name]" class="form-input"
               placeholder="Area name (EN) *" style="height:38px;" required>
        <input type="text" name="areas[${areaIndex}][name_ar]" class="form-input"
               placeholder="اسم المنطقة (AR)" style="height:38px;" dir="rtl">
        <button type="button" class="area-remove" onclick="removeArea(this)">&#10005; Remove</button>
    `;
    list.appendChild(row);
    areaIndex++;
    row.querySelector('input').focus();
}

function removeArea(btn) {
    btn.closest('.area-row').remove();
}
</script>
@endsection
