<?php

namespace ArchiElite\UrlShortener\Tests\Unit\Models;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Tests\TestCase;
use Carbon\Carbon;

class UrlShortenerTest extends TestCase
{
    public function test_is_expired_returns_false_when_expired_at_is_null(): void
    {
        $url = new UrlShortener();
        $url->expired_at = null;

        $this->assertFalse($url->isExpired());
    }

    public function test_is_expired_returns_false_when_expired_at_is_in_the_future(): void
    {
        $url = new UrlShortener();
        $url->expired_at = Carbon::now()->addHour();

        $this->assertFalse($url->isExpired());
    }

    public function test_is_expired_returns_true_when_expired_at_is_in_the_past(): void
    {
        $url = new UrlShortener();
        $url->expired_at = Carbon::now()->subSecond();

        $this->assertTrue($url->isExpired());
    }

    public function test_short_url_table_name(): void
    {
        $this->assertSame('short_urls', (new UrlShortener())->getTable());
    }

    public function test_fillable_attributes(): void
    {
        $expected = ['long_url', 'short_url', 'user_id', 'status', 'expired_at', 'max_clicks'];

        $this->assertSame($expected, (new UrlShortener())->getFillable());
    }

    public function test_max_clicks_is_cast_to_integer(): void
    {
        $url = new UrlShortener();
        $url->max_clicks = '42';

        $this->assertSame(42, $url->max_clicks);
    }

    public function test_expired_at_is_cast_to_datetime(): void
    {
        $url = new UrlShortener();
        $url->expired_at = '2030-01-01 10:00:00';

        $this->assertInstanceOf(Carbon::class, $url->expired_at);
        $this->assertSame('2030-01-01 10:00:00', $url->expired_at->toDateTimeString());
    }
}
