@extends('admin.layouts.app')

@section('title', 'New Payroll — ' . $driver->user->name)
@section('page-title', 'New Payroll Entry')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.payroll.index') }}">Payroll</a>
    <span class="sep">/</span>
    <span class="current">New — {{ $driver->user->name }}</span>
@endsection

@section('content')
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.payroll.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">New Payroll Entry — {{ $driver->user->name }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.payroll.store', $driver) }}">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Pay Period</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Period Start <span class="req">*</span></label>
                    <input type="date" name="period_start" class="form-input @error('period_start') err @enderror"
                           value="{{ old('period_start', $periodStart->toDateString()) }}" required>
                    @error('period_start')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Period End <span class="req">*</span></label>
                    <input type="date" name="period_end" class="form-input @error('period_end') err @enderror"
                           value="{{ old('period_end', $periodEnd->toDateString()) }}" required>
                    @error('period_end')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Salary Breakdown</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Basic Salary <span class="req">*</span></label>
                    <input type="number" name="basic_salary" class="form-input @error('basic_salary') err @enderror"
                           value="{{ old('basic_salary', $driver->basic_salary) }}" step="0.01" min="0" placeholder="0.00" required
                           oninput="recalc()">
                    @error('basic_salary')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Car & Gasoline Allowance <span class="req">*</span></label>
                    <input type="number" name="car_allowance" class="form-input @error('car_allowance') err @enderror"
                           value="{{ old('car_allowance', $driver->car_allowance) }}" step="0.01" min="0" placeholder="0.00" required
                           oninput="recalc()">
                    @error('car_allowance')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Daily Order Threshold</label>
                    <input type="number" id="daily-threshold" class="form-input" value="{{ $driver->daily_order_threshold }}" min="0" placeholder="0"
                           oninput="calcExtraOrders()">
                    <span style="font-size:.75rem;color:var(--text-dim);margin-top:4px;display:block;">
                        Orders per day above this count earn a bonus — used to auto-fill the field below
                    </span>
                </div>
                <div class="form-group">
                    <label class="form-label">Bonus Per Extra Order</label>
                    <input type="number" name="extra_order_bonus" class="form-input @error('extra_order_bonus') err @enderror"
                           value="{{ old('extra_order_bonus', $driver->bonus_per_extra_order) }}" step="0.01" min="0" placeholder="0.00"
                           oninput="recalc()">
                    @error('extra_order_bonus')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Extra Orders Above Threshold</label>
                    <input type="number" name="extra_orders_count" id="extra-orders-count" class="form-input @error('extra_orders_count') err @enderror"
                           value="{{ old('extra_orders_count', 0) }}" min="0" placeholder="0"
                           oninput="recalc()">
                    <span style="font-size:.75rem;color:var(--text-dim);margin-top:4px;display:block;">
                        Auto-calculated from daily threshold above — or enter manually
                    </span>
                    @error('extra_orders_count')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Deductions</label>
                    <input type="number" name="deductions" class="form-input @error('deductions') err @enderror"
                           value="{{ old('deductions', 0) }}" step="0.01" min="0" placeholder="0.00"
                           oninput="recalc()">
                    @error('deductions')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="display:flex;flex-direction:column;justify-content:flex-end;">
                    <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:10px;padding:14px 16px;">
                        <div style="font-size:.72rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Net Amount</div>
                        <div id="net-display" style="font-size:1.4rem;font-weight:800;color:#22c55e;">0.00 JD</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">Payment Details</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Payment Method <span class="req">*</span></label>
                    <select name="payment_method" class="form-input @error('payment_method') err @enderror" required>
                        <option value="">— Select —</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash"          {{ old('payment_method') === 'cash'          ? 'selected' : '' }}>Cash</option>
                        <option value="cliq"          {{ old('payment_method') === 'cliq'          ? 'selected' : '' }}>CliQ</option>
                    </select>
                    @error('payment_method')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Reference Number <span class="opt">(optional)</span></label>
                    <input type="text" name="reference_number" class="form-input"
                           value="{{ old('reference_number') }}" placeholder="e.g. TXN-1234">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Notes <span class="opt">(optional)</span></label>
                    <textarea name="notes" class="form-input" rows="3"
                              placeholder="Any additional notes..." style="height:auto;">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.payroll.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Draft</button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
var dailyOrders = @json($dailyOrders);

function calcExtraOrders() {
    var threshold = parseInt(document.getElementById('daily-threshold').value) || 0;
    var total = 0;
    Object.values(dailyOrders).forEach(function(cnt) {
        total += Math.max(0, cnt - threshold);
    });
    document.getElementById('extra-orders-count').value = total;
    recalc();
}

function recalc() {
    var basic  = parseFloat(document.querySelector('[name=basic_salary]').value)       || 0;
    var allow  = parseFloat(document.querySelector('[name=car_allowance]').value)      || 0;
    var extra  = parseFloat(document.getElementById('extra-orders-count').value)       || 0;
    var bonus  = parseFloat(document.querySelector('[name=extra_order_bonus]').value)  || 0;
    var deduct = parseFloat(document.querySelector('[name=deductions]').value)         || 0;
    var gross  = basic + allow + (extra * bonus);
    var net    = Math.max(0, gross - deduct);
    document.getElementById('net-display').textContent = net.toFixed(2) + ' JD';
}

document.addEventListener('DOMContentLoaded', function() {
    calcExtraOrders();
    recalc();
});
</script>
@endsection
