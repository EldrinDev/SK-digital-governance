<?php

namespace App;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\HtmlString;

/**
 * Draws QR codes as inline SVG, so they print sharply and need no image files.
 */
class QrCode
{
    /**
     * Get an SVG QR code for the given text, in the platform's navy on white.
     */
    public static function svg(string $text, int $size = 200): HtmlString
    {
        $style = new RendererStyle($size, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(20, 43, 74)));
        $writer = new Writer(new ImageRenderer($style, new SvgImageBackEnd));

        // Drop the XML declaration so the SVG can sit inside the page.
        return new HtmlString(preg_replace('/^<\?xml[^>]*>\s*/', '', $writer->writeString($text)));
    }
}
