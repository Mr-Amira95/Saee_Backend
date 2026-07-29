@extends('admin.layouts.app')

@section('title', __('Bulk Import Orders'))
@section('page-title', __('Bulk Import Orders'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">{{ __('Orders') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ __('Bulk Import') }}</span>
@endsection

@section('head')
    <style>
        .form-wrap {
            max-width: 100% !important;
        }
        .guidelines-panel {
            background: rgba(12, 18, 48, 0.5) !important;
        }
        html.light-theme .guidelines-panel {
            background: rgba(15, 23, 42, 0.03) !important;
            border-color: rgba(15, 23, 42, 0.08) !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ __('Bulk Import Orders') }}</h1>
            <p>{{ __('Upload a CSV file containing multiple orders to import them instantly.') }}</p>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.orders.import-image') }}" class="btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ __('AI Image Import') }}
            </a>
            <a href="{{ route('admin.orders.import.template') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                {{ __('Download CSV Template') }}
            </a>
        </div>
    </div>

    <div class="form-wrap">
        <div class="form-section">
            <div class="form-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                {{ __('Upload CSV File') }}
            </div>
            
            <form action="{{ route('admin.orders.import.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group" style="padding: 30px; border: 2px dashed var(--bdr); border-radius: 12px; text-align: center; background: rgba(255,255,255,0.01); transition: border-color 0.2s;" id="dropzone">
                    <input type="file" name="csv_file" id="csv_file" style="display: none;" accept=".csv" required>
                    <div style="font-size: 2.2rem; color: var(--text-dim); margin-bottom: 12px;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display: inline-block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-sub); margin-bottom: 6px;">
                        {{ __('Drag and drop your template CSV here, or') }} <span style="color: var(--red-lt); cursor: pointer;" onclick="document.getElementById('csv_file').click()">{{ __('browse') }}</span>
                    </div>
                    <div style="font-size: 0.76rem; color: var(--text-dim);" id="file-name-display">{{ __('Only CSV format is supported. Max file size: 10MB.') }}</div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #ef4444; margin-top: 8px; display: none;" id="file-error-display"></div>
                </div>

                <div class="form-actions" style="margin-top: 18px;">
                    <a href="{{ route('admin.orders.import') }}" class="btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn-primary" id="submitBtn" disabled>{{ __('Upload and Parse') }}</button>
                </div>
            </form>
        </div>

        {{-- Reference: Clients --}}
        <div class="form-section guidelines-panel">
            <div class="form-section-title" style="color: var(--text-sub);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
                {!! __('Client Reference — use these IDs in the <code>client_id</code> column') !!}
            </div>
            <div style="max-height: 220px; overflow-y: auto; border-radius: 8px; border: 1px solid var(--bdr);">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                    <thead>
                        <tr style="position: sticky; top: 0; background: var(--card-hd, rgba(12,18,48,0.95));">
                            <th style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr); width: 80px;">{{ __('ID') }}</th>
                            <th style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr);">{{ __('Company Name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td style="padding: 7px 14px; font-weight: 700; color: var(--red-lt);">{{ $client->id }}</td>
                                <td style="padding: 7px 14px; color: var(--text);">{{ $client->company_name }}</td>
                            </tr>
                        @endforeach
                        @if($clients->isEmpty())
                            <tr><td colspan="2" style="padding: 14px; color: var(--text-dim); text-align: center;">{{ __('No active clients found.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Reference: Cities & Areas --}}
        <div class="form-section guidelines-panel">
            <div class="form-section-title" style="color: var(--text-sub);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {!! __('Cities & Areas Reference — use these IDs in <code>city_id</code> and <code>area_id</code>') !!}
            </div>
            <div style="max-height: 220px; overflow-y: auto; border-radius: 8px; border: 1px solid var(--bdr);">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;" data-no-export="true">
                    <thead>
                        <tr style="position: sticky; top: 0; background: var(--card-hd, rgba(12,18,48,0.95));">
                            <th class="no-sort" style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr); width: 80px;">{{ __('City ID') }}</th>
                            <th class="no-sort" style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr);">{{ __('City Name') }}</th>
                            <th class="no-sort" style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr); width: 90px;">{{ __('Area ID') }}</th>
                            <th class="no-sort" style="padding: 8px 14px; text-align: left; color: var(--text-dim); font-weight: 600; border-bottom: 1px solid var(--bdr);">{{ __('Area Name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cities as $city)
                            @if($city->areas->isEmpty())
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                    <td style="padding: 7px 14px; font-weight: 700; color: var(--red-lt);">{{ $city->id }}</td>
                                    <td style="padding: 7px 14px; color: var(--text); font-weight: 600;">{{ app()->getLocale() === 'ar' ? ($city->name_ar ?: $city->name) : $city->name }}</td>
                                    <td colspan="2" style="padding: 7px 14px; color: var(--text-dim); font-style: italic;">{{ __('No areas') }}</td>
                                </tr>
                            @else
                                @foreach($city->areas as $loop2 => $area)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                        @if($loop2 === 0)
                                            <td style="padding: 7px 14px; font-weight: 700; color: var(--red-lt); vertical-align: top;" rowspan="{{ $city->areas->count() }}">{{ $city->id }}</td>
                                            <td style="padding: 7px 14px; color: var(--text); font-weight: 600; vertical-align: top;" rowspan="{{ $city->areas->count() }}">{{ app()->getLocale() === 'ar' ? ($city->name_ar ?: $city->name) : $city->name }}</td>
                                        @endif
                                        <td style="padding: 7px 14px; color: var(--text-dim); padding-left: 20px;">{{ $area->id }}</td>
                                        <td style="padding: 7px 14px; color: var(--text);">{{ app()->getLocale() === 'ar' ? ($area->name_ar ?: $area->name) : $area->name }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                        @if($cities->isEmpty())
                            <tr><td colspan="4" style="padding: 14px; color: var(--text-dim); text-align: center;">{{ __('No active cities found.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Help / Instructions Card --}}
        <div class="form-section guidelines-panel">
            <div class="form-section-title" style="color: var(--text-sub);">
                {{ __('Template Formatting Guidelines') }}
            </div>
            <div class="info-rows" style="gap: 12px;">
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">client_id</span>
                    <strong>{!! __("Must match an existing Client's ID in the system (e.g. <code>1</code>). Check the Clients page to find their IDs.") !!}</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">payment_type</span>
                    <strong>{!! __('Use <code>cod</code> / <code>عند التسليم</code> or <code>prepaid</code> / <code>مدفوع</code>.') !!}</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">delivery_on_customer</span>
                    <strong>{!! __('Use <code>Yes</code> / <code>نعم</code> if the customer pays the delivery fee, or <code>No</code> / <code>لا</code> if it is charged to the client.') !!}</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">order_price</span>
                    <strong>{!! __('The amount driver should collect from customer for COD goods. Leave as <code>0.00</code> for prepaid orders.') !!}</strong>
                </div>
                <div class="info-row">
                    <span style="font-weight: 700; color: var(--text); width: 180px;">city_id &amp; area_id</span>
                    <strong>{!! __('Must match database IDs. Make sure the <code>area_id</code> belongs to the specified <code>city_id</code>, otherwise the import will show errors.') !!}</strong>
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
        const fileErrorDisplay = document.getElementById('file-error-display');

        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
        const DEFAULT_HINT = @json(__('Only CSV format is supported. Max file size: 10MB.'));
        const SELECTED_LABEL = @json(__('Selected:'));

        function showFileError(message) {
            fileErrorDisplay.textContent = message;
            fileErrorDisplay.style.display = 'block';
            fileNameDisplay.textContent = DEFAULT_HINT;
            fileNameDisplay.style.color = 'var(--text-dim)';
            submitBtn.disabled = true;
            dropzone.style.borderColor = '#ef4444';
            fileInput.value = '';
        }

        function clearFileError() {
            fileErrorDisplay.textContent = '';
            fileErrorDisplay.style.display = 'none';
        }

        fileInput.addEventListener('change', function() {
            clearFileError();

            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const isCsv = /\.csv$/i.test(file.name);

                if (!isCsv) {
                    showFileError(@json(__('Unsupported file format. Only CSV files are accepted.')));
                    return;
                }

                if (file.size > MAX_FILE_SIZE) {
                    showFileError(@json(__('File size exceeds the 10MB limit. Please upload a smaller file.')));
                    return;
                }

                fileNameDisplay.textContent = `${SELECTED_LABEL} ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                fileNameDisplay.style.color = '#3b82f6';
                submitBtn.disabled = false;
                dropzone.style.borderColor = 'rgba(59, 130, 246, 0.4)';
            } else {
                fileNameDisplay.textContent = DEFAULT_HINT;
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
