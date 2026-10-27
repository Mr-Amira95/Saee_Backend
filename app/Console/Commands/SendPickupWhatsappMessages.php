<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsappMessageJob;
use App\Models\Order;
use Illuminate\Console\Command;

class SendPickupWhatsappMessages extends Command
{
    protected $signature   = 'whatsapp:send-pickup-messages';
    protected $description = 'Send the "assign_order" WhatsApp message to receivers of picked-up orders that have not been notified yet';

    public function handle(): int
    {
        $sent = 0;

        Order::query()
            ->where('status', 'picked_up')
            ->whereNull('pickup_whatsapp_sent_at')
            ->with('receiver')
            ->chunkById(200, function ($orders) use (&$sent) {
                foreach ($orders as $order) {
                    $receiver = $order->receiver;

                    SendWhatsappMessageJob::dispatch(
                        'assign_order',
                        $receiver?->receiver_phone ?? '',
                        [
                            'cutomer_name' => $receiver?->receiver_name ?? '',
                            'order_number' => $order->order_number ?? '',
                        ],
                        $order->id,
                        [$order->order_number ?? ''],
                        'ar',
                    )->onQueue(config('whatsapp.queue', 'default'));
                }

                // Query-builder update so the realtime broadcast observers don't fire per order
                Order::whereKey($orders->modelKeys())->update(['pickup_whatsapp_sent_at' => now()]);
                $sent += $orders->count();
            });

        $this->info("Queued {$sent} pickup WhatsApp message(s).");

        return self::SUCCESS;
    }
}
