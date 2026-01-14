<?php

namespace ArchiElite\UrlShortener\Services;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Services\Rules\ExpiredRule;
use ArchiElite\UrlShortener\Services\Rules\MaxClicksRule;
use ArchiElite\UrlShortener\Services\Rules\UrlAccessRuleInterface;

class UrlAccessService
{
    /**
     * @var UrlAccessRuleInterface[]
     */
    protected array $rules = [];

    public function __construct()
    {
        $this->registerDefaultRules();
    }

    /**
     * Register default access rules.
     */
    protected function registerDefaultRules(): void
    {
        $this->addRule(new ExpiredRule());
        $this->addRule(new MaxClicksRule());
    }

    /**
     * Add a new access rule.
     *
     * @param UrlAccessRuleInterface $rule
     * @return $this
     */
    public function addRule(UrlAccessRuleInterface $rule): self
    {
        $this->rules[] = $rule;

        // Sort rules by priority
        usort($this->rules, function ($a, $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $this;
    }

    /**
     * Check if a URL can be accessed based on all rules.
     *
     * @param UrlShortener $urlShortener
     * @return array ['accessible' => bool, 'reason' => string|null]
     */
    public function canAccess(UrlShortener $urlShortener): array
    {
        foreach ($this->rules as $rule) {
            $result = $rule->check($urlShortener);

            if (! $result['passed']) {
                return [
                    'accessible' => false,
                    'reason' => $result['reason'],
                ];
            }
        }

        return [
            'accessible' => true,
            'reason' => null,
        ];
    }
}
