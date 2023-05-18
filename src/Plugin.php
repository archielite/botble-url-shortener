<?php

namespace ArchiElite\UrlShortener;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('short_urls');
        Schema::dropIfExists('short_url_analytics');
    }
}
