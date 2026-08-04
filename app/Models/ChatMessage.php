<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'role',
        'message',
        'tokens',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    /**
     * Assistant replies are stored as raw HTML (per OpenAIChatbotService's
     * system prompt). Strip dangerous tags/attributes before it's ever
     * echoed with {!! !!}, mirroring the client-side sanitizeAiHtml() JS.
     */
    public function safeHtml(): string
    {
        $html = trim((string) $this->message);

        if ($html === '') {
            return '';
        }

        $disallowedTags = ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form'];

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $root = $dom->getElementById('__root');
        if ($root === null) {
            return e($html);
        }

        foreach ($disallowedTags as $tag) {
            $nodes = $root->getElementsByTagName($tag);
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
                $node = $nodes->item($i);
                $node->parentNode->removeChild($node);
            }
        }

        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('.//*', $root) as $element) {
            if (! $element instanceof \DOMElement) {
                continue;
            }

            foreach (iterator_to_array($element->attributes ?? []) as $attr) {
                $name = strtolower($attr->name);
                $value = strtolower(trim($attr->value));
                $isUnsafeUrl = in_array($name, ['href', 'src'], true) && str_starts_with($value, 'javascript:');

                if (str_starts_with($name, 'on') || $isUnsafeUrl) {
                    $element->removeAttribute($attr->name);
                }
            }
        }

        $inner = '';
        foreach ($root->childNodes as $child) {
            $inner .= $dom->saveHTML($child);
        }

        return $inner;
    }

    /**
     * Detect this message's text direction independent of the viewer's UI
     * locale, mirroring the client-side detectTextDirection() JS so admin
     * bubbles align (e.g. list bullets) the same way the client portal does.
     */
    public function textDirection(): string
    {
        return self::detectDirection((string) $this->message);
    }

    /**
     * Same detection, usable for raw AI reply strings that aren't yet
     * persisted as a ChatMessage (e.g. the /api/chatbot/message response).
     */
    public static function detectDirection(string $text): string
    {
        $visible = preg_replace('/<[^>]*>/', ' ', $text);
        $visible = preg_replace('/[\w.+-]+@[\w-]+\.[\w.-]+/', ' ', $visible);
        $visible = preg_replace('/https?:\/\/\S+/', ' ', $visible);
        $visible = preg_replace('/[+\d][\d\s\-()]{5,}/', ' ', $visible);

        $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}]/u', $visible);
        $latinCount = preg_match_all('/[A-Za-z]/', $visible);

        return $arabicCount >= $latinCount ? 'rtl' : 'ltr';
    }
}
