@extends('client.layouts.app')
@section('title', 'Bulk Import Orders')
@section('page-title', 'Bulk Import Orders')

@push('styles')
<style>
    .form-wrap { max-width: 100%; }
    .form-section {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 18px;
        backdrop-filter: blur(8px);
    }
    .form-section-title {
        font-size: .8rem;
        font-weight: 700;
        color: var(--text-sub);
        letter-spacing: .07em;
        text-transform: uppercase;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title svg { color: var(--red-lt); opacity: .7; }
    .form-actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; padding-top: 4px; }
    .info-rows { display: flex; flex-direction: column; gap: 10px; }
    .info-row  { display: flex; align-items: flex-start; gap: 12px; font-size: .84rem; color: var(--text-sub); }

    .guidelines-panel {
        background: rgba(12, 18, 48, 0.5) !important;
    }
    html.light-theme .guidelines-panel {
        background: rgba(15, 23, 42, 0.03) !important;
        border-color: rgba(15, 23, 42, 0.08) !important;
    }
</style>
@endpush

@section('content')

<div class="page-hd">
    <div class="page-hd-left" style="display:flex;align-items:center;gap:10px;">
        <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
        <div>
            <h1>Bulk Import Orders</h1>
            <p>Upload a CSV file containing multiple orders to import them instantly.</p>
        </div>
    </div>
    <div class="page-hd-right">
        <a href="{{ route('client.orders.import-image') }}" class="btn-secondary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('AI Image Import') }}
        </a>
        <a href="{{ route('client.orders.template') }}" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV Template
        </a>
    </div>
</div>

@if(session('error'))
    <div class="flash flash-err">{{ session('error') }}</div>
@endif

<div class="form-wrap">

    {{-- Upload Form --}}
    <div class="form-section">
        <div class="form-section-title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Upload CSV File
        </div>

        <form action="{{ route('client.orders.import.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="padding: 30px; border: 2px dashed var(--bdr); border-radius: 12px; text-align: center; background: rgba(255,255,255,0.01); transition: border-color 0.2s;" id="dropzone">
                <input type="file" name="csv_file" id="csv_file" style="display: none;" accept=".csv,.txt" required>
                <div style="font-size: 2.2rem; color: var(--text-dim); margin-bottom: 12px;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-sub); margin-bottom: 6px;">
                    Drag and drop your template CSV here, or <span style="color: var(--red-lt); cursor: pointer;" onclick="document.getElementById('csv_file').click()">browse</span>
                </div>
                <div style="font-size: 0.76rem; color: var(--text-dim);" id="file-name-display">Only CSV format is supported. Max file size: 4MB.</div>
            </div>

            <div class="form-actions" style="margin-top: 18px;">
                <a href="{{ route('client.orders.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" id="submitBtn" disabled>Upload and Parse</button>
            </div>
        </form>
    </div>

    {{-- Reference: Cities & Areas --}}
    <div class="form-section guidelines-panel">
        <div class="form-section-title" style="color: var(--text-sub);">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Cities &amp; Areas Reference — use these IDs in <code>city_id</code> and <code>area_id</code>
        </div>
        <div style="max-height: 220px; overflow-y: auto; border-radius: 8px; border: 1px solid var(--bdr);">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                <thead>
                    <tr style="position: sticky; top: 0; background: var(--card);">
                        <th style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr); width: 80px;">City ID</th>
                        <th style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr);">City Name</th>
                        <th style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr); width: 90px;">Area ID</th>
                        <th style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr);">Area Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cities as $city)
                        @if($city->areas->isEmpty())
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td style="padding: 7px 14px; font-weight: 700; color: var(--red-lt);">{{ $city->id }}</td>
                                <td style="padding: 7px 14px; color: var(--text); font-weight: 600;">{{ $city->name }}</td>
                                <td colspan="2" style="padding: 7px 14px; color: var(--text-dim); font-style: italic;">No areas</td>
                            </tr>
                        @else
                            @foreach($city->areas as $loop2 => $area)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                    @if($loop2 === 0)
                                        <td style="padding: 7px 14px; font-weight: 700; color: var(--red-lt); vertical-align: top;" rowspan="{{ $city->areas->count() }}">{{ $city->id }}</td>
                                        <td style="padding: 7px 14px; color: var(--text); font-weight: 600; vertical-align: top;" rowspan="{{ $city->areas->count() }}">{{ $city->name }}</td>
                                    @endif
                                    <td style="padding: 7px 14px; color: var(--text-dim); padding-left: 20px;">{{ $area->id }}</td>
                                    <td style="padding: 7px 14px; color: var(--text);">{{ $area->name }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    @if($cities->isEmpty())
                        <tr><td colspan="4" style="padding: 14px; color: var(--text-dim); text-align: center;">No active cities found.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Template Formatting Guidelines --}}
    <div class="form-section guidelines-panel">
        <div class="form-section-title" style="color: var(--text-sub);">
            Template Formatting Guidelines
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span style="font-weight: 700; color: var(--text); width: 220px;">payment_type</span>
                <strong>Must be either <code>cod</code> or <code>prepaid</code> (lowercase).</strong>
            </div>
            <div class="info-row">
                <span style="font-weight: 700; color: var(--text); width: 220px;">delivery_on_customer</span>
                <strong>Use <code>true</code> if the customer pays the delivery fee, or <code>false</code> if it is charged to you.</strong>
            </div>
            <div class="info-row">
                <span style="font-weight: 700; color: var(--text); width: 220px;">order_price</span>
                <strong>The amount driver should collect from customer for COD goods. Leave as <code>0.00</code> for prepaid orders.</strong>
            </div>
            <div class="info-row">
                <span style="font-weight: 700; color: var(--text); width: 220px;">city_id &amp; area_id</span>
                <strong>Must match database IDs. Make sure the <code>area_id</code> belongs to the specified <code>city_id</code>, otherwise the import will show errors.</strong>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    const fileInput = document.getElementById('csv_file');
    const submitBtn = document.getElementById('submitBtn');
    const dropzone  = document.getElementById('dropzone');
    const fileNameDisplay = document.getElementById('file-name-display');

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            fileNameDisplay.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            fileNameDisplay.style.color = '#3b82f6';
            submitBtn.disabled = false;
            dropzone.style.borderColor = 'rgba(59, 130, 246, 0.4)';
        } else {
            fileNameDisplay.textContent = 'Only CSV format is supported. Max file size: 4MB.';
            fileNameDisplay.style.color = 'var(--text-dim)';
            submitBtn.disabled = true;
            dropzone.style.borderColor = 'var(--bdr)';
        }
    });

    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.style.borderColor = 'var(--red-lt)';
        this.style.background = 'rgba(220, 38, 38, 0.02)';
    });

    dropzone.addEventListener('dragleave', function () {
        this.style.borderColor = 'var(--bdr)';
        this.style.background = 'rgba(255, 255, 255, 0.01)';
    });

    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.style.borderColor = 'var(--bdr)';
        this.style.background = 'rgba(255, 255, 255, 0.01)';
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
