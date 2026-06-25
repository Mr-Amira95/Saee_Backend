@extends('client.layouts.app')
@section('title', __('Import Orders'))
@section('page-title', __('Import Orders'))

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Import Orders') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Upload a CSV file to create multiple orders at once') }}</p>
    </div>
</div>

@if(session('error'))
    <div class="flash flash-err">{{ session('error') }}</div>
@endif

<div class="grid-2" style="align-items:start;">

    {{-- Upload form --}}
    <div class="card">
        <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Upload CSV File') }}</div>

        <form method="POST" action="{{ route('client.orders.import.submit') }}" enctype="multipart/form-data" id="importForm">
            @csrf

            <div id="dropzone"
                style="border:2px dashed rgba(255,255,255,.1);border-radius:12px;padding:36px 24px;text-align:center;cursor:pointer;transition:border-color .2s;margin-bottom:18px;"
                ondragover="event.preventDefault();this.style.borderColor='rgba(220,38,38,.4)';"
                ondragleave="this.style.borderColor='rgba(255,255,255,.1)';"
                ondrop="handleDrop(event)"
                onclick="document.getElementById('csvInput').click()">
                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24" style="color:var(--text-dim);margin-bottom:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <div style="font-size:.9rem;font-weight:600;color:var(--text-sub);">{{ __('Drop CSV file here, or click to browse') }}</div>
                <div style="font-size:.78rem;color:var(--text-dim);margin-top:6px;">{{ __('Maximum file size: 4 MB') }}</div>
                <div id="fileNameDisplay" style="display:none;margin-top:10px;">
                    <span style="font-size:.84rem;color:var(--red-lt);font-weight:600;" id="fileNameText"></span>
                </div>
            </div>

            <input type="file" id="csvInput" name="csv_file" accept=".csv,.txt" style="display:none;" onchange="onFileSelect(this)">

            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;" id="importBtn" disabled>
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                {{ __('Upload & Import') }}
            </button>
        </form>

        <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:.82rem;color:var(--text-dim);">{{ __('Download the template to get started:') }}</div>
            <a href="{{ route('client.orders.template') }}" class="btn-secondary" style="padding:7px 14px;font-size:.81rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                {{ __('Download Template') }}
            </a>
        </div>
    </div>

    {{-- Format guide --}}
    <div class="card">
        <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:14px;">{{ __('CSV Format Guide') }}</div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            @foreach([
                ['order_description', 'Text', 'Optional — describe the shipment contents'],
                ['payment_type', 'cod / prepaid', 'Required'],
                ['delivery_on_customer', 'true / false', 'Whether delivery fee is charged to receiver'],
                ['delivery_customer_amount', 'Number', 'Required if delivery_on_customer is true'],
                ['order_price', 'Number', 'Required for COD orders'],
                ['receiver_name', 'Text', 'Required'],
                ['receiver_phone', 'Text', 'Required'],
                ['city_id', 'Number', 'City ID (see admin for IDs)'],
                ['area_id', 'Number', 'Area ID (must belong to the city)'],
                ['address_text', 'Text', 'Required — full delivery address'],
                ['notes', 'Text', 'Optional — special instructions'],
            ] as [$col, $type, $desc])
            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.03);">
                <code style="font-size:.74rem;background:rgba(255,255,255,.04);padding:2px 7px;border-radius:5px;flex-shrink:0;color:var(--red-lt);">{{ $col }}</code>
                <div>
                    <span style="font-size:.76rem;font-weight:600;color:var(--text-sub);">{{ $type }}</span>
                    <div style="font-size:.75rem;color:var(--text-dim);">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Validation errors table --}}
@if(isset($has_errors) && $has_errors && isset($results))
<div class="card" style="margin-top:20px;">
    <div style="font-size:.88rem;font-weight:700;color:#f87171;margin-bottom:14px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Validation Errors — Fix these issues and re-upload
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Row') }}</th>
                    <th>{{ __('Receiver') }}</th>
                    <th>{{ __('Errors') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                @if(!empty($result['errors']))
                <tr>
                    <td style="color:var(--text-dim);">Row {{ $result['row_number'] }}</td>
                    <td>{{ $result['data']['receiver_name'] ?? '—' }}</td>
                    <td>
                        <ul style="margin:0;padding-left:16px;">
                            @foreach($result['errors'] as $err)
                            <li style="font-size:.81rem;color:#fca5a5;margin-bottom:2px;">{{ $err }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function onFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('fileNameText').textContent = file.name;
        document.getElementById('fileNameDisplay').style.display = 'block';
        document.getElementById('importBtn').disabled = false;
    }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').style.borderColor = 'rgba(255,255,255,.1)';
    const files = e.dataTransfer.files;
    if (files && files[0]) {
        document.getElementById('csvInput').files = files;
        onFileSelect(document.getElementById('csvInput'));
    }
}

document.getElementById('importForm').addEventListener('submit', () => {
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.style.opacity = '.6';
});
</script>
@endpush
