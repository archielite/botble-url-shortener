<?php

namespace ArchiElite\UrlShortener\Tests\Feature\Http;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Tests\Concerns\CreatesUrlShorteners;
use ArchiElite\UrlShortener\Tests\TestCase;
use Botble\ACL\Models\User;
use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlShortenerCrudTest extends TestCase
{
    use CreatesUrlShorteners;
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->superUser()->create();
        $this->actingAs($this->admin, 'admin');
    }

    public function test_index_renders_listing_page(): void
    {
        $response = $this->get(route('url_shortener.index'));

        $response->assertOk();
    }

    public function test_create_renders_form(): void
    {
        $response = $this->get(route('url_shortener.create'));

        $response->assertOk();
    }

    public function test_store_creates_short_url_with_explicit_alias(): void
    {
        $response = $this->post(route('url_shortener.store'), [
            'long_url' => 'https://example.com/page',
            'short_url' => 'manual-alias',
            'status' => BaseStatusEnum::PUBLISHED->value,
        ]);

        $this->assertDatabaseHas('short_urls', [
            'short_url' => 'manual-alias',
            'long_url' => 'https://example.com/page',
            'user_id' => $this->admin->getKey(),
        ]);
    }

    public function test_store_generates_random_short_url_when_alias_omitted(): void
    {
        $this->post(route('url_shortener.store'), [
            'long_url' => 'https://example.com/auto',
            'status' => BaseStatusEnum::PUBLISHED->value,
        ]);

        $created = UrlShortener::query()->where('long_url', 'https://example.com/auto')->first();

        $this->assertNotNull($created);
        $this->assertNotEmpty($created->short_url);
        $this->assertSame(6, strlen($created->short_url));
    }

    public function test_store_rejects_duplicate_short_url(): void
    {
        $this->makeShortUrl(['short_url' => 'taken-alias']);

        $response = $this->post(route('url_shortener.store'), [
            'long_url' => 'https://example.com/page',
            'short_url' => 'taken-alias',
            'status' => BaseStatusEnum::PUBLISHED->value,
        ]);

        $response->assertSessionHasErrors('short_url');
    }

    public function test_store_validates_long_url_format(): void
    {
        $response = $this->post(route('url_shortener.store'), [
            'long_url' => 'not-a-valid-url',
            'status' => BaseStatusEnum::PUBLISHED->value,
        ]);

        $response->assertSessionHasErrors('long_url');
    }

    public function test_edit_renders_form_for_existing_short_url(): void
    {
        $url = $this->makeShortUrl();

        $response = $this->get(route('url_shortener.edit', $url->getKey()));

        $response->assertOk();
    }

    public function test_update_persists_changes(): void
    {
        $url = $this->makeShortUrl([
            'short_url' => 'before',
            'long_url' => 'https://example.com/before',
        ]);

        $this->put(route('url_shortener.update', $url->getKey()), [
            'long_url' => 'https://example.com/after',
            'short_url' => 'after',
            'status' => BaseStatusEnum::PUBLISHED->value,
        ]);

        $this->assertDatabaseHas('short_urls', [
            'id' => $url->getKey(),
            'short_url' => 'after',
            'long_url' => 'https://example.com/after',
        ]);
    }

    public function test_update_allows_keeping_same_short_url(): void
    {
        $url = $this->makeShortUrl(['short_url' => 'keepit']);

        $response = $this->put(route('url_shortener.update', $url->getKey()), [
            'long_url' => 'https://example.com/updated',
            'short_url' => 'keepit',
            'status' => BaseStatusEnum::PUBLISHED->value,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_destroy_deletes_short_url(): void
    {
        $url = $this->makeShortUrl();

        $this->delete(route('url_shortener.destroy', $url->getKey()));

        $this->assertDatabaseMissing('short_urls', ['id' => $url->getKey()]);
    }

    public function test_unauthenticated_users_cannot_access_admin_routes(): void
    {
        auth('admin')->logout();

        $response = $this->get(route('url_shortener.index'));

        $this->assertContains($response->getStatusCode(), [302, 401, 403]);
    }
}
