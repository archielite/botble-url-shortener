<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Http;

use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Botble\ACL\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class QrCodeControllerTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->superUser()->create();
        $this->actingAs($admin, 'admin');
    }

    public function test_show_returns_view_with_qr_code_url(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-show']);

        $response = $this->get(route('url_shortener.qr-code.show', 'qr-show'));

        $response->assertOk();
        $response->assertViewHas('urlShortener');
        $response->assertViewHas('qrCodeUrl');
        $response->assertViewHas('availableSizes');
        $response->assertViewHas('errorCorrectionLevels');
        $response->assertViewHas('availableFormats');
    }

    public function test_show_returns_404_for_missing_short_url(): void
    {
        $response = $this->get(route('url_shortener.qr-code.show', 'missing'));

        $response->assertNotFound();
    }

    public function test_generate_returns_qr_code_url_payload(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-gen']);

        $response = $this->postJson(route('url_shortener.qr-code.generate', 'qr-gen'), [
            'size' => '400x400',
            'format' => 'png',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.url', fn ($url) => str_contains($url, 'size=400x400'));
        $response->assertJsonPath('data.download_url', fn ($url) => str_contains($url, 'qr-gen'));
    }

    public function test_generate_rejects_invalid_size(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-bad']);

        $response = $this->postJson(route('url_shortener.qr-code.generate', 'qr-bad'), [
            'size' => 'not-a-size',
        ]);

        $response->assertJsonPath('error', true);
    }

    public function test_generate_rejects_invalid_ecc(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-ecc']);

        $response = $this->postJson(route('url_shortener.qr-code.generate', 'qr-ecc'), [
            'ecc' => 'Z',
        ]);

        $response->assertJsonPath('error', true);
    }

    public function test_generate_rejects_unknown_format(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-fmt']);

        $response = $this->postJson(route('url_shortener.qr-code.generate', 'qr-fmt'), [
            'format' => 'webp',
        ]);

        $response->assertJsonPath('error', true);
    }

    public function test_generate_rejects_non_square_size(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-rect']);

        $response = $this->postJson(route('url_shortener.qr-code.generate', 'qr-rect'), [
            'size' => '200x300',
        ]);

        $response->assertJsonPath('error', true);
    }

    public function test_download_returns_image_with_attachment_headers(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('binary-png-payload', 200),
        ]);

        $this->makeShortUrl(['short_url' => 'qr-dl']);

        $response = $this->get(route('url_shortener.qr-code.download', ['short_url' => 'qr-dl']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Content-Disposition', 'attachment; filename="qr-code-qr-dl.png"');
        $this->assertSame('binary-png-payload', $response->getContent());
    }

    public function test_download_uses_format_for_filename(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('svg-payload', 200),
        ]);

        $this->makeShortUrl(['short_url' => 'qr-svg']);

        $response = $this->get(route('url_shortener.qr-code.download', [
            'short_url' => 'qr-svg',
            'format' => 'svg',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $response->assertHeader('Content-Disposition', 'attachment; filename="qr-code-qr-svg.svg"');
    }

    public function test_download_returns_400_on_invalid_options(): void
    {
        $this->makeShortUrl(['short_url' => 'qr-bad-dl']);

        $response = $this->get(route('url_shortener.qr-code.download', [
            'short_url' => 'qr-bad-dl',
            'size' => 'bogus',
        ]));

        $response->assertStatus(400);
    }

    public function test_download_returns_500_when_image_generation_fails(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('error', 500),
        ]);

        $this->makeShortUrl(['short_url' => 'qr-fail']);

        $response = $this->get(route('url_shortener.qr-code.download', ['short_url' => 'qr-fail']));

        $response->assertStatus(500);
    }

    public function test_image_returns_inline_image(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('inline-png', 200),
        ]);

        $this->makeShortUrl(['short_url' => 'qr-img']);

        $response = $this->get(route('url_shortener.qr-code.image', ['short_url' => 'qr-img']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Cache-Control', 'public, max-age=86400');
        $this->assertSame('inline-png', $response->getContent());
    }

    public function test_clear_cache_removes_cached_image(): void
    {
        Http::fake([
            'api.qrserver.com/*' => Http::response('payload', 200),
        ]);

        $this->makeShortUrl(['short_url' => 'qr-clear']);

        $this->get(route('url_shortener.qr-code.image', ['short_url' => 'qr-clear']));

        $response = $this->delete(route('url_shortener.qr-code.clear-cache', 'qr-clear'));

        $response->assertOk();

        $this->get(route('url_shortener.qr-code.image', ['short_url' => 'qr-clear']));

        Http::assertSentCount(2);
    }
}
