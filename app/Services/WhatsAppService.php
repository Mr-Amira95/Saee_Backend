<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppLog;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Compile template and simulate sending a WhatsApp message.
     */
    public function sendNotification(Order $order, string $event): ?WhatsAppLog
    {
        try {
            $templateModel = WhatsAppTemplate::where('event', $event)->first();
            $templateBody = $templateModel ? $templateModel->template_body : $this->getDefaultTemplate($event);

            $message = $this->compileMessage($order, $templateBody, $event);
            $phone = $order->receiver_phone;

            // Log simulation in Laravel system logs
            Log::info("WhatsApp Send Simulation [Event: {$event}] to {$phone}: \n{$message}");

            // Save to database audit logs
            return WhatsAppLog::create([
                'order_id' => $order->id,
                'phone'    => $phone,
                'message'  => $message,
                'status'   => 'simulated',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification for Order #{$order->order_number}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Compile template by replacing placeholders with actual order values.
     */
    public function compileMessage(Order $order, string $template, string $event): string
    {
        $driverName = $order->driver ? $order->driver->name : 'our driver';
        $driverPhone = $order->driver ? $order->driver->phone : 'our office';
        
        // Generate public location sharing link
        // We use order_number to look up the order on the public side
        $locationLink = route('public.share-location', ['order_number' => $order->order_number]);

        $rejectionReason = 'Not specified';
        if ($event === 'order_rejected') {
            $rejectionReason = $order->rejectionReason 
                ? $order->rejectionReason->reason 
                : ($order->notes ?? 'Not specified');
        }

        $placeholders = [
            '{customer_name}'     => $order->receiver_name,
            '{order_number}'      => $order->order_number,
            '{driver_name}'       => $driverName,
            '{driver_phone}'      => $driverPhone,
            '{location_link}'     => $locationLink,
            '{rejection_reason}'  => $rejectionReason,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    /**
     * Get fallbacks for default templates if none are in the DB.
     */
    private function getDefaultTemplate(string $event): string
    {
        return match ($event) {
            'order_created' => "Hello {customer_name}, your order #{order_number} has been created and assigned to {driver_name} (Phone: {driver_phone}). Please share your location here: {location_link}",
            'order_delivered' => "Hello {customer_name}, your order #{order_number} has been delivered successfully by {driver_name}! Please rate our service and share your feedback here: {location_link}",
            'order_rejected' => "Hello {customer_name}, your order #{order_number} could not be delivered. Reason: {rejection_reason}. Please review and update your location/details here: {location_link}",
            default => "Hello {customer_name}, order #{order_number} status updated.",
        };
    }
}
