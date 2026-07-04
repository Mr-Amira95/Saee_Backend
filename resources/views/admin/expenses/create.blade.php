@extends('admin.layouts.app')

@section('title', 'Record Expense')
@section('page-title', 'Record Expense')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.expenses.index') }}">Expenses</a>
    <span class="sep">/</span>
    <span class="current">Record New</span>
@endsection

@section('content')
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.expenses.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Record Expense</h1>
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
            <div class="form-section-title">Expense Details</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select name="category" class="form-input @error('category') err @enderror" required>
                        <option value="">— Select —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>
                                {{ $cat->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (JD) <span class="req">*</span></label>
                    <input type="number" name="amount" class="form-input @error('amount') err @enderror"
                           value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
                    @error('amount')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Date <span class="req">*</span></label>
                    <input type="date" name="payment_date" class="form-input @error('payment_date') err @enderror"
                           value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                    @error('payment_date')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method <span class="req">*</span></label>
                    <select name="payment_method" class="form-input @error('payment_method') err @enderror" required>
                        <option value="">— Select —</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash"          {{ old('payment_method') === 'cash'          ? 'selected' : '' }}>Cash</option>
                        <option value="cliq"          {{ old('payment_method') === 'cliq'          ? 'selected' : '' }}>CliQ</option>
                        <option value="cheque"        {{ old('payment_method') === 'cheque'        ? 'selected' : '' }}>Cheque</option>
                    </select>
                    @error('payment_method')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Description <span class="req">*</span></label>
                    <input type="text" name="description" class="form-input @error('description') err @enderror"
                           value="{{ old('description') }}" placeholder="Brief description of the expense" required>
                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Additional Info</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Vendor / Payee <span class="opt">(optional)</span></label>
                    <input type="text" name="vendor" class="form-input"
                           value="{{ old('vendor') }}" placeholder="e.g. Landlord name, utility company">
                </div>
                <div class="form-group">
                    <label class="form-label">Reference Number <span class="opt">(optional)</span></label>
                    <input type="text" name="reference_number" class="form-input"
                           value="{{ old('reference_number') }}" placeholder="Invoice #, cheque #, etc.">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Receipt / Attachment <span class="opt">(optional)</span></label>
                    <input type="file" name="receipt" class="form-input"
                           accept=".jpg,.jpeg,.png,.pdf">
                    <span style="font-size:.75rem;color:var(--text-dim);margin-top:4px;display:block;">JPG, PNG or PDF — max 5MB</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.expenses.index') }}" class="btn-secondary">Cancel</a>
            @if(auth()->user()->hasAdminAction('finances.expenses'))
            <button type="submit" class="btn-primary">Save Expense</button>
            @endif
        </div>
    </form>
@endsection
