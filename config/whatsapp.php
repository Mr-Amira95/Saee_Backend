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

    /*
     * Message bodies keyed by event. {{placeholder}} tokens are replaced with
     * the variables passed to WhatsAppService::sendTemplate().
     */
    'templates' => [
        'order_created' => "Hello {{customer_name}}, your order #{{order_number}} has been created and assigned to {{driver_name}} (Phone: {{driver_phone}}). Please share your location here: {{location_link}}",
        'order_picked_up' => "Hello {{customer_name}},\n\nYour order #{{order_number}} has been picked up by our driver {{driver_name}} 🚚\n\nPlease share your current location so we can deliver your package efficiently!\n\nThank you for choosing SAEE.",
        'assign_order' => "مرحباً {{customer_name}} 👋\n\nطلبك رقم {{order_number}} مع السائق وهو الآن قيد التوصيل 🚚\nسيتم توصيل طلبك اليوم.\n\nيمكنك أيضاً متابعة حالة طلبك من خلال الضغط على زر متابعة الطلب\n\nيرجى مشاركة موقع التوصيل في هذه المحادثة 📍",
        'delivered_order' => "مرحباً {{customer_name}} 👋\n\nتم توصيل طلبك رقم {{order_number}} بنجاح 🎉\n\nنأمل أن تكون تجربتك مرضية 🙏\n\nنود معرفة رأيك، فملاحظاتك تساعدنا على تحسين خدماتنا وتقديم تجربة أفضل لك",
        'order_rejected' => "Hello {{customer_name}}, your order #{{order_number}} could not be delivered. Reason: {{rejection_reason}}. Please review and update your details here: {{location_link}}",
        'user_invitation' => "Welcome to Sa'ee Logistics, {{name}}! 👋\n\nYour account has been created. Please set your password using the link below:\n\n{{link}}\n\nThis link is valid for 24 hours. If you did not expect this message, please contact support.",
        'password_reset_otp' => "Your Sa'ee password reset code is: *{{code}}*\n\nThis code expires in 5 minutes. Do not share it with anyone.",
    ],

];
