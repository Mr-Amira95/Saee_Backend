<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

/**
 * Media files for WhatsApp messages: downloading what contacts send, uploading
 * what admins send, and storing both on the private disk (config whatsapp.media_disk).
 */
class WhatsAppMediaService
{
    /**
     * What Meta accepts per message type (max size in MB). Anything not matched
     * by image / video / audio is sent as a document.
     */
    public const LIMITS = [
        'image'    => ['mimes' => ['image/jpeg', 'image/png'], 'max_mb' => 5],
        'video'    => ['mimes' => ['video/mp4', 'video/3gpp'], 'max_mb' => 16],
        'audio'    => ['mimes' => ['audio/aac', 'audio/amr', 'audio/mpeg', 'audio/mp4', 'audio/ogg'], 'max_mb' => 16],
        'document' => ['mimes' => [], 'max_mb' => 100],
    ];

    /** Mime types safe to show inline in the browser; everything else is downloaded. */
    private const INLINE = ['image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'video/mp4', 'video/3gpp', 'audio/aac', 'audio/amr', 'audio/mpeg', 'audio/mp4', 'audio/ogg'];

    public function typeFor(string $mime): string
    {
        $mime = $this->baseMime($mime);

        foreach (['image', 'video', 'audio'] as $type) {
            if (in_array($mime, self::LIMITS[$type]['mimes'], true)) {
                return $type;
            }
        }

        return 'document';
    }

    /**
     * Human-readable reason the file can't be sent as $type, or null when it's fine.
     */
    public function rejectionReason(string $type, int $bytes): ?string
    {
        $maxMb = self::LIMITS[$type]['max_mb'];

        return $bytes > $maxMb * 1024 * 1024
            ? __('This :type is too large. WhatsApp allows up to :max MB.', ['type' => __($type), 'max' => $maxMb])
            : null;
    }

    public function canShowInline(?string $mime): bool
    {
        return in_array($this->baseMime((string) $mime), self::INLINE, true);
    }

    /**
     * Save file contents on the media disk and return the stored path.
     */
    public function store(string $contents, string $mime): string
    {
        $extension = MimeTypes::getDefault()->getExtensions($this->baseMime($mime))[0] ?? 'bin';
        $path = 'whatsapp/' . now()->format('Y/m') . '/' . Str::uuid() . '.' . $extension;

        Storage::disk($this->disk())->put($path, $contents);

        return $path;
    }

    public function absolutePath(string $path): string
    {
        return Storage::disk($this->disk())->path($path);
    }

    public function exists(?string $path): bool
    {
        return $path && Storage::disk($this->disk())->exists($path);
    }

    /**
     * Fetch a contact's media from Meta (by media id) and store it locally.
     * Meta's download URLs expire after a few minutes, so call this promptly.
     *
     * @return array{path: string, mime: string, size: int}
     */
    public function download(string $mediaId): array
    {
        $info = Http::withToken(config('whatsapp.api_token'))
            ->timeout(15)
            ->get(rtrim(config('whatsapp.api_url'), '/') . '/' . $mediaId)
            ->throw();

        $file = Http::withToken(config('whatsapp.api_token'))
            ->timeout(60)
            ->get($info->json('url'))
            ->throw();

        $mime = $info->json('mime_type') ?? $file->header('Content-Type') ?: 'application/octet-stream';

        return [
            'path' => $this->store($file->body(), $mime),
            'mime' => $this->baseMime($mime),
            'size' => strlen($file->body()),
        ];
    }

    /**
     * Upload a stored file to Meta and return the media id to send it with.
     */
    public function upload(string $path, string $mime): string
    {
        $url = sprintf('%s/%s/media', rtrim(config('whatsapp.api_url'), '/'), config('whatsapp.sender'));

        $response = Http::withToken(config('whatsapp.api_token'))
            ->timeout(120)
            ->attach('file', Storage::disk($this->disk())->get($path), basename($path), ['Content-Type' => $mime])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'type'              => $mime,
            ]);

        if (! $response->successful() || ! $response->json('id')) {
            throw new \RuntimeException($response->json('error.message') ?? "Media upload failed (HTTP {$response->status()}).");
        }

        return $response->json('id');
    }

    /**
     * Convert a browser recording to OGG/Opus mono — the format WhatsApp shows as a
     * voice message. Needs ffmpeg (config whatsapp.ffmpeg); returns null without it.
     */
    public function toVoiceNote(string $sourcePath): ?string
    {
        $base = tempnam(sys_get_temp_dir(), 'wa-voice');
        $target = $base . '.ogg';
        @unlink($base);

        try {
            $result = Process::timeout(60)->run([
                config('whatsapp.ffmpeg', 'ffmpeg'), '-y', '-i', $sourcePath,
                '-vn', '-ac', '1', '-c:a', 'libopus', '-b:a', '32k', $target,
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $result->successful() || ! is_file($target) || filesize($target) === 0) {
            @unlink($target);

            return null;
        }

        $contents = file_get_contents($target);
        @unlink($target);

        return $this->store($contents, 'audio/ogg');
    }

    private function baseMime(string $mime): string
    {
        return strtolower(trim(explode(';', $mime)[0]));
    }

    private function disk(): string
    {
        return config('whatsapp.media_disk', 'local');
    }
}
