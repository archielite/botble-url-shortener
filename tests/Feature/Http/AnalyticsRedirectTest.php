<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Http;

use ArchiElite\UrlShortener\Models\Analytics;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsRedirectTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    public function test_redirects_to_long_url_for_published_short_url(): void
    {
        $url = $this->makeShortUrl([
            'short_url' => 'go-1',
            'long_url' => 'https://example.com/landing',
        ]);

        $response = $this->get(route('url_shortener.go', 'go-1'));

        $response->assertRedirect('https://example.com/landing');
    }

    public function test_records_an_analytics_row_on_visit(): void
    {
        $this->makeShortUrl(['short_url' => 'go-2']);

        $this->get(route('url_shortener.go', 'go-2'));

        $this->assertDatabaseHas('short_url_analytics', [
            'short_url' => 'go-2',
        ]);
    }

    public function test_first_visit_is_a_real_click(): void
    {
        $this->makeShortUrl(['short_url' => 'go-3']);

        $this->get(route('url_shortener.go', 'go-3'));

        $this->assertSame(1, Analytics::getRealClicks('go-3'));
    }

    public function test_repeated_visit_from_same_ip_is_not_counted_as_real_click(): void
    {
        $this->makeShortUrl(['short_url' => 'go-4']);

        $this->get(route('url_shortener.go', 'go-4'));
        $this->get(route('url_shortener.go', 'go-4'));

        $this->assertSame(2, Analytics::getClicks('go-4'));
        $this->assertSame(1, Analytics::getRealClicks('go-4'));
    }

    public function test_redirects_home_when_short_url_does_not_exist(): void
    {
        $response = $this->get(route('url_shortener.go', 'missing-99'));

        $response->assertRedirect(BaseHelper::getHomepageUrl());
    }

    public function test_redirects_home_when_short_url_is_unpublished(): void
    {
        $this->makeShortUrl([
            'short_url' => 'draft-1',
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $response = $this->get(route('url_shortener.go', 'draft-1'));

        $response->assertRedirect(BaseHelper::getHomepageUrl());
    }

    public function test_redirects_home_when_short_url_is_expired(): void
    {
        $this->makeExpiredShortUrl(['short_url' => 'expired-1']);

        $response = $this->get(route('url_shortener.go', 'expired-1'));

        $response->assertRedirect(BaseHelper::getHomepageUrl());
        $this->assertDatabaseMissing('short_url_analytics', [
            'short_url' => 'expired-1',
        ]);
    }

    public function test_redirects_home_when_max_clicks_reached(): void
    {
        $this->makeMaxedOutShortUrl(2, ['short_url' => 'maxed-1']);

        $response = $this->get(route('url_shortener.go', 'maxed-1'));

        $response->assertRedirect(BaseHelper::getHomepageUrl());
    }

    public function test_captures_referer_header(): void
    {
        $this->makeShortUrl(['short_url' => 'go-ref']);

        $this->withHeader('referer', 'https://twitter.com/post')
            ->get(route('url_shortener.go', 'go-ref'));

        $this->assertDatabaseHas('short_url_analytics', [
            'short_url' => 'go-ref',
            'referer' => 'https://twitter.com/post',
        ]);
    }
}
