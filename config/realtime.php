<?php

/*
|--------------------------------------------------------------------------
| Realtime dashboard pages
|--------------------------------------------------------------------------
|
| Admin and client pages refresh themselves in place (public/js/realtime.js)
| when data they show changes. Change "types" are the snake_case model names
| broadcast by App\Realtime\RealtimeHub (order, financial_ledger_entry, ...).
|
*/

$orderTypes = ['order', 'order_tracking_log', 'order_payment', 'order_receiver', 'driver_rating', 'handover_request'];

$financeTypes = ['financial_ledger_entry', 'invoice', 'client_delivery_invoice', 'driver_payment', 'handover_request', 'expense', 'order', 'order_payment'];

return [

    // Pages that never auto-refresh: forms, editors, and pages with their own live feed.
    'disabled' => [
        '*.create', '*.edit', '*.import*', '*.print*', '*.password.*',
        'admin.cms.*', 'admin.settings.*', 'admin.support.*', 'admin.notifications.*',
        'admin.cities.*', 'admin.rejection-reasons.*',
        'admin.drivers.live-map', 'admin.drivers.location-history', 'admin.drivers.bank-details',
        'client.account.*', 'client.ai-chat.*', 'client.support.*', 'client.notifications.*',
    ],

    // Route name pattern => change types that refresh it (first match wins).
    // Enabled pages that are not listed here refresh on any change.
    'pages' => [
        'admin.dashboard'          => ['*'],
        'admin.reports.*'          => ['*'],
        'admin.orders.*'           => [...$orderTypes, 'financial_ledger_entry'],
        'admin.financials.*'       => $financeTypes,
        'admin.billing.*'          => ['client_delivery_invoice', 'financial_ledger_entry', 'order', 'order_payment'],
        'admin.expenses.*'         => ['expense'],
        'admin.payroll.*'          => ['driver_payment', 'driver_profile', 'financial_ledger_entry', 'attendance'],
        'admin.attendance.*'       => ['attendance'],
        'admin.clients.*'          => ['client_profile', 'client_employee', 'client_attachment', 'client_bank_detail', 'client_delivery_price', 'user', 'order', 'financial_ledger_entry', 'invoice', 'client_delivery_invoice'],
        'admin.drivers.*'          => ['driver_profile', 'driver_bank_detail', 'driver_payment', 'user', 'attendance', 'financial_ledger_entry', ...$orderTypes],
        'admin.admins.*'           => ['user'],
        'admin.ai-conversations.*' => ['chat_session', 'chat_message'],
        'admin.whatsapp-logs.*'    => ['whats_app_log'],

        'client.dashboard'         => ['*'],
        'client.reports.*'         => ['*'],
        'client.track'             => $orderTypes,
        'client.orders.*'          => [...$orderTypes, 'financial_ledger_entry'],
        'client.financials.*'      => ['invoice', 'financial_ledger_entry', 'order', 'order_payment'],
        'client.billing.*'         => ['client_delivery_invoice', 'financial_ledger_entry', 'order'],
        'client.users.*'           => ['client_employee'],
    ],

];
