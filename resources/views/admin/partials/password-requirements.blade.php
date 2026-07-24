@php
    $reqId = $id ?? 'pwReqs';
@endphp
<div class="pw-reqs" id="{{ $reqId }}">
    <div class="pw-req-item" data-req="len"><span class="pw-req-icon">✓</span> {{ __('At least 8 characters') }}</div>
    <div class="pw-req-item" data-req="upper"><span class="pw-req-icon">✓</span> {{ __('At least one uppercase letter') }}</div>
    <div class="pw-req-item" data-req="lower"><span class="pw-req-icon">✓</span> {{ __('At least one lowercase letter') }}</div>
    <div class="pw-req-item" data-req="special"><span class="pw-req-icon">✓</span> {{ __('At least one special character') }}</div>
</div>
