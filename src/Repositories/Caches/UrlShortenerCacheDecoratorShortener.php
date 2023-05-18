<?php

namespace ArchiElite\UrlShortener\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use ArchiElite\UrlShortener\Repositories\Interfaces\UrlShortenerInterface;

class UrlShortenerCacheDecoratorShortener extends CacheAbstractDecorator implements UrlShortenerInterface
{
}
