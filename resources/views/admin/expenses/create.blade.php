@extends('admin.layouts.app')

@section('title', __('Record Expense'))
@section('page-title', __('Record Expense'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.expenses.index') }}">{{ __('Expenses') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ __('Record New') }}</span>
@endsection

@section('content')
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.expenses.index') }}" class="btn-secondary">&#8592; {{ __('Back') }}</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">{{ __('Record Expense') }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-section">
            <div class="form-section-title">{{ __('Expense Details') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Category') }} <span class="req">*</span></label>
                    <select name="category" class="form-input @error('category') err @enderror" required>
                        <option value="">{{ __('— Select —') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>
                                {{ $cat->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Amount (JD)') }} <span class="req">*</span></label>
                    <input type="number" name="amount" class="form-input @error('amount') err @enderror"
                           value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
                    @error('amount')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Payment Date') }} <span class="req">*</span></label>
                    <input type="text" id="payment_date" name="payment_date" class="form-input @error('payment_date') err @enderror"
                           value="{{ old('payment_date', now()->format('d-m-Y')) }}" placeholder="DD-MM-YYYY" maxlength="10" autocomplete="off" required>
                    @error('payment_date')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Payment Method') }} <span class="req">*</span></label>
                    <select name="payment_method" class="form-input @error('payment_method') err @enderror" required>
                        <option value="">{{ __('— Select —') }}</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                        <option value="cash"          {{ old('payment_method') === 'cash'          ? 'selected' : '' }}>{{ __('Cash') }}</option>
                        <option value="cliq"          {{ old('payment_method') === 'cliq'          ? 'selected' : '' }}>{{ __('CliQ') }}</option>
                        <option value="cheque"        {{ old('payment_method') === 'cheque'        ? 'selected' : '' }}>{{ __('Cheque') }}</option>
                    </select>
                    @error('payment_method')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">{{ __('Description') }} <span class="req">*</span></label>
                    <input type="text" name="description" class="form-input @error('description') err @enderror"
                           value="{{ old('description') }}" placeholder="{{ __('Brief description of the expense') }}" required>
                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">{{ __('Additional Info') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Vendor / Payee') }} <span class="opt">{{ __('(optional)') }}</span></label>
                    <input type="text" name="vendor" class="form-input"
                           value="{{ old('vendor') }}" placeholder="{{ __('e.g. Landlord name, utility company') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Reference Number') }} <span class="opt">{{ __('(optional)') }}</span></label>
                    <input type="text" name="reference_number" class="form-input"
                           value="{{ old('reference_number') }}" placeholder="{{ __('Invoice #, cheque #, etc.') }}">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">{{ __('Receipt / Attachment') }} <span class="opt">{{ __('(optional)') }}</span></label>
                    <input type="file" name="receipt" class="form-input"
                           accept=".jpg,.jpeg,.png,.pdf">
                    <span style="font-size:.75rem;color:var(--text-dim);margin-top:4px;display:block;">{{ __('JPG, PNG or PDF — max 5MB') }}</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.expenses.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            @if(auth()->user()->hasAdminAction('finances.expenses'))
            <button type="submit" class="btn-primary">{{ __('Save Expense') }}</button>
            @endif
        </div>
    </form>

    <script>
        (function() {
            var el = document.getElementById('payment_date');
            if (!el) return;
            el.addEventListener('input', function() {
                var digits = el.value.replace(/[^0-9]/g, '').slice(0, 8);
                var out = digits.slice(0, 2);
                if (digits.length > 2) out += '-' + digits.slice(2, 4);
                if (digits.length > 4) out += '-' + digits.slice(4, 8);
                el.value = out;
            });
        })();
    </script>
@endsection
