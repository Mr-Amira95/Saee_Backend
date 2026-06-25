@extends('client.layouts.app')
@section('title', __('New Order'))
@section('page-title', __('New Order'))

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Create New Order') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Fill in the shipment details below') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('client.orders.store') }}" id="orderForm">
@csrf

<div class="grid-2" style="align-items:start;">

    {{-- Left column --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        {{-- Shipment Info card --}}
        <div class="card">
            <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;">{{ __('Shipment Details') }}</div>

            <div class="form-group">
                <label class="form-label" for="order_description">{{ __('Shipment Contents / Description') }}</label>
                <textarea id="order_description" name="order_description" class="form-textarea" placeholder="{{ __('E.g. Electronics — 2 items') }}">{{ old('order_description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Payment Type *') }}</label>
                <div style="display:flex;gap:12px;">
                    <label style="flex:1;display:flex;align-items:center;gap:9px;background:var(--in-bg);border:1px solid var(--in-bdr);border-radius:9px;padding:12px 14px;cursor:pointer;" id="codLabel">
                        <input type="radio" name="payment_type" value="cod" {{ old('payment_type','cod') === 'cod' ? 'checked' : '' }} onchange="onPaymentChange()" style="width:15px;height:15px;accent-color:var(--red);">
                        <span style="font-size:.88rem;font-weight:600;">{{ __('COD') }}</span>
                        <span style="font-size:.74rem;color:var(--text-dim);">{{ __('Cash on delivery') }}</span>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:9px;background:var(--in-bg);border:1px solid var(--in-bdr);border-radius:9px;padding:12px 14px;cursor:pointer;" id="prepaidLabel">
                        <input type="radio" name="payment_type" value="prepaid" {{ old('payment_type') === 'prepaid' ? 'checked' : '' }} onchange="onPaymentChange()" style="width:15px;height:15px;accent-color:var(--red);">
                        <span style="font-size:.88rem;font-weight:600;">{{ __('Paid') }}</span>
                        <span style="font-size:.74rem;color:var(--text-dim);">{{ __('Already paid') }}</span>
                    </label>
                </div>
            </div>

            <div id="codPriceGroup" class="form-group" style="{{ old('payment_type','cod') !== 'cod' ? 'display:none' : '' }}">
                <label class="form-label" for="order_price">{{ __('COD Order Price (JD) *') }}</label>
                <input id="order_price" name="order_price" type="number" step="0.01" min="0" class="form-input {{ $errors->has('order_price') ? 'has-error' : '' }}" placeholder="0.00" value="{{ old('order_price') }}">
                @error('order_price') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="hidden" name="delivery_on_customer" value="0">
                    <input type="checkbox" name="delivery_on_customer" value="1" id="deliveryOnCustomer" {{ old('delivery_on_customer') ? 'checked' : '' }} onchange="onDeliveryChange()" style="width:16px;height:16px;accent-color:var(--red);">
                    <span style="font-size:.88rem;font-weight:600;">{{ __('Delivery Charges On Customer') }}</span>
                </label>
                <div class="form-hint">{{ __('Enable if the customer pays the delivery fee separately.') }}</div>
            </div>

            <div id="customerFeeGroup" class="form-group" style="{{ old('delivery_on_customer') ? '' : 'display:none' }}">
                <label class="form-label" for="delivery_customer_amount">{{ __('Customer Delivery Fee (JD) *') }}</label>
                <input id="delivery_customer_amount" name="delivery_customer_amount" type="number" step="0.01" min="0" class="form-input {{ $errors->has('delivery_customer_amount') ? 'has-error' : '' }}" placeholder="0.00" value="{{ old('delivery_customer_amount') }}">
                @error('delivery_customer_amount') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Notes card --}}
        <div class="card">
            <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;">{{ __('Instructions') }}</div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" for="notes">{{ __('Special Delivery Instructions / Notes') }}</label>
                <textarea id="notes" name="notes" class="form-textarea" placeholder="{{ __('Any special instructions for the delivery driver…') }}">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        {{-- Receiver card --}}
        <div class="card">
            <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;">{{ __('Receiver Information') }}</div>

            <div class="form-group">
                <label class="form-label" for="receiver_name">{{ __('Receiver Name *') }}</label>
                <input id="receiver_name" name="receiver_name" type="text" class="form-input {{ $errors->has('receiver_name') ? 'has-error' : '' }}" placeholder="{{ __('Full name') }}" value="{{ old('receiver_name') }}">
                @error('receiver_name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="receiver_phone">{{ __('Receiver Phone *') }}</label>
                <input id="receiver_phone" name="receiver_phone" type="tel" class="form-input {{ $errors->has('receiver_phone') ? 'has-error' : '' }}" placeholder="{{ __('07xxxxxxxx') }}" value="{{ old('receiver_phone') }}">
                @error('receiver_phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="city_id">{{ __('City *') }}</label>
                <select id="city_id" name="city_id" class="form-select {{ $errors->has('city_id') ? 'has-error' : '' }}" onchange="loadAreas(this.value)">
                    <option value="">{{ __('Select city…') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
                @error('city_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="area_id">{{ __('Area *') }}</label>
                <select id="area_id" name="area_id" class="form-select {{ $errors->has('area_id') ? 'has-error' : '' }}">
                    <option value="">{{ __('Select area…') }}</option>
                </select>
                @error('area_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" for="address_text">{{ __('Address Details *') }}</label>
                <textarea id="address_text" name="address_text" class="form-textarea {{ $errors->has('address_text') ? 'has-error' : '' }}" placeholder="{{ __('Street, building, floor, landmarks…') }}" style="min-height:72px;">{{ old('address_text') }}</textarea>
                @error('address_text') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary" style="width:100%;padding:13px 24px;justify-content:center;font-size:.92rem;" id="submitBtn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ __('Create Order') }}
        </button>
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
function onPaymentChange() {
    const isCod = document.querySelector('input[name="payment_type"]:checked')?.value === 'cod';
    document.getElementById('codPriceGroup').style.display = isCod ? '' : 'none';
    if (!isCod) document.getElementById('order_price').value = '';
}

function onDeliveryChange() {
    const checked = document.getElementById('deliveryOnCustomer').checked;
    document.getElementById('customerFeeGroup').style.display = checked ? '' : 'none';
    if (!checked) document.getElementById('delivery_customer_amount').value = '';
}

const preselectedCity  = '{{ old('city_id') }}';
const preselectedArea  = '{{ old('area_id') }}';

function loadAreas(cityId, preselect) {
    const sel = document.getElementById('area_id');
    sel.innerHTML = '<option value="">{{ __('Loading…') }}</option>';
    if (!cityId) { sel.innerHTML = '<option value="">{{ __('Select area…') }}</option>'; return; }

    fetch(`{{ route('client.api.areas') }}?city_id=${cityId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(areas => {
        sel.innerHTML = '<option value="">{{ __('Select area…') }}</option>';
        areas.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id; opt.textContent = a.name;
            if (preselect && String(a.id) === String(preselect)) opt.selected = true;
            sel.appendChild(opt);
        });
    })
    .catch(() => { sel.innerHTML = '<option value="">Error loading areas</option>'; });
}

if (preselectedCity) loadAreas(preselectedCity, preselectedArea);

document.getElementById('orderForm').addEventListener('submit', () => {
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').style.opacity = '.6';
});
</script>
@endpush
