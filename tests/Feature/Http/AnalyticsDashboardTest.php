<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Http;

use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Botble\ACL\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsDashboardTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->superUser()->create();
        $this->actingAs($admin, 'admin');
    }

    public function test_show_renders_analytics_view_with_metrics(): void
    {
        $this->makeShortUrl(['short_url' => 'view-1']);
        $this->makeAnalyticsClick('view-1', ['real_click' => 1]);
        $this->makeAnalyticsClick('view-1', ['real_click' => 0]);
        $this->makeAnalyticsClick('view-1', ['real_click' => 1]);

        $response = $this->get(route('url_shortener.analytics', 'view-1'));

        $response->assertOk();
        $response->assertViewIs('plugins/url-shortener::analytics');
        $response->assertViewHas('clicks', 3);
        $response->assertViewHas('realClicks', 2);
        $response->assertViewHas('shortUrl');
        $response->assertViewHas('countriesViews');
        $response->assertViewHas('referrers');
    }

    public function test_show_returns_404_for_unknown_short_url(): void
    {
        $response = $this->get(route('url_shortener.analytics', 'missing-99'));

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_unpublished_short_url(): void
    {
        $this->makeShortUrl([
            'short_url' => 'draft-99',
            'status' => \Botble\Base\Enums\BaseStatusEnum::DRAFT,
        ]);

        $response = $this->get(route('url_shortener.analytics', 'draft-99'));

        $response->assertNotFound();
    }
}
