<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhatsAppLog;
use App\Services\WhatsAppMediaService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class WhatsAppLogController extends Controller
{
    /**
     * Meta only accepts free-form replies within this many hours of the
     * contact's last inbound message.
     */
    private const REPLY_WINDOW_HOURS = 24;

    /**
     * WhatsApp-style inbox with no chat open.
     */
    public function index()
    {
        return view('admin.whatsapp-logs.inbox', [
            'conversations' => $this->conversations($this->allLogs()),
            'active'        => null,
        ]);
    }

    /**
     * Inbox with one phone's chat open — a single thread across every order
     * that phone has messaged about.
     */
    public function show(string $phone)
    {
        $normalized    = WhatsAppLog::normalizePhone($phone);
        $logs          = $this->allLogs();
        $conversations = $this->conversations($logs);
        $active        = $conversations->firstWhere('phone', $normalized);

        abort_if(! $active, 404);

        $thread = $logs->filter(fn (WhatsAppLog $log) => WhatsAppLog::normalizePhone($log->phone) === $normalized)->values();

        $senderIds = $thread->pluck('meta.sent_by')->filter()->unique();
        $senders   = $senderIds->isEmpty() ? collect() : User::whereIn('id', $senderIds)->pluck('name', 'id');

        return view('admin.whatsapp-logs.inbox', [
            'conversations' => $conversations,
            'active'        => $active,
            'thread'        => $thread,
            'senders'       => $senders,
            'canReply'      => $this->canReply($thread),
        ]);
    }

    /**
     * Send a reply from the inbox composer: text, an attached file (with the text
     * as its caption), or a recorded voice message.
     */
    public function send(Request $request, string $phone, WhatsAppService $whatsApp, WhatsAppMediaService $media)
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:4096', 'required_without:file'],
            'file'    => ['nullable', 'file', 'max:' . (WhatsAppMediaService::LIMITS['document']['max_mb'] * 1024)],
            'voice'   => ['nullable', 'boolean'],
        ]);

        $normalized = WhatsAppLog::normalizePhone($phone);
        $thread = $this->threadFor($normalized);

        abort_if($thread->isEmpty(), 404);

        $back = back(302, [], route('admin.whatsapp-logs.show', $normalized));

        if (! $this->canReply($thread)) {
            return $back->withInput()->with('wa_error', __('The 24-hour reply window has closed. The contact needs to message you first.'));
        }

        $text    = trim($data['message'] ?? '');
        $orderId = $thread->where('direction', 'inbound')->last()?->order_id;
        $meta    = ['sent_by' => $request->user()->id];

        if ($file = $request->file('file')) {
            $voice = $request->boolean('voice');
            [$path, $mime, $error] = $this->storeUpload($file, $voice, $media);

            if ($error) {
                return $back->withInput()->with('wa_error', $error);
            }

            $isAudio = $media->typeFor($mime) === 'audio';
            $result = $whatsApp->sendMedia($normalized, $path, $mime, $isAudio ? null : $text, $file->getClientOriginalName(), $orderId, $meta, $voice);

            // Audio can't carry a caption, so typed text goes as its own message.
            if ($result['success'] && $isAudio && $text !== '') {
                $result = $whatsApp->sendText($normalized, $text, $orderId, $meta);
            }
        } else {
            $result = $whatsApp->sendText($normalized, $text, $orderId, $meta);
        }

        if (! $result['success']) {
            return $back->withInput()->with('wa_error', __('Message could not be sent: :error', ['error' => $result['error']]));
        }

        return $back;
    }

    /**
     * Send an approved template (config whatsapp.inbox_templates) — the only way
     * to start a new chat or reopen one after the 24-hour reply window.
     */
    public function sendTemplate(Request $request, WhatsAppService $whatsApp)
    {
        $templates = config('whatsapp.inbox_templates', []);

        $request->validate([
            'phone'    => ['required', 'string', 'max:25', 'regex:/^\+?[\d\s\-()]+$/'],
            'template' => ['required', Rule::in(array_keys($templates))],
        ]);

        $template = $templates[$request->input('template')];
        $fieldCount = count($template['fields'] ?? []);

        $data = $request->validate([
            'params'   => [$fieldCount ? 'required' : 'nullable', 'array', 'size:' . $fieldCount],
            'params.*' => ['required', 'string', 'max:500'],
        ], [], ['params.*' => __('template field')]);

        $normalized = WhatsAppLog::normalizePhone($request->input('phone'));
        $back = back(302, [], route('admin.whatsapp-logs.index'));

        if (strlen($normalized) < 8 || strlen($normalized) > 15) {
            return $back->withInput()->with('wa_template_error', __('Enter a valid phone number with country code.'));
        }

        // Meta rejects template parameters containing new lines, tabs or runs of spaces.
        $params = array_map(fn ($value) => preg_replace('/\s+/', ' ', trim($value)), array_values($data['params'] ?? []));

        $result = $whatsApp->sendStructuredTemplate(
            $normalized,
            $request->input('template'),
            $template['language'] ?? 'en_US',
            $params,
            $this->threadFor($normalized)->last()?->order_id,
            meta: ['sent_by' => $request->user()->id],
        );

        if (! $result['success']) {
            return $back->withInput()->with('wa_template_error', __('Message could not be sent: :error', ['error' => $result['error']]));
        }

        return redirect()->route('admin.whatsapp-logs.show', $normalized);
    }

    /**
     * Serve a sent or received media file. Received files that couldn't be
     * downloaded when the webhook arrived are fetched from Meta now.
     */
    public function media(Request $request, WhatsAppLog $log, WhatsAppMediaService $media)
    {
        $info = $log->mediaInfo();
        abort_if(! $info, 404);

        if (! $media->exists($info['path'] ?? null) && ! empty($info['id'])) {
            try {
                $info = [...array_diff_key($info, ['download_error' => true]), ...$media->download($info['id'])];
                $log->update(['meta' => [...($log->meta ?? []), 'media' => $info]]);
            } catch (\Throwable) {
                abort(404);
            }
        }

        abort_unless($media->exists($info['path'] ?? null), 404);

        $absolute = $media->absolutePath($info['path']);
        $headers  = ['X-Content-Type-Options' => 'nosniff'];

        // Only known-safe types are shown inline; anything else (HTML, SVG, …) is a download.
        if ($request->boolean('download') || ! $media->canShowInline($info['mime'] ?? null)) {
            return response()->download($absolute, $info['filename'] ?? basename($info['path']), $headers);
        }

        return response()->file($absolute, $headers + ['Content-Type' => $info['mime']]);
    }

    /**
     * Validate and store an uploaded file for sending.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string}  [path, mime, error]
     */
    private function storeUpload(UploadedFile $file, bool $voice, WhatsAppMediaService $media): array
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';

        if ($voice) {
            // A real WhatsApp voice message needs OGG/Opus — convert when ffmpeg is available.
            if ($path = $media->toVoiceNote($file->getRealPath())) {
                return [$path, 'audio/ogg', null];
            }

            // Otherwise send the recording as-is if WhatsApp accepts its format
            // (file sniffing labels audio-only mp4/ogg as video/*).
            $mime = preg_replace('#^video/#', 'audio/', $mime);

            if ($media->typeFor($mime) !== 'audio') {
                return [null, null, __('This browser records voice in a format WhatsApp does not accept. Install ffmpeg on the server, or record from Firefox or Safari.')];
            }
        }

        if ($reason = $media->rejectionReason($media->typeFor($mime), $file->getSize())) {
            return [null, null, $reason];
        }

        return [$media->store($file->get(), $mime), $mime, null];
    }

    private function threadFor(string $normalizedPhone): Collection
    {
        return WhatsAppLog::orderBy('created_at')->orderBy('id')->get()
            ->filter(fn (WhatsAppLog $log) => WhatsAppLog::normalizePhone($log->phone) === $normalizedPhone)
            ->values();
    }

    private function allLogs(): Collection
    {
        return WhatsAppLog::with('order.receiver')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    private function canReply(Collection $thread): bool
    {
        $lastInbound = $thread->where('direction', 'inbound')->last();

        return $lastInbound !== null
            && $lastInbound->created_at->gt(now()->subHours(self::REPLY_WINDOW_HOURS));
    }

    /**
     * Roll a flat log collection up into one summary object per phone number,
     * most recent activity first.
     */
    private function conversations(Collection $logs): Collection
    {
        $users = User::whereNotNull('phone')->where('phone', '!=', '')
            ->get(['id', 'name', 'phone', 'phone_country_code', 'role'])
            ->keyBy(fn (User $user) => WhatsAppLog::normalizePhone($user->whatsappPhone()));

        return $logs
            ->groupBy(fn (WhatsAppLog $log) => WhatsAppLog::normalizePhone($log->phone))
            ->map(function (Collection $group, string $normalizedPhone) use ($users) {
                $orders = $group->pluck('order')->filter()->unique('id')->sortByDesc('created_at')->values();
                $receiver = $orders->first(fn ($o) => $o->receiver)?->receiver;
                $user = $users->get($normalizedPhone);
                $profileName = $group->pluck('meta.profile_name')->filter()->last();
                $lastLog = $group->last();
                $lastOutboundKey = $group->where('direction', 'outbound')->keys()->last();

                return (object) [
                    'phone'        => $normalizedPhone,
                    'displayPhone' => '+' . $normalizedPhone,
                    'name'         => $user?->name ?? $receiver?->receiver_name ?? $profileName,
                    'role'         => match (true) {
                        $user?->isDriver() => __('Driver'),
                        $user?->isClientMaster(), $user?->isClientEmployee() => __('Client'),
                        $user !== null => __('Admin'),
                        $receiver !== null => __('Customer'),
                        default => null,
                    },
                    'orders'       => $orders,
                    'lastLog'      => $lastLog,
                    'lastActivity' => $lastLog->created_at,
                    // Inbound messages since our last outbound one — shown like an unread badge.
                    'awaiting'     => $group->filter(fn ($log, $key) => $log->direction === 'inbound'
                        && ($lastOutboundKey === null || $key > $lastOutboundKey))->count(),
                ];
            })
            ->sortByDesc('lastActivity')
            ->values();
    }
}
