<?php

return [

    /*
     * Meta WhatsApp Cloud API base URL.
     * Full endpoint: {api_url}/{sender}/messages
     */
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v20.0'),

    /*
     * Bearer token for authenticating with the provider.
     */
    'api_token' => env('WHATSAPP_API_TOKEN'),

    /*
     * Phone Number ID (Meta) used as the sender identifier.
     */
    'sender' => env('WHATSAPP_SENDER'),

    /*
     * Provider name. Supported: 'meta'.
     * Extend WhatsAppService::sendRawMessage() to support others.
     */
    'provider' => env('WHATSAPP_PROVIDER', 'meta'),

    /*
     * Shared secret for webhook endpoint verification (GET hub.verify_token).
     */
    'webhook_token' => env('WHATSAPP_WEBHOOK_TOKEN'),

    /*
     * Laravel queue name for WhatsApp jobs.
     */
    'queue' => env('WHATSAPP_QUEUE', 'default'),

];
