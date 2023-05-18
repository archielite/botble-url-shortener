<?php

namespace ArchiElite\ShortenerUrl\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use ArchiElite\ShortenerUrl\Models\ShortUrl;
use ArchiElite\ShortenerUrl\Repositories\Caches\ShortUrlCacheDecorator;
use ArchiElite\ShortenerUrl\Repositories\Eloquent\ShortUrlRepository;
use ArchiElite\ShortenerUrl\Repositories\Interfaces\ShortUrlInterface;
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
        $this->setNamespace('plugins/url-shortener')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->publishAssets();

        Event::listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-url_shortener',
                'priority' => 5,
                'parent_id' => null,
                'name' => 'plugins/url-shortener::url-shortener.name',
                'icon' => 'fas fa-link',
                'url' => route('url_shortener.index'),
                'permissions' => ['url_shortener.index'],
            ]);
        });
    }
}
