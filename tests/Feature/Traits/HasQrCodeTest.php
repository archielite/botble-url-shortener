<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Traits;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Services\QrCodeService;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class HasQrCodeTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    public function test_get_qr_code_url_uses_short_url_route(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $qrUrl = $url->getQrCodeUrl();

        $this->assertStringContainsString('api.qrserver.com', $qrUrl);
        $this->assertStringContainsString(urlencode(route('url_shortener.go', 'abc123')), $qrUrl);
    }

    public function test_get_qr_code_url_passes_options_through(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $qrUrl = $url->getQrCodeUrl(['size' => '500x500', 'format' => 'svg']);

        $this->assertStringContainsString('size=500x500', $qrUrl);
        $this->assertStringContainsString('format=svg', $qrUrl);
    }

    public function test_get_qr_code_image_returns_payload_via_service(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('binary', 200),
        ]);

        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $this->assertSame('binary', $url->getQrCodeImage());
    }

    public function test_get_qr_code_page_url_resolves_route(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $this->assertSame(
            route('url_shortener.qr-code.show', 'abc123'),
            $url->getQrCodePageUrl()
        );
    }

    public function test_get_qr_code_download_url_includes_options(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $downloadUrl = $url->getQrCodeDownloadUrl(['format' => 'svg']);

        $this->assertStringContainsString('abc123', $downloadUrl);
        $this->assertStringContainsString('format=svg', $downloadUrl);
    }

    public function test_clear_qr_code_cache_delegates_to_service(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('payload', 200),
        ]);

        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $url->getQrCodeImage();
        $url->clearQrCodeCache();
        $url->getQrCodeImage();

        Http::assertSentCount(2);
    }

    public function test_qr_code_returns_default_url(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'abc123']);

        $this->assertSame($url->getQrCodeUrl(), $url->qrCode());
    }
}
