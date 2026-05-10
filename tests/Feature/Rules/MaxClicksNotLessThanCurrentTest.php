<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Rules;

use ArchiElite\UrlShortener\Rules\MaxClicksNotLessThanCurrent;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaxClicksNotLessThanCurrentTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    public function test_fails_when_value_is_below_current_clicks(): void
    {
        $shortUrl = 'abc123';
        $this->makeAnalyticsClick($shortUrl);
        $this->makeAnalyticsClick($shortUrl);
        $this->makeAnalyticsClick($shortUrl);

        $rule = new MaxClicksNotLessThanCurrent($shortUrl);
        $message = null;

        $rule->validate('max_clicks', 2, function (string $msg) use (&$message): void {
            $message = $msg;
        });

        $this->assertNotNull($message);
        $this->assertStringContainsString('3', (string) $message);
    }

    public function test_passes_when_value_equals_current_clicks(): void
    {
        $shortUrl = 'eq123';
        $this->makeAnalyticsClick($shortUrl);
        $this->makeAnalyticsClick($shortUrl);

        $rule = new MaxClicksNotLessThanCurrent($shortUrl);
        $called = false;

        $rule->validate('max_clicks', 2, function () use (&$called): void {
            $called = true;
        });

        $this->assertFalse($called);
    }

    public function test_passes_when_value_above_current_clicks(): void
    {
        $shortUrl = 'gt123';
        $this->makeAnalyticsClick($shortUrl);

        $rule = new MaxClicksNotLessThanCurrent($shortUrl);
        $called = false;

        $rule->validate('max_clicks', 99, function () use (&$called): void {
            $called = true;
        });

        $this->assertFalse($called);
    }

    public function test_passes_when_no_clicks_recorded(): void
    {
        $rule = new MaxClicksNotLessThanCurrent('never-clicked');
        $called = false;

        $rule->validate('max_clicks', 1, function () use (&$called): void {
            $called = true;
        });

        $this->assertFalse($called);
    }
}
