<?php
require __DIR__ . '/../vendor/autoload.php';

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

try {
    $renderer = new ImageRenderer(
        new RendererStyle(160, 0),
        new SvgImageBackEnd()
    );
    $writer = new Writer($renderer);
    $svg = $writer->writeString('https://ideaabd.com/v/RSU-2026-001');
    $base64Svg = 'data:image/svg+xml;base64,' . base64_encode($svg);
    echo "SVG QR generated successfully! Length: " . strlen($base64Svg) . "\n";
} catch (\Throwable $e) {
    echo "QR Error: " . $e->getMessage() . "\n";
}
