<?php

namespace ArchiElite\UrlShortener\Tests\Unit\Services;

use ArchiElite\UrlShortener\Services\QrCodeService;
use ArchiElite\UrlShortener\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

class QrCodeServiceTest extends TestCase
{
    protected QrCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new QrCodeService();

        config()->set('plugins.url-shortener.qr-code', [
            'size' => '300x300',
            'ecc' => 'L',
            'format' => 'png',
            'color' => '0-0-0',
            'bgcolor' => '255-255-255',
            'qzone' => 1,
            'margin' => 1,
        ]);
    }

    public function test_generate_qr_code_url_uses_default_options(): void
    {
        $url = $this->service->generateQrCodeUrl('https://example.com');

        $this->assertStringContainsString('api.qrserver.com/v1/create-qr-code/', $url);
        $this->assertStringContainsString('data=' . urlencode('https://example.com'), $url);
        $this->assertStringContainsString('size=300x300', $url);
        $this->assertStringContainsString('ecc=L', $url);
        $this->assertStringContainsString('format=png', $url);
    }

    public function test_generate_qr_code_url_overrides_defaults_with_options(): void
    {
        $url = $this->service->generateQrCodeUrl('https://example.com', [
            'size' => '500x500',
            'format' => 'svg',
            'color' => 'ff0000',
        ]);

        $this->assertStringContainsString('size=500x500', $url);
        $this->assertStringContainsString('format=svg', $url);
        $this->assertStringContainsString('color=ff0000', $url);
    }

    public function test_generate_qr_code_image_caches_response(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('binary-image-data', 200),
        ]);

        $first = $this->service->generateQrCodeImage('https://example.com');
        $second = $this->service->generateQrCodeImage('https://example.com');

        $this->assertSame('binary-image-data', $first);
        $this->assertSame('binary-image-data', $second);
        Http::assertSentCount(1);
    }

    public function test_generate_qr_code_image_returns_null_on_http_failure(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('error', 500),
        ]);

        Log::shouldReceive('error')->once();

        $result = $this->service->generateQrCodeImage('https://example.com');

        $this->assertNull($result);
    }

    public function test_generate_qr_code_image_returns_null_on_exception(): void
    {
        Http::fake(function () {
            throw new \RuntimeException('connection refused');
        });

        Log::shouldReceive('error')->once();

        $result = $this->service->generateQrCodeImage('https://example.com');

        $this->assertNull($result);
    }

    public function test_validate_options_accepts_valid_payload(): void
    {
        $this->assertTrue($this->service->validateOptions([
            'size' => '300x300',
            'ecc' => 'M',
            'format' => 'svg',
            'color' => '255-0-0',
            'bgcolor' => 'ffffff',
            'margin' => 5,
            'qzone' => 4,
        ]));
    }

    public function test_validate_options_rejects_non_square_size(): void
    {
        $this->assertFalse($this->service->validateOptions(['size' => '300x400']));
    }

    public function test_validate_options_rejects_size_outside_range(): void
    {
        $this->assertFalse($this->service->validateOptions(['size' => '5x5']));
        $this->assertFalse($this->service->validateOptions(['size' => '2000x2000']));
    }

    public function test_validate_options_rejects_malformed_size(): void
    {
        $this->assertFalse($this->service->validateOptions(['size' => 'not-a-size']));
    }

    public function test_validate_options_rejects_unknown_ecc(): void
    {
        $this->assertFalse($this->service->validateOptions(['ecc' => 'Z']));
    }

    public function test_validate_options_rejects_unknown_format(): void
    {
        $this->assertFalse($this->service->validateOptions(['format' => 'webp']));
    }

    public function test_validate_options_rejects_invalid_color(): void
    {
        $this->assertFalse($this->service->validateOptions(['color' => '999-0-0']));
        $this->assertFalse($this->service->validateOptions(['bgcolor' => 'zzz']));
    }

    public function test_validate_options_rejects_margin_out_of_range(): void
    {
        $this->assertFalse($this->service->validateOptions(['margin' => -1]));
        $this->assertFalse($this->service->validateOptions(['margin' => 51]));
    }

    public function test_validate_options_rejects_qzone_out_of_range(): void
    {
        $this->assertFalse($this->service->validateOptions(['qzone' => -1]));
        $this->assertFalse($this->service->validateOptions(['qzone' => 101]));
    }

    public function test_get_mime_type_returns_expected_values(): void
    {
        $this->assertSame('image/png', $this->service->getMimeType('png'));
        $this->assertSame('image/gif', $this->service->getMimeType('gif'));
        $this->assertSame('image/jpeg', $this->service->getMimeType('jpeg'));
        $this->assertSame('image/jpeg', $this->service->getMimeType('jpg'));
        $this->assertSame('image/svg+xml', $this->service->getMimeType('svg'));
        $this->assertSame('application/postscript', $this->service->getMimeType('eps'));
        $this->assertSame('image/png', $this->service->getMimeType('unknown'));
    }

    public function test_get_available_sizes_returns_known_keys(): void
    {
        $sizes = $this->service->getAvailableSizes();

        $this->assertArrayHasKey('100x100', $sizes);
        $this->assertArrayHasKey('800x800', $sizes);
    }

    public function test_get_available_formats_returns_known_keys(): void
    {
        $formats = $this->service->getAvailableFormats();

        foreach (['png', 'gif', 'jpeg', 'jpg', 'svg', 'eps'] as $key) {
            $this->assertArrayHasKey($key, $formats);
        }
    }

    public function test_get_error_correction_levels_returns_all_levels(): void
    {
        $levels = $this->service->getErrorCorrectionLevels();

        foreach (['L', 'M', 'Q', 'H'] as $level) {
            $this->assertArrayHasKey($level, $levels);
        }
    }

    public function test_clear_cache_forgets_entry(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('payload', 200),
        ]);

        $this->service->generateQrCodeImage('https://example.com');
        $this->service->clearCache('https://example.com');
        $this->service->generateQrCodeImage('https://example.com');

        Http::assertSentCount(2);
    }

    public function test_is_valid_color_accepts_hex_and_rgb(): void
    {
        $isValidColor = (new ReflectionClass($this->service))->getMethod('isValidColor');
        $isValidColor->setAccessible(true);

        $this->assertTrue($isValidColor->invoke($this->service, '0-0-0'));
        $this->assertTrue($isValidColor->invoke($this->service, '255-255-255'));
        $this->assertTrue($isValidColor->invoke($this->service, 'fff'));
        $this->assertTrue($isValidColor->invoke($this->service, 'ABCDEF'));
        $this->assertFalse($isValidColor->invoke($this->service, '256-0-0'));
        $this->assertFalse($isValidColor->invoke($this->service, 'gggggg'));
    }
}
