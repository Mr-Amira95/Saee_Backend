<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * Renders a Blade view to a dompdf PDF with Arabic text pre-shaped, since
 * dompdf cannot join Arabic letters or apply right-to-left ordering itself.
 */
class ArabicPdfRenderer
{
    /** Max characters per line when shaping Arabic text (lines are wrapped before dompdf sees them). */
    private const ARABIC_LINE_CHARS = 60;

    /**
     * @param  string|array<int, float>  $paper  dompdf paper name, or [0, 0, width, height] in points
     * @param  int|null  $lineChars  Arabic wrap width; narrow papers need a smaller value
     */
    public function render(
        string $view,
        array $data = [],
        string $orientation = 'portrait',
        string|array $paper = 'a4',
        ?int $lineChars = null,
    ): DomPdf {
        @set_time_limit(300);

        $html = $this->shapeArabic(view($view, $data)->render(), $lineChars ?? self::ARABIC_LINE_CHARS);

        return Pdf::loadHTML($html)->setPaper($paper, $orientation);
    }

    /**
     * Every Arabic fragment of the HTML is pre-shaped into presentation forms
     * in visual order (ar-php's documented dompdf recipe).
     */
    private function shapeArabic(string $html, int $lineChars): string
    {
        $arabic = new Arabic();
        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 0; $i -= 2) {
            $start  = $positions[$i - 1];
            $length = $positions[$i] - $start;

            // Diacritics (tashkeel) can't be positioned by dompdf — drop them before shaping
            $text   = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', substr($html, $start, $length));
            $shaped = $arabic->utf8Glyphs($text, $lineChars, false);
            $html   = substr_replace($html, str_replace("\n", '<br>', $shaped), $start, $length);
        }

        return $html;
    }
}
