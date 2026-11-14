{{-- Delivery status of an outbound WhatsApp log: ✓ sent, ✓✓ delivered, blue ✓✓ read, ! not sent. --}}
@if($log->status === 'failed')
    <svg class="wa-tick failed" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><title>{{ __('Not sent') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
@elseif(in_array($log->status, ['delivered', 'read'], true))
    <svg class="wa-tick {{ $log->status }}" width="17" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 28 24"><title>{{ $log->status === 'read' ? __('Read') : __('Delivered') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l4 4L17 7M12 16l1 1L23 7"/></svg>
@else
    <svg class="wa-tick" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><title>{{ __('Sent') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
@endif
