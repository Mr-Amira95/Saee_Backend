@extends('admin.layouts.app')

@section('title', 'Bulk Import Orders')
@section('page-title', 'Bulk Import Orders')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">Orders</a>
    <span class="sep">/</span>
    <span class="current">Bulk Import</span>
@endsection

@section('head')
    <style>
        .form-wrap {
            max-width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Bulk Import Orders</h1>
            <p>Upload a CSV file containing multiple orders to import them instantly.</p>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.orders.import.template') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download CSV Template
            </a>
        </div>
    </div>

    <div class="form-wrap">
        <div class="form-section">
            <div class="form-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Upload CSV File
            </div>
            
            <form action="{{ route('admin.orders.import.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group" style="padding: 30px; border: 2px dashed var(--bdr); border-radius: 12px; text-align: center; background: rgba(255,255,255,0.01); transition: border-color 0.2s;" id="dropzone">
                    <input type="file" name="csv_file" id="csv_file" style="display: none;" accept=".csv" required>
                    <div style="font-size: 2.2rem; color: var(--text-dim); margin-bottom: 12px;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-sub); margin-bottom: 6px;">
                        Drag and drop your template CSV here, or <span style="color: var(--red-lt); cursor: pointer;" onclick="document.getElementById('csv_file').click()">browse</span>
                    </div>
                    <div style="font-size: 0.76rem; color: var(--text-dim);" id="file-name-display">Only CSV format is supported. Max file size: 4MB.</div>
                </div>

                <div class="form-actions" style="margin-top: 18px;">
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary" id="submitBtn" disabled>Upload and Parse</button>
                </div>
            </form>
        </div>

        {{-- Help / Instructions Card --}}
        <div class="form-section" style="background: rgba(12, 18, 48, 0.5);">
            <div class="form-section-title" style="color: var(--text-sub);">
                Template Formatting Guidelines
            </div>
            <div class="info-rows" style="gap: 12px;">
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">client_id</span>
                    <strong>Must match an existing Client's ID in the system (e.g. <code>1</code>). Check the Clients page to find their IDs.</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">payment_type</span>
                    <strong>Must be either <code>cod</code> or <code>prepaid</code> (lowercase).</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">delivery_on_customer</span>
                    <strong>Use <code>true</code> if the customer pays the delivery fee, or <code>false</code> if it is charged to the client.</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">order_price</span>
                    <strong>The amount driver should collect from customer for COD goods. Leave as <code>0.00</code> for prepaid orders.</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">city_id &amp; area_id</span>
                    <strong>Must match database IDs. Make sure the <code>area_id</code> belongs to the specified <code>city_id</code>, otherwise the import will show errors.</strong>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const fileInput = document.getElementById('csv_file');
        const submitBtn = document.getElementById('submitBtn');
        const dropzone = document.getElementById('dropzone');
        const fileNameDisplay = document.getElementById('file-name-display');

        fileInput.addEventListener('change', function() {
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

        // Simple drag & drop support
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--red-lt)';
            this.style.background = 'rgba(220, 38, 38, 0.02)';
        });

        dropzone.addEventListener('dragleave', function() {
            this.style.borderColor = 'var(--bdr)';
            this.style.background = 'rgba(255, 255, 255, 0.01)';
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--bdr)';
            this.style.background = 'rgba(255, 255, 255, 0.01)';
            
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection
