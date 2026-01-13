<?php

namespace ArchiElite\UrlShortener\Services\Rules;

use ArchiElite\UrlShortener\Models\UrlShortener;

interface UrlAccessRuleInterface
{
    /**
     * Check if the URL passes this access rule.
     *
     * @param UrlShortener $urlShortener
     * @return array ['passed' => bool, 'reason' => string|null]
     */
    public function check(UrlShortener $urlShortener): array;

    /**
     * Get the priority of this rule (lower number = higher priority).
     *
     * @return int
     */
    public function getPriority(): int;
}
