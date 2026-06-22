<?php

namespace Database\Seeders;

use App\Models\WhatsAppTemplate;
use Illuminate\Database\Seeder;

class WhatsAppTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event'         => 'order_created',
                'template_body' => "Hello {{customer_name}}, your order #{{order_number}} has been created and assigned to {{driver_name}} (Phone: {{driver_phone}}). Please share your location here: {{location_link}}",
            ],
            [
                'event'         => 'order_picked_up',
                'template_body' => "Hello {{customer_name}},\n\nYour order #{{order_number}} has been picked up by our driver {{driver_name}} 🚚\n\nPlease share your current location so we can deliver your package efficiently!\n\nThank you for choosing SAEE.",
            ],
            [
                'event'         => 'order_delivered',
                'template_body' => "Hello {{customer_name}}, your order #{{order_number}} has been delivered successfully by {{driver_name}}! Thank you for choosing SAEE.",
            ],
            [
                'event'         => 'order_rejected',
                'template_body' => "Hello {{customer_name}}, your order #{{order_number}} could not be delivered. Reason: {{rejection_reason}}. Please review and update your details here: {{location_link}}",
            ],
        ];

        foreach ($templates as $tpl) {
            WhatsAppTemplate::updateOrCreate(
                ['event'         => $tpl['event']],
                ['template_body' => $tpl['template_body']],
            );
        }
    }
}
