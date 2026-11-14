<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppLog extends Model
{
    use BroadcastsRealtimeChanges;

    protected $table = 'whatsapp_logs';

    protected $fillable = [
        'order_id',
        'phone',
        'message',
        'status',
        'direction',
        'message_type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public const MEDIA_TYPES = ['image', 'video', 'audio', 'document', 'sticker'];

    /**
     * Media details (path, mime, size, filename, voice, Meta id) for media
     * messages, or null for anything else.
     */
    public function mediaInfo(): ?array
    {
        $meta = $this->meta ?? [];

        if (isset($meta['media'])) {
            return $meta['media'];
        }

        // Logs saved before media support kept Meta's raw media object as meta.
        if (in_array($this->message_type, self::MEDIA_TYPES, true) && isset($meta['id'])) {
            return array_filter([
                'id'       => $meta['id'],
                'mime'     => $meta['mime_type'] ?? null,
                'filename' => $meta['filename'] ?? null,
                'voice'    => $meta['voice'] ?? null,
            ], fn ($value) => $value !== null);
        }

        return null;
    }

    /**
     * Caption of a media message ('' when there is none).
     */
    public function caption(): string
    {
        $message = trim((string) $this->message);

        // Older logs stored a "[image]"-style placeholder instead of a caption.
        return preg_match('/^\[\w+\]$/', $message) ? '' : $message;
    }

    /**
     * Readable message text. Template sends are stored as
     * "Template: {name} ({json params})", so rebuild the body from the
     * configured template text when possible.
     */
    public function displayBody(): string
    {
        if ($this->message_type !== 'template'
            || ! preg_match('/^Template: (\S+) \((.*)\)$/s', (string) $this->message, $m)) {
            return (string) $this->message;
        }

        $body   = config("whatsapp.templates.{$m[1]}");
        $params = json_decode($m[2], true);

        if (! $body || ! is_array($params)) {
            return (string) $this->message;
        }

        $position = 0;

        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($match) use ($params, &$position) {
            $value = array_is_list($params) ? ($params[$position++] ?? '') : ($params[trim($match[1])] ?? '');

            return (string) $value;
        }, $body);
    }

    /**
     * Canonical international-digits form of a phone number (e.g. "962792856567"),
     * used to group inbound/outbound logs into one conversation regardless of
     * which raw format (+962…, 0…, local 7-digit…) each side stored it in.
     * Numbers with an explicit "+" prefix are already international and are
     * kept as-is, so non-Jordanian numbers (e.g. +966…) aren't re-prefixed.
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with(trim((string) $phone), '+')) {
            return $digits;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '962')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '962' . substr($digits, 1);
        }

        return '962' . $digits;
    }
}
