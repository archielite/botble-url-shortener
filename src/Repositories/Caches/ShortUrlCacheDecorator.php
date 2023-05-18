<?php

namespace ArchiElite\ShortenerUrl\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use ArchiElite\ShortenerUrl\Repositories\Interfaces\ShortUrlInterface;

class ShortUrlCacheDecorator extends CacheAbstractDecorator implements ShortUrlInterface
{
}
