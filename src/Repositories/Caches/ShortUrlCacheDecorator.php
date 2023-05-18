<?php

namespace ArchiElite\UrlShortener\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use ArchiElite\UrlShortener\Repositories\Interfaces\ShortUrlInterface;

class ShortUrlCacheDecorator extends CacheAbstractDecorator implements ShortUrlInterface
{
}
