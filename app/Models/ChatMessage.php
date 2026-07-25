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
}
