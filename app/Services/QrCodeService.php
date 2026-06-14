<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Renders QR codes as inline SVG using the already-bundled bacon/bacon-qr-code
 * library (no imagick / GD dependency for SVG output). Single source of truth
 * for QR rendering across the on-screen preview, the print sheet, and the PDF.
 */
class QrCodeService
{
    /**
     * Render the given URL/text as an SVG QR code string.
     *
     * @param  string  $data  The value to encode (e.g. a scan URL).
     * @param  int  $size  Pixel size of the rendered SVG.
     */
    public function svg(string $data, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($data);
    }

    /**
     * Render an SVG QR code without the XML declaration so it can be inlined
     * directly into a Blade view or a dompdf template.
     */
    public function inlineSvg(string $data, int $size = 200): string
    {
        $svg = $this->svg($data, $size);

        return preg_replace('/<\?xml.*?\?>\s*/s', '', $svg) ?? $svg;
    }

    /**
     * Render QR as a base64 data URI image — works in dompdf where inline SVG does not.
     */
    public function svgDataUri(string $data, int $size = 200): string
    {
        $svg = $this->svg($data, $size);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
