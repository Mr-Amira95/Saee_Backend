<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsAppTemplate;

class WhatsAppTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'event' => 'order_created',
                'template_body' => "Hello {customer_name}, your order #{order_number} has been created and assigned to {driver_name} (Phone: {driver_phone}). Please share your location here: {location_link}",
            ],
            [
                'event' => 'order_delivered',
                'template_body' => "Hello {customer_name}, your order #{order_number} has been delivered successfully by {driver_name}! Please rate our service and share your feedback here: {location_link}",
            ],
            [
                'event' => 'order_rejected',
                'template_body' => "Hello {customer_name}, your order #{order_number} could not be delivered. Reason: {rejection_reason}. Please review and update your location/details here: {location_link}",
            ],
        ];

        foreach ($templates as $tpl) {
            WhatsAppTemplate::updateOrCreate(
                ['event' => $tpl['event']],
                ['template_body' => $tpl['template_body']]
            );
        }
    }
}
