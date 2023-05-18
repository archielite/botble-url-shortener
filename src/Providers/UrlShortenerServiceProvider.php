<?php

namespace ArchiElite\UrlShortener\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Repositories\Caches\UrlShortenerCacheDecoratorShortener;
use ArchiElite\UrlShortener\Repositories\Eloquent\UrlShortenerRepositoryShortener;
use ArchiElite\UrlShortener\Repositories\Interfaces\UrlShortenerInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class UrlShortenerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(UrlShortenerInterface::class, function () {
            return new UrlShortenerCacheDecoratorShortener(new UrlShortenerRepositoryShortener(new UrlShortener()));
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
