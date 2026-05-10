<?php

namespace ArchiElite\UrlShortener\Tests;

use Tests\TestCase as BaseTestCase;

/**
 * Base test case for url-shortener plugin.
 *
 * Tests run inside a Botble installation: drop this plugin at
 * platform/plugins/url-shortener and run from the host root:
 *
 *   vendor/bin/phpunit -c platform/plugins/url-shortener/phpunit.xml.dist
 */
abstract class TestCase extends BaseTestCase
{
}
