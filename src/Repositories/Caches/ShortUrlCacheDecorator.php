<?php

namespace ArchiElite\ShortUrl\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use ArchiElite\ShortUrl\Repositories\Interfaces\ShortUrlInterface;

class ShortUrlCacheDecorator extends CacheAbstractDecorator implements ShortUrlInterface
{
}
