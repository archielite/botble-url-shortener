<?php

namespace ArchiElite\UrlShortener\Services\Rules;

use ArchiElite\UrlShortener\Models\UrlShortener;

class ExpiredRule implements UrlAccessRuleInterface
{
    public function check(UrlShortener $urlShortener): array
    {
        if ($urlShortener->isExpired()) {
            return [
                'passed' => false,
                'reason' => 'expired',
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
        ];
    }

    public function getPriority(): int
    {
        return 1;
    }
}
