<?php

namespace ArchiElite\ShortUrl\Providers;

use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use ArchiElite\ShortUrl\Models\ShortUrl;
use ArchiElite\ShortUrl\Repositories\Caches\ShortUrlCacheDecorator;
use ArchiElite\ShortUrl\Repositories\Eloquent\ShortUrlRepository;
use ArchiElite\ShortUrl\Repositories\Interfaces\ShortUrlInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class ShortUrlServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(ShortUrlInterface::class, function () {
            return new ShortUrlCacheDecorator(new ShortUrlRepository(new ShortUrl()));
        });

        Helper::autoload(__DIR__ . '/../../helpers');
    }

    public function boot(): void
    {
        $this->setNamespace('plugins/short-url')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->publishAssets();

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()->registerItem([
                'id' => 'cms-plugins-short_url',
                'priority' => 5,
                'parent_id' => null,
                'name' => 'plugins/short-url::short-url.name',
                'icon' => 'fas fa-link',
                'url' => route('short_url.index'),
                'permissions' => ['short_url.index'],
            ]);
        });
    }
}
