<?php

namespace Tests\Unit;

use App\Services\QrCodeService;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    private QrCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QrCodeService();
    }

    public function test_svg_output_is_valid_svg(): void
    {
        $svg = $this->service->svg('https://example.com/r/abc-123');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_svg_is_deterministic_for_same_input(): void
    {
        $a = $this->service->svg('https://example.com/r/same');
        $b = $this->service->svg('https://example.com/r/same');

        $this->assertSame($a, $b);
    }

    public function test_different_input_produces_different_output(): void
    {
        $a = $this->service->svg('https://example.com/r/one');
        $b = $this->service->svg('https://example.com/r/two');

        $this->assertNotSame($a, $b);
    }

    public function test_size_changes_rendered_dimensions(): void
    {
        $small = $this->service->svg('https://example.com/r/abc', 100);
        $large = $this->service->svg('https://example.com/r/abc', 400);

        $this->assertStringContainsString('width="100"', $small);
        $this->assertStringContainsString('width="400"', $large);
    }

    public function test_inline_svg_strips_xml_declaration(): void
    {
        $inline = $this->service->inlineSvg('https://example.com/r/abc');

        $this->assertStringNotContainsString('<?xml', $inline);
        $this->assertStringContainsString('<svg', $inline);
    }
}
