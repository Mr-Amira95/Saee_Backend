@extends('client.layouts.app')
@section('title', 'Banking Details')
@section('page-title', 'Banking Details')

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.account.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">Banking Details</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">Bank account information for settlements and payouts</p>
    </div>
</div>

<form method="POST" action="{{ route('client.account.banking.save') }}">
@csrf

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">Bank Account</div>

    <div class="form-group">
        <label class="form-label" for="bank_name">Bank Name *</label>
        <input id="bank_name" name="bank_name" type="text" class="form-input {{ $errors->has('bank_name') ? 'has-error' : '' }}"
               placeholder="e.g. Arab Bank" value="{{ old('bank_name', $bankDetail?->bank_name) }}" required>
        @error('bank_name') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="account_name">Account Holder Name *</label>
        <input id="account_name" name="account_name" type="text" class="form-input {{ $errors->has('account_name') ? 'has-error' : '' }}"
               placeholder="As it appears on the account" value="{{ old('account_name', $bankDetail?->account_name) }}" required>
        @error('account_name') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="iban">IBAN *</label>
        <input id="iban" name="iban" type="text" class="form-input {{ $errors->has('iban') ? 'has-error' : '' }}"
               placeholder="JO94CBJO0010000000000131000302" value="{{ old('iban', $bankDetail?->iban) }}"
               style="font-family:monospace;" required>
        @error('iban') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
            <label class="form-label" for="swift_code">SWIFT / BIC Code</label>
            <input id="swift_code" name="swift_code" type="text" class="form-input {{ $errors->has('swift_code') ? 'has-error' : '' }}"
                   placeholder="ARABJOAXXX" value="{{ old('swift_code', $bankDetail?->swift_code) }}" style="font-family:monospace;">
            @error('swift_code') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="account_number">Account Number</label>
            <input id="account_number" name="account_number" type="text" class="form-input {{ $errors->has('account_number') ? 'has-error' : '' }}"
                   placeholder="0000000000" value="{{ old('account_number', $bankDetail?->account_number) }}" style="font-family:monospace;">
            @error('account_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">CliQ Details</div>

    <div class="form-group">
        <label class="form-label">CliQ Type</label>
        <div style="display:flex;gap:12px;">
            <label style="display:flex;align-items:center;gap:9px;background:var(--in-bg);border:1px solid var(--in-bdr);border-radius:9px;padding:10px 14px;cursor:pointer;">
                <input type="radio" name="cliq_alias_type" value="alias" {{ old('cliq_alias_type', $bankDetail?->cliq_alias_type ?? 'alias') === 'alias' ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--red);">
                <span style="font-size:.87rem;font-weight:600;">Alias</span>
            </label>
            <label style="display:flex;align-items:center;gap:9px;background:var(--in-bg);border:1px solid var(--in-bdr);border-radius:9px;padding:10px 14px;cursor:pointer;">
                <input type="radio" name="cliq_alias_type" value="number" {{ old('cliq_alias_type', $bankDetail?->cliq_alias_type) === 'number' ? 'checked' : '' }} style="width:14px;height:14px;accent-color:var(--red);">
                <span style="font-size:.87rem;font-weight:600;">Phone Number</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="cliq_id">CliQ Alias / Number</label>
        <input id="cliq_id" name="cliq_id" type="text" class="form-input {{ $errors->has('cliq_id') ? 'has-error' : '' }}"
               placeholder="yourname or 07xxxxxxxx" value="{{ old('cliq_id', $bankDetail?->cliq_id) }}">
        @error('cliq_id') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:14px;">Notes</div>
    <div class="form-group" style="margin-bottom:0;">
        <textarea name="notes" class="form-textarea" placeholder="Any additional banking notes or instructions…">{{ old('notes', $bankDetail?->notes) }}</textarea>
    </div>
</div>

<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px 24px;font-size:.92rem;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    Save Banking Details
</button>

</form>

@endsection
