@extends('admin.layouts.app')

@section('title', 'New Invoice — ' . ($client->company_name ?? $client->user->name))
@section('page-title', 'Generate Delivery Invoice')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.billing.index') }}">Billing</a>
    <span class="sep">/</span>
    <span class="current">New — {{ $client->company_name ?? $client->user->name }}</span>
@endsection

@section('content')
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.billing.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">
            New Delivery Invoice — {{ $client->company_name ?? $client->user->name }}
        </h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div style="background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:.85rem;color:var(--text-sub);">
        The system will automatically find all delivered orders for this client within the selected period
        that have a delivery fee charged to the client (delivery_on_customer = false) and are not yet billed.
    </div>

    <form method="POST" action="{{ route('admin.billing.store', $client) }}" enctype="multipart/form-data">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Billing Period</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Period Start <span class="req">*</span></label>
                    <input type="date" name="period_start" class="form-input @error('period_start') err @enderror"
                           value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}" required>
                    @error('period_start')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Period End <span class="req">*</span></label>
                    <input type="date" name="period_end" class="form-input @error('period_end') err @enderror"
                           value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}" required>
                    @error('period_end')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Optional Adjustments & Electronic Invoice Details</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Discount Amount</label>
                    <input type="number" name="discount_amount" class="form-input @error('discount_amount') err @enderror"
                           value="{{ old('discount_amount', 0) }}" step="0.01" min="0" placeholder="0.00">
                    @error('discount_amount')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Electronic Invoice Number</label>
                    <input type="text" name="electronic_invoice_number" class="form-input @error('electronic_invoice_number') err @enderror"
                           value="{{ old('electronic_invoice_number') }}" placeholder="e.g. e-inv-123456">
                    @error('electronic_invoice_number')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">QR Image Attachment</label>
                    <input type="file" name="qr_attachment" class="form-input @error('qr_attachment') err @enderror" accept="image/*">
                    @error('qr_attachment')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Notes <span class="opt">(optional)</span></label>
                    <input type="text" name="notes" class="form-input"
                           value="{{ old('notes') }}" placeholder="e.g. Promo discount applied">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.billing.index') }}" class="btn-secondary">Cancel</a>
            @if(auth()->user()->hasAdminAction('finances.client_billing'))
            <button type="submit" class="btn-primary">Generate Draft Invoice</button>
            @endif
        </div>
    </form>
@endsection
