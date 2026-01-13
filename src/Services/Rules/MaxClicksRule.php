<?php

namespace ArchiElite\UrlShortener\Services\Rules;

use ArchiElite\UrlShortener\Models\Analytics;
use ArchiElite\UrlShortener\Models\UrlShortener;

class MaxClicksRule implements UrlAccessRuleInterface
{
    public function check(UrlShortener $urlShortener): array
    {
        if ($urlShortener->max_clicks === null) {
            return [
                'passed' => true,
                'reason' => null,
            ];
        }

        $totalClicks = Analytics::getClicks($urlShortener->short_url);

        if ($totalClicks >= $urlShortener->max_clicks) {
            return [
                'passed' => false,
                'reason' => 'max_clicks_reached',
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
        ];
    }

    public function getPriority(): int
    {
        return 2;
    }
}
