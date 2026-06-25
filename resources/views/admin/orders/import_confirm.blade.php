@extends('admin.layouts.app')

@section('title', 'Confirm Import')
@section('page-title', 'Confirm Import')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">Orders</a>
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.import') }}">Bulk Import</a>
    <span class="sep">/</span>
    <span class="current">Confirm</span>
@endsection

@section('head')
<style>
    .form-wrap { max-width: 100% !important; }

    .confirm-table-wrap {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--bdr);
    }

    .confirm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
        min-width: 1600px;
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

    /* compact inputs inside the table */
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

    .tbl-select option {
        background: #1a2040;
        color: var(--text);
    }

    .col-client   { min-width: 170px; }
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
</style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Confirm Import</h1>
            <p>Review and adjust the {{ count($rows) }} order(s) below, then click <strong>Confirm &amp; Import</strong> to save them.</p>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.orders.import') }}" class="btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Cancel
            </a>
        </div>
    </div>

    <div class="flash flash-info" style="margin-bottom: 22px; border-radius: 14px; border-color: rgba(59,130,246,0.3); background: rgba(59,130,246,0.05);">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            All rows passed validation. You can edit any field using the dropdowns and inputs below before confirming.
        </div>
    </div>

    <form action="{{ route('admin.orders.import.confirm') }}" method="POST" id="confirmForm">
        @csrf

        <div class="confirm-table-wrap">
            <table class="confirm-table">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-client">Client</th>
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    <tr>
                        <td class="col-num">{{ $i + 1 }}</td>

                        {{-- Client --}}
                        <td class="col-client">
                            <select name="rows[{{ $i }}][client_profile_id]" class="tbl-select">
                                <option value="">— Select —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ (isset($row['client_id']) && $row['client_id'] == $client->id) ? 'selected' : '' }}>
                                        {{ $client->id }} · {{ $client->company_name }}
                                    </option>
                                @endforeach
                            </select>
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
                            <select name="rows[{{ $i }}][area_id]"
                                class="tbl-select area-select"
                                data-row="{{ $i }}"
                                data-selected-area="{{ $row['area_id'] ?? '' }}">
                                <option value="">— Area —</option>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="form-actions" style="margin-top: 22px;">
            <a href="{{ route('admin.orders.import') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Confirm &amp; Import {{ count($rows) }} Order{{ count($rows) !== 1 ? 's' : '' }}
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    // Build areas map: { city_id: [{id, name}, ...] }
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
        const row       = citySelect.dataset.row;
        const cityId    = parseInt(citySelect.value);
        const areaSelect = document.querySelector(`.area-select[data-row="${row}"]`);
        const selectedArea = parseInt(areaSelect.dataset.selectedArea) || 0;

        areaSelect.innerHTML = '<option value="">— Area —</option>';

        const areas = areasMap[cityId] || [];
        areas.forEach(function(area) {
            const opt = document.createElement('option');
            opt.value = area.id;
            opt.textContent = area.id + ' · ' + area.name;
            if (area.id === selectedArea) opt.selected = true;
            areaSelect.appendChild(opt);
        });
    }

    // Init all city selects on page load
    document.querySelectorAll('.city-select').forEach(function(sel) {
        populateAreas(sel);
        sel.addEventListener('change', function() {
            // Clear the pre-selected area when user manually changes city
            const row = this.dataset.row;
            document.querySelector(`.area-select[data-row="${row}"]`).dataset.selectedArea = '';
            populateAreas(this);
        });
    });
</script>
@endsection
