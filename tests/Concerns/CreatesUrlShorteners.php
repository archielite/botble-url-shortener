<?php

namespace ArchiElite\UrlShortener\Tests\Concerns;

use ArchiElite\UrlShortener\Models\Analytics;
use ArchiElite\UrlShortener\Models\UrlShortener;
use Botble\Base\Enums\BaseStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait CreatesUrlShorteners
{
    protected function makeShortUrl(array $attributes = []): UrlShortener
    {
        $defaults = [
            'long_url' => 'https://example.com/' . Str::random(8),
            'short_url' => Str::random(6),
            'user_id' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
            'expired_at' => null,
            'max_clicks' => null,
        ];

        return UrlShortener::query()->create(array_merge($defaults, $attributes));
    }

    protected function makeAnalyticsClick(string $shortUrl, array $attributes = []): Analytics
    {
        $defaults = [
            'short_url' => $shortUrl,
            'click' => 0,
            'real_click' => 1,
            'country' => 'US',
            'country_full' => 'United States',
            'referer' => null,
            'ip_address' => '127.0.0.1',
        ];

        $analytics = new Analytics();
        $analytics->fill(array_merge($defaults, $attributes));
        $analytics->save();

        return $analytics;
    }

    protected function makeExpiredShortUrl(array $attributes = []): UrlShortener
    {
        return $this->makeShortUrl(array_merge([
            'expired_at' => Carbon::now()->subDay(),
        ], $attributes));
    }

    protected function makeMaxedOutShortUrl(int $maxClicks = 1, array $attributes = []): UrlShortener
    {
        $shortUrl = $this->makeShortUrl(array_merge([
            'max_clicks' => $maxClicks,
        ], $attributes));

        for ($i = 0; $i < $maxClicks; $i++) {
            $this->makeAnalyticsClick($shortUrl->short_url);
        }

        return $shortUrl;
    }
}
