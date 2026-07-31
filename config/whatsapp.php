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
        'order_picked_up' => "Hello {{customer_name}},\n\nYour order #{{order_number}} has been picked up by our driver {{driver_name}} 🚚\n\nPlease share your current location so we can deliver your package efficiently!\n\nThank you for choosing SAEE.",
        'assign_order' => "مرحباً {{cutomer_name}} 👋\n\nطلبك رقم {{order_number}} مع السائق وهو الآن قيد التوصيل 🚚\nسيتم توصيل طلبك اليوم.\n\nيمكنك أيضاً متابعة حالة طلبك من خلال الضغط على زر متابعة الطلب\n\nيرجى مشاركة موقع التوصيل في هذه المحادثة 📍",
        'delivered_order' => "مرحباً {{cutomer_name}} 👋\n\nتم توصيل طلبك رقم {{order_number}} بنجاح 🎉\n\nنأمل أن تكون تجربتك مرضية 🙏\n\nنود معرفة رأيك، فملاحظاتك تساعدنا على تحسين خدماتنا وتقديم تجربة أفضل لك",
        'order_rejected' => "Hello {{customer_name}}, your order #{{order_number}} could not be delivered. Reason: {{rejection_reason}}. Please review and update your details here: {{location_link}}",
        'user_invitation' => "مرحباً {{user_name}} 👋\n\nتم إنشاء حسابك بنجاح في ساعي للخدمات اللوجستية. يرجى تعيين كلمة المرور الخاصة بك من خلال الزر أدناه\n\nهذا الرابط صالح لمدة 24 ساعة",
        'otp' => "{{code}} هو كود التحقق الخاص بك. للحفاظ على أمانك، لا تشارك هذا الكود مع أي شخص.",
    ],

    /*
     * Events whose Meta template body was approved with named parameters
     * (e.g. {{user_name}}) rather than positional ({{1}}). See
     * WhatsAppService::sendTemplate() — this changes how body parameters
     * are shaped in the API request.
     */
    'named_templates' => [
        'user_invitation',
        'assign_order',
        'delivered_order',
    ],

];
