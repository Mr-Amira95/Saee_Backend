@extends('admin.layouts.app')

@section('title', 'AI Image Orders Import')
@section('page-title', 'AI Image Orders Import')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">Orders</a>
    <span class="sep">/</span>
    <span class="current">AI Image Import</span>
@endsection

@section('head')
    <style>
        .form-wrap {
            max-width: 100% !important;
        }
        .form-section {
            background: var(--card);
            border: 1px solid var(--bdr);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 18px;
            backdrop-filter: blur(8px);
            position: relative;
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
        
        /* Spinner loading overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(12, 18, 48, 0.85);
            z-index: 9999;
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: var(--text);
            animation: fadeIn 0.3s ease-out;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(220, 38, 38, 0.15);
            border-top-color: var(--red-lt);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left" style="display:flex;align-items:center;gap:10px;">
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
            <div>
                <h1>AI Image Orders Import</h1>
                <p>Upload a screenshot, photo, or scan of an orders sheet. OpenAI Vision will extract it into editable rows.</p>
            </div>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.orders.import') }}" class="btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m12-9a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Use CSV Import instead
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="flash flash-err" style="border-radius: 14px; margin-bottom: 20px;">{{ session('error') }}</div>
    @endif

    <div class="form-wrap">
        <div class="form-section">
            <div class="form-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Upload Image
            </div>
            
            <form action="{{ route('admin.orders.import-image.upload') }}" method="POST" enctype="multipart/form-data" id="image-upload-form">
                @csrf
                
                <div class="form-group" style="padding: 40px 30px; border: 2px dashed var(--bdr); border-radius: 12px; text-align: center; background: rgba(255,255,255,0.01); transition: border-color 0.2s; cursor: pointer;" id="dropzone">
                    <input type="file" name="image" id="image" style="display: none;" accept="image/jpeg,image/jpg,image/png,image/webp" required>
                    <div style="font-size: 3rem; color: var(--text-dim); margin-bottom: 12px;" id="dropzone-icon">
                        <svg width="50" height="50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div style="font-size: 1rem; font-weight: 600; color: var(--text-sub); margin-bottom: 6px;" id="dropzone-text">
                        Drag and drop your image sheet here, or <span style="color: var(--red-lt);">browse</span>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-dim);" id="file-name-display">Supported: JPG, JPEG, PNG, WEBP. Max size: 10MB.</div>
                    
                    {{-- Preview Container --}}
                    <div id="image-preview-container" style="display: none; margin-top: 20px; justify-content: center;">
                        <img id="image-preview" src="#" alt="Upload Preview" style="max-height: 180px; border-radius: 8px; border: 1px solid var(--bdr); box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary" id="submitBtn" disabled>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="margin-right: 5px; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l-1.813-5.096L2.091 14 7.18 12.187 8 7l1.813 5.187L15 14l-5.187 1.904z"/></svg>
                        Parse with OpenAI
                    </button>
                </div>
            </form>
        </div>

        {{-- How it works reference --}}
        <div class="form-section" style="background: rgba(12, 18, 48, 0.5);">
            <div class="form-section-title" style="color: var(--text-sub);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                AI Vision parsing guidelines
            </div>
            <div style="font-size: 0.85rem; color: var(--text-sub); line-height: 1.6;">
                <p style="margin-bottom: 12px;">OpenAI vision model scans the uploaded image and attempts to capture order columns automatically. The following columns can be read:</p>
                <ul style="padding-left: 20px; list-style-type: disc; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px;">
                    <li><strong>Receiver Name</strong> &amp; <strong>Phone</strong></li>
                    <li><strong>City</strong> (Amman, Irbid, Zarqa, etc.)</li>
                    <li><strong>Area / Neighborhood</strong></li>
                    <li><strong>Full Address details</strong></li>
                    <li><strong>Price / Collect amount</strong></li>
                    <li><strong>Item details / Descriptions</strong></li>
                    <li><strong>Special notes</strong></li>
                </ul>
                <p style="margin-top: 15px; font-style: italic;" class="text-dim">💡 Any details that cannot be resolved automatically (like matching client profiles, cities, or area IDs) will be highlighted in the review screen for manual selection.</p>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
        <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 8px;">Analyzing Image with AI</h2>
        <p style="color: var(--text-dim); text-align: center; max-width: 400px; font-size: 0.88rem; line-height: 1.5;">
            We are sending the image to OpenAI GPT-4o to scan and extract order data. This process usually takes 10 to 20 seconds. Please do not refresh or close this tab.
        </p>
    </div>
@endsection

@section('scripts')
    <script>
        const fileInput = document.getElementById('image');
        const submitBtn = document.getElementById('submitBtn');
        const dropzone = document.getElementById('dropzone');
        const fileNameDisplay = document.getElementById('file-name-display');
        const form = document.getElementById('image-upload-form');
        const loadingOverlay = document.getElementById('loading-overlay');
        const imagePreview = document.getElementById('image-preview');
        const previewContainer = document.getElementById('image-preview-container');

        // Click dropzone to trigger input file selection
        dropzone.addEventListener('click', function(e) {
            if (e.target !== fileInput && !previewContainer.contains(e.target)) {
                fileInput.click();
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                fileNameDisplay.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileNameDisplay.style.color = '#3b82f6';
                submitBtn.disabled = false;
                dropzone.style.borderColor = 'rgba(59, 130, 246, 0.4)';

                // Render image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            } else {
                fileNameDisplay.textContent = 'Supported: JPG, JPEG, PNG, WEBP. Max size: 10MB.';
                fileNameDisplay.style.color = 'var(--text-dim)';
                submitBtn.disabled = true;
                dropzone.style.borderColor = 'var(--bdr)';
                previewContainer.style.display = 'none';
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

        // Show loading screen when submitting form
        form.addEventListener('submit', function() {
            loadingOverlay.style.display = 'flex';
        });
    </script>
@endsection
