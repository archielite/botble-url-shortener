<?php

namespace ArchiElite\UrlShortener\Tests\Unit\Services\Rules;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Services\Rules\ExpiredRule;
use ArchiElite\UrlShortener\Tests\TestCase;
use Carbon\Carbon;

class ExpiredRuleTest extends TestCase
{
    public function test_passes_when_expired_at_is_null(): void
    {
        $url = new UrlShortener();
        $url->expired_at = null;

        $result = (new ExpiredRule())->check($url);

        $this->assertTrue($result['passed']);
        $this->assertNull($result['reason']);
    }

    public function test_passes_when_expired_at_is_in_the_future(): void
    {
        $url = new UrlShortener();
        $url->expired_at = Carbon::now()->addDay();

        $result = (new ExpiredRule())->check($url);

        $this->assertTrue($result['passed']);
        $this->assertNull($result['reason']);
    }

    public function test_fails_when_expired_at_is_in_the_past(): void
    {
        $url = new UrlShortener();
        $url->expired_at = Carbon::now()->subSecond();

        $result = (new ExpiredRule())->check($url);

        $this->assertFalse($result['passed']);
        $this->assertSame('expired', $result['reason']);
    }

    public function test_priority_is_one(): void
    {
        $this->assertSame(1, (new ExpiredRule())->getPriority());
    }
}
