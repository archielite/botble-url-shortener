<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Services\Rules;

use ArchiElite\UrlShortener\Services\Rules\MaxClicksRule;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaxClicksRuleTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    public function test_passes_when_max_clicks_is_null(): void
    {
        $url = $this->makeShortUrl(['max_clicks' => null]);

        $result = (new MaxClicksRule())->check($url);

        $this->assertTrue($result['passed']);
        $this->assertNull($result['reason']);
    }

    public function test_passes_when_clicks_below_limit(): void
    {
        $url = $this->makeShortUrl(['max_clicks' => 10]);
        $this->makeAnalyticsClick($url->short_url);
        $this->makeAnalyticsClick($url->short_url);

        $result = (new MaxClicksRule())->check($url);

        $this->assertTrue($result['passed']);
    }

    public function test_fails_when_clicks_reach_limit(): void
    {
        $url = $this->makeShortUrl(['max_clicks' => 2]);
        $this->makeAnalyticsClick($url->short_url);
        $this->makeAnalyticsClick($url->short_url);

        $result = (new MaxClicksRule())->check($url);

        $this->assertFalse($result['passed']);
        $this->assertSame('max_clicks_reached', $result['reason']);
    }

    public function test_fails_when_clicks_exceed_limit(): void
    {
        $url = $this->makeShortUrl(['max_clicks' => 1]);
        $this->makeAnalyticsClick($url->short_url);
        $this->makeAnalyticsClick($url->short_url);

        $result = (new MaxClicksRule())->check($url);

        $this->assertFalse($result['passed']);
    }

    public function test_priority_is_two(): void
    {
        $this->assertSame(2, (new MaxClicksRule())->getPriority());
    }
}
