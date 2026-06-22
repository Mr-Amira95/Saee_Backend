<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsappWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle the Meta webhook verification challenge (GET).
     *
     * Meta sends: hub.mode=subscribe, hub.verify_token, hub.challenge
     * We must respond with hub.challenge as plain text when the token matches.
     */
    public function verify(Request $request): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('whatsapp.webhook_token')) {
            Log::info('WhatsApp webhook endpoint verified.');
            return response($challenge ?? '', 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed — invalid token.', [
            'mode'  => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Receive incoming WhatsApp messages (POST).
     *
     * The controller only validates, logs, and dispatches — all business logic
     * runs in the background via ProcessWhatsappWebhookJob so the provider
     * receives a fast 200 response.
     */
    public function receive(Request $request): JsonResponse
    {
        Log::info('WhatsApp webhook received.', ['payload' => $request->all()]);

        ProcessWhatsappWebhookJob::dispatch($request->all())
            ->onQueue(config('whatsapp.queue', 'default'));

        return response()->json(['success' => true], 200);
    }
}
