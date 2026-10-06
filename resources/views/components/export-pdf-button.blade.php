@props(['href' => null, 'orders' => null])

{{--
    Unified "Export PDF" button used by every orders table in the admin and
    client portals. It opens the printable waybills page
    (shared/orders/print.blade.php, rendered by App\Services\WaybillExportService)
    in a new tab — edit those three files and every table picks up the change.

    Usage:
      <x-export-pdf-button :href="route('admin.orders.print-all', request()->query())" />
          → every order matching the page's filters (paginated tables)
      <x-export-pdf-button :orders="$orders" />
          → exactly the given orders (fixed lists: invoices, settlements, ...)
--}}

@php
    $icon = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M4 10V6a2 2 0 012-2h6l6 6v4"/></svg>';

    if (! $href) {
        $orders = $orders instanceof \Illuminate\Contracts\Pagination\Paginator ? $orders->items() : ($orders ?? []);
        $ids = collect($orders)
            ->map(fn ($order) => is_object($order) ? $order->getKey() : $order)
            ->filter()
            ->unique()
            ->implode(',');
        $action = request()->routeIs('client.*') ? route('client.waybills.export') : route('admin.waybills.export');
    }
@endphp

@if($href)
    <a href="{{ $href }}" target="_blank" {{ $attributes->merge(['class' => 'btn-secondary']) }}>
        {!! $icon !!}
        {{ __('Export PDF') }}
    </a>
@elseif($ids !== '')
    <button type="button"
            onclick="exportOrdersPdf(this)"
            data-action="{{ $action }}"
            data-token="{{ csrf_token() }}"
            data-ids="{{ $ids }}"
            {{ $attributes->merge(['class' => 'btn-secondary']) }}>
        {!! $icon !!}
        {{ __('Export PDF') }}
    </button>

    @once
    <script>
        function exportOrdersPdf(btn) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = btn.dataset.action;
            form.target = '_blank';
            [['_token', btn.dataset.token], ['ids', btn.dataset.ids]].forEach(function (field) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = field[0];
                input.value = field[1];
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }
    </script>
    @endonce
@endif
