<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Models;

use ArchiElite\UrlShortener\Models\Analytics;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    public function test_store_persists_an_analytics_record(): void
    {
        Analytics::store([
            'short_url' => 'abc123',
            'click' => 0,
            'real_click' => 1,
            'country' => 'US',
            'country_full' => 'United States',
            'referer' => 'https://google.com',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('short_url_analytics', [
            'short_url' => 'abc123',
            'real_click' => 1,
            'country' => 'US',
        ]);
    }

    public function test_real_click_returns_true_for_first_visit(): void
    {
        $this->assertTrue(Analytics::realClick('abc123', '127.0.0.1'));
    }

    public function test_real_click_returns_false_for_repeat_visit_within_a_day(): void
    {
        $this->makeAnalyticsClick('abc123', ['ip_address' => '10.0.0.1']);

        $this->assertFalse(Analytics::realClick('abc123', '10.0.0.1'));
    }

    public function test_real_click_returns_true_after_a_day(): void
    {
        $analytics = $this->makeAnalyticsClick('abc123', ['ip_address' => '10.0.0.2']);
        $analytics->created_at = Carbon::now()->subDays(2);
        $analytics->save();

        $this->assertTrue(Analytics::realClick('abc123', '10.0.0.2'));
    }

    public function test_get_clicks_counts_all_records(): void
    {
        $this->makeAnalyticsClick('abc123', ['real_click' => 1]);
        $this->makeAnalyticsClick('abc123', ['real_click' => 0]);
        $this->makeAnalyticsClick('abc123', ['real_click' => 1]);
        $this->makeAnalyticsClick('xyz789');

        $this->assertSame(3, Analytics::getClicks('abc123'));
    }

    public function test_get_real_clicks_counts_only_real_clicks(): void
    {
        $this->makeAnalyticsClick('abc123', ['real_click' => 1]);
        $this->makeAnalyticsClick('abc123', ['real_click' => 0]);
        $this->makeAnalyticsClick('abc123', ['real_click' => 1]);

        $this->assertSame(2, Analytics::getRealClicks('abc123'));
    }

    public function test_get_today_clicks_excludes_old_records(): void
    {
        $this->makeAnalyticsClick('abc123');
        $this->makeAnalyticsClick('abc123');

        $old = $this->makeAnalyticsClick('abc123');
        $old->created_at = Carbon::now()->subDays(5);
        $old->save();

        $this->assertSame(2, Analytics::getTodayClicks('abc123'));
    }

    public function test_get_referrers_groups_by_referer(): void
    {
        $this->makeAnalyticsClick('abc123', ['referer' => 'https://google.com', 'real_click' => 1]);
        $this->makeAnalyticsClick('abc123', ['referer' => 'https://google.com', 'real_click' => 1]);
        $this->makeAnalyticsClick('abc123', ['referer' => 'https://twitter.com', 'real_click' => 1]);
        $this->makeAnalyticsClick('abc123', ['referer' => null, 'real_click' => 1]);

        $referrers = Analytics::getReferrers('abc123');

        $this->assertGreaterThanOrEqual(3, $referrers->total());
    }

    public function test_get_countries_views_groups_by_country(): void
    {
        $this->makeAnalyticsClick('abc123', ['country_full' => 'United States']);
        $this->makeAnalyticsClick('abc123', ['country_full' => 'United States']);
        $this->makeAnalyticsClick('abc123', ['country_full' => 'Vietnam']);

        $countries = Analytics::getCountriesViews('abc123');

        $this->assertSame(2, $countries['United States']);
        $this->assertSame(1, $countries['Vietnam']);
    }

    public function test_get_countries_real_views_filters_real_clicks(): void
    {
        $this->makeAnalyticsClick('abc123', ['country_full' => 'United States', 'real_click' => 1]);
        $this->makeAnalyticsClick('abc123', ['country_full' => 'United States', 'real_click' => 0]);

        $countries = Analytics::getCountriesRealViews('abc123');

        $this->assertSame(1, $countries['United States']);
    }

    public function test_get_countries_color_returns_a_color_per_country(): void
    {
        $colors = Analytics::getCountriesColor(['US' => 1, 'VN' => 2, 'FR' => 3]);

        $this->assertCount(4, $colors);
        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/^\d{1,3}, \d{1,3}, \d{1,3}$/', $color);
        }
    }

    public function test_get_creation_date_returns_human_diff_when_url_exists(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'abc123']);
        $url->created_at = Carbon::now()->subDays(2);
        $url->save();

        $this->assertNotNull(Analytics::getCreationDate('abc123'));
    }

    public function test_get_creation_date_returns_null_when_url_missing(): void
    {
        $this->assertNull(Analytics::getCreationDate('does-not-exist'));
    }
}
