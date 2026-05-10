<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Validation;

use ArchiElite\UrlShortener\Http\Requests\UrlShortenerRequest;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;

class UrlShortenerRequestTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    public function test_long_url_is_required(): void
    {
        $errors = $this->validate([], $this->buildRequest(null));

        $this->assertArrayHasKey('long_url', $errors);
    }

    public function test_long_url_must_be_a_valid_url(): void
    {
        $errors = $this->validate([
            'long_url' => 'not-a-url',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('long_url', $errors);
    }

    public function test_long_url_max_length_is_one_thousand(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com/' . str_repeat('a', 1000),
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('long_url', $errors);
    }

    public function test_short_url_is_optional_for_create(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
        ], $this->buildRequest(null));

        $this->assertArrayNotHasKey('short_url', $errors);
    }

    public function test_short_url_min_length(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'short_url' => 'abc',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('short_url', $errors);
    }

    public function test_short_url_max_length(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'short_url' => str_repeat('a', 31),
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('short_url', $errors);
    }

    public function test_short_url_pattern_rejects_spaces_and_invalid_chars(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'short_url' => 'has space',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('short_url', $errors);

        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'short_url' => 'has/slash',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('short_url', $errors);
    }

    public function test_short_url_must_be_unique(): void
    {
        $this->makeShortUrl(['short_url' => 'taken-1']);

        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'short_url' => 'taken-1',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('short_url', $errors);
    }

    public function test_short_url_unique_ignores_current_record_on_update(): void
    {
        $existing = $this->makeShortUrl(['short_url' => 'editme']);

        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'short_url' => 'editme',
        ], $this->buildRequest($existing));

        $this->assertArrayNotHasKey('short_url', $errors);
    }

    public function test_expired_at_must_be_after_now(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'expired_at' => '2000-01-01 00:00:00',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('expired_at', $errors);
    }

    public function test_max_clicks_min_one(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'max_clicks' => 0,
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('max_clicks', $errors);
    }

    public function test_max_clicks_must_be_integer(): void
    {
        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'max_clicks' => 'abc',
        ], $this->buildRequest(null));

        $this->assertArrayHasKey('max_clicks', $errors);
    }

    public function test_max_clicks_cannot_be_below_current_clicks_on_update(): void
    {
        $existing = $this->makeShortUrl(['short_url' => 'maxed', 'max_clicks' => 10]);
        $this->makeAnalyticsClick('maxed');
        $this->makeAnalyticsClick('maxed');
        $this->makeAnalyticsClick('maxed');

        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'max_clicks' => 1,
        ], $this->buildRequest($existing));

        $this->assertArrayHasKey('max_clicks', $errors);
    }

    public function test_max_clicks_passes_when_not_below_current_clicks(): void
    {
        $existing = $this->makeShortUrl(['short_url' => 'okmax']);
        $this->makeAnalyticsClick('okmax');

        $errors = $this->validate([
            'long_url' => 'https://example.com',
            'max_clicks' => 5,
        ], $this->buildRequest($existing));

        $this->assertArrayNotHasKey('max_clicks', $errors);
    }

    protected function buildRequest($routeBinding): UrlShortenerRequest
    {
        $request = UrlShortenerRequest::create('/admin/url-shortener', 'POST');
        $route = new Route(['POST'], 'url-shortener/{url_shortener}', []);
        $route->bind($request);
        $route->setParameter('url_shortener', $routeBinding);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    protected function validate(array $data, UrlShortenerRequest $request): array
    {
        $validator = Validator::make($data, $request->rules());

        return $validator->errors()->toArray();
    }
}
