@extends('client.layouts.app')
@section('title', 'Confirm Import')
@section('page-title', 'Confirm Import')

@push('styles')
<style>
    .confirm-table-wrap {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--bdr);
    }

    .confirm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
        min-width: 1200px;
    }

    .confirm-table thead tr {
        background: rgba(12, 18, 48, 0.8);
    }

    .confirm-table th {
        padding: 10px 10px;
        text-align: left;
        color: var(--text-dim);
        font-weight: 600;
        border-bottom: 1px solid var(--bdr);
        white-space: nowrap;
    }

    .confirm-table th.col-num {
        width: 46px;
        text-align: center;
        position: sticky;
        left: 0;
        background: rgba(12, 18, 48, 0.95);
        z-index: 2;
    }

    .confirm-table td {
        padding: 5px 6px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        vertical-align: middle;
    }

    .confirm-table td.col-num {
        text-align: center;
        font-weight: 700;
        color: var(--text-dim);
        position: sticky;
        left: 0;
        background: var(--card);
        z-index: 1;
        border-right: 1px solid var(--bdr);
    }

    .confirm-table tbody tr:hover td {
        background: rgba(255,255,255,0.025);
    }

    .confirm-table tbody tr:hover td.col-num {
        background: var(--card);
    }

    .tbl-input, .tbl-select {
        width: 100%;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--bdr);
        border-radius: 6px;
        color: var(--text);
        font-size: 0.79rem;
        padding: 5px 8px;
        box-sizing: border-box;
        transition: border-color 0.15s, background 0.15s;
        min-width: 0;
    }

    .tbl-input:focus, .tbl-select:focus {
        outline: none;
        border-color: rgba(224, 84, 84, 0.45);
        background: rgba(255,255,255,0.07);
    }

    .tbl-select option { background: #1a2040; color: var(--text); }

    .col-desc     { min-width: 170px; }
    .col-ptype    { min-width: 110px; }
    .col-doc      { min-width: 110px; }
    .col-damount  { min-width: 110px; }
    .col-price    { min-width: 110px; }
    .col-rname    { min-width: 150px; }
    .col-rphone   { min-width: 130px; }
    .col-city     { min-width: 140px; }
    .col-area     { min-width: 140px; }
    .col-address  { min-width: 200px; }
    .col-notes    { min-width: 160px; }
    .col-shift    { min-width: 140px; }
    .col-errors   { min-width: 250px; }

    .form-actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; margin-top: 22px; }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">Confirm Import</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">Review and adjust the {{ count($rows) }} order(s) below, then click <strong>Confirm &amp; Import</strong> to save them.</p>
    </div>
    <a href="{{ route('client.orders.import') }}" class="btn-secondary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Cancel
    </a>
</div>

@if(!empty($rowErrors))
<div class="flash flash-err" style="margin-bottom: 22px; border-radius: 14px;">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <div>
        <strong style="display: block; font-size: 0.95rem; margin-bottom: 3px;">Validation Errors Found</strong>
        Some spreadsheet records contain formatting errors. Please correct the highlighted cells below before confirming.
    </div>
</div>
@else
<div class="flash flash-info" style="margin-bottom: 22px; border-radius: 14px; border-color: rgba(59,130,246,0.3); background: rgba(59,130,246,0.05);">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
        All rows passed validation. You can edit any field using the dropdowns and inputs below before confirming.
    </div>
</div>
@endif

<form action="{{ route('client.orders.import.confirm') }}" method="POST" id="confirmForm">
    @csrf

    <div class="confirm-table-wrap">
        <table class="confirm-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-desc">Description</th>
                    <th class="col-ptype">Payment Type</th>
                    <th class="col-doc">Delivery on Customer</th>
                    <th class="col-damount">Delivery Amount</th>
                    <th class="col-price">Order Price</th>
                    <th class="col-rname">Receiver Name</th>
                    <th class="col-rphone">Receiver Phone</th>
                    <th class="col-city">City</th>
                    <th class="col-area">Area</th>
                    <th class="col-address">Address</th>
                    <th class="col-notes">Notes</th>
                    <th class="col-shift">Delivery Shift</th>
                    @if(!empty($rowErrors))
                    <th class="col-errors" style="color: var(--red-lt);">Validation Errors</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                @php $rowHasErrors = !empty($rowErrors[$i]); @endphp
                <tr style="{{ $rowHasErrors ? 'background: rgba(220, 38, 38, 0.04);' : '' }}">
                    <td class="col-num" style="{{ $rowHasErrors ? 'color: var(--red-lt); font-weight: 800;' : '' }}">
                        {{ $i + 1 }}
                        @if($rowHasErrors)
                            <div style="font-size: 0.65rem; color: #fca5a5; font-weight: 500; margin-top: 4px;">⚠️</div>
                        @endif
                    </td>

                    {{-- Description --}}
                    <td class="col-desc">
                        <input type="text" name="rows[{{ $i }}][order_description]"
                            class="tbl-input"
                            value="{{ $row['order_description'] ?? '' }}">
                    </td>

                    {{-- Payment Type --}}
                    <td class="col-ptype">
                        <select name="rows[{{ $i }}][payment_type]" class="tbl-select">
                            <option value="cod"     {{ strtolower($row['payment_type'] ?? '') === 'cod'     ? 'selected' : '' }}>COD</option>
                            <option value="prepaid" {{ strtolower($row['payment_type'] ?? '') === 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                        </select>
                    </td>

                    {{-- Delivery on Customer --}}
                    <td class="col-doc">
                        <select name="rows[{{ $i }}][delivery_on_customer]" class="tbl-select">
                            @php $doc = strtolower($row['delivery_on_customer'] ?? 'false'); @endphp
                            <option value="false" {{ $doc === 'false' ? 'selected' : '' }}>No (Client pays)</option>
                            <option value="true"  {{ $doc === 'true'  ? 'selected' : '' }}>Yes (Customer pays)</option>
                        </select>
                    </td>

                    {{-- Delivery Customer Amount --}}
                    <td class="col-damount">
                        <input type="number" name="rows[{{ $i }}][delivery_customer_amount]"
                            class="tbl-input"
                            step="0.01" min="0"
                            value="{{ $row['delivery_customer_amount'] ?? '0.00' }}">
                    </td>

                    {{-- Order Price --}}
                    <td class="col-price">
                        <input type="number" name="rows[{{ $i }}][order_price]"
                            class="tbl-input"
                            step="0.01" min="0"
                            value="{{ $row['order_price'] ?? '0.00' }}">
                    </td>

                    {{-- Receiver Name --}}
                    <td class="col-rname">
                        <input type="text" name="rows[{{ $i }}][receiver_name]"
                            class="tbl-input"
                            value="{{ $row['receiver_name'] ?? '' }}">
                    </td>

                    {{-- Receiver Phone --}}
                    <td class="col-rphone">
                        <input type="text" name="rows[{{ $i }}][receiver_phone]"
                            class="tbl-input"
                            value="{{ $row['receiver_phone'] ?? '' }}">
                    </td>

                    {{-- City --}}
                    <td class="col-city">
                        <select name="rows[{{ $i }}][city_id]"
                            class="tbl-select city-select"
                            data-row="{{ $i }}"
                            data-selected-city="{{ $row['city_id'] ?? '' }}">
                            <option value="">— City —</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}"
                                    {{ ($row['city_id'] ?? '') == $city->id ? 'selected' : '' }}>
                                    {{ $city->id }} · {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    {{-- Area --}}
                    <td class="col-area">
                        @php $rowCity = $cities->firstWhere('id', $row['city_id'] ?? null); @endphp
                        <select name="rows[{{ $i }}][area_id]"
                            class="tbl-select area-select"
                            data-row="{{ $i }}"
                            data-selected-area="{{ $row['area_id'] ?? '' }}">
                            <option value="">— Area —</option>
                            @if($rowCity)
                                @foreach($rowCity->areas as $area)
                                    <option value="{{ $area->id }}"
                                        {{ ($row['area_id'] ?? '') == $area->id ? 'selected' : '' }}>
                                        {{ $area->id }} · {{ $area->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </td>

                    {{-- Address --}}
                    <td class="col-address">
                        <input type="text" name="rows[{{ $i }}][address_text]"
                            class="tbl-input"
                            value="{{ $row['address_text'] ?? '' }}">
                    </td>

                    {{-- Notes --}}
                    <td class="col-notes">
                        <input type="text" name="rows[{{ $i }}][notes]"
                            class="tbl-input"
                            value="{{ $row['notes'] ?? '' }}">
                    </td>

                    {{-- Delivery Shift --}}
                    <td class="col-shift">
                        <select name="rows[{{ $i }}][delivery_shift]" class="tbl-select">
                            <option value="doesnt_matter" {{ ($row['delivery_shift'] ?? 'doesnt_matter') === 'doesnt_matter' ? 'selected' : '' }}>Doesn't Matter</option>
                            <option value="before_12pm"   {{ ($row['delivery_shift'] ?? '') === 'before_12pm' ? 'selected' : '' }}>Before 12 PM</option>
                            <option value="after_12pm"    {{ ($row['delivery_shift'] ?? '') === 'after_12pm' ? 'selected' : '' }}>After 12 PM</option>
                        </select>
                    </td>

                    @if(!empty($rowErrors))
                    <td class="col-errors">
                        @if($rowHasErrors)
                            <ul style="margin: 0; padding-left: 12px; color: #fca5a5; font-size: 0.72rem; list-style-type: square; line-height: 1.3;">
                                @foreach($rowErrors[$i] as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color: var(--success); font-size: 0.75rem; font-weight: 600;">✓ Valid Row</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="form-actions">
        <a href="{{ route('client.orders.import') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Confirm &amp; Import {{ count($rows) }} Order{{ count($rows) !== 1 ? 's' : '' }}
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
    const areasMap = {
        @foreach($cities as $city)
        {{ $city->id }}: [
            @foreach($city->areas as $area)
            { id: {{ $area->id }}, name: {{ json_encode($area->name) }} },
            @endforeach
        ],
        @endforeach
    };

    function populateAreas(citySelect) {
        const row        = citySelect.dataset.row;
        const cityId     = parseInt(citySelect.value);
        const areaSelect = document.querySelector(`.area-select[data-row="${row}"]`);
        const selectedArea = parseInt(areaSelect.dataset.selectedArea) || 0;

        areaSelect.innerHTML = '<option value="">— Area —</option>';

        const areas = areasMap[cityId] || [];
        areas.forEach(function (area) {
            const opt = document.createElement('option');
            opt.value = area.id;
            opt.textContent = area.id + ' · ' + area.name;
            if (area.id === selectedArea) opt.selected = true;
            areaSelect.appendChild(opt);
        });
    }

    document.querySelectorAll('.city-select').forEach(function (sel) {
        populateAreas(sel);
        sel.addEventListener('change', function () {
            const row = this.dataset.row;
            document.querySelector(`.area-select[data-row="${row}"]`).dataset.selectedArea = '';
            populateAreas(this);
        });
    });
</script>
@endpush
