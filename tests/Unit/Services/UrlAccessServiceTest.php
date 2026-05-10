<?php

namespace ArchiElite\UrlShortener\Tests\Unit\Services;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Services\Rules\ExpiredRule;
use ArchiElite\UrlShortener\Services\Rules\MaxClicksRule;
use ArchiElite\UrlShortener\Services\Rules\UrlAccessRuleInterface;
use ArchiElite\UrlShortener\Services\UrlAccessService;
use ArchiElite\UrlShortener\Tests\TestCase;
use ReflectionClass;

class UrlAccessServiceTest extends TestCase
{
    public function test_default_rules_are_registered_in_priority_order(): void
    {
        $service = new UrlAccessService();
        $rules = $this->getRegisteredRules($service);

        $this->assertCount(2, $rules);
        $this->assertInstanceOf(ExpiredRule::class, $rules[0]);
        $this->assertInstanceOf(MaxClicksRule::class, $rules[1]);
    }

    public function test_can_access_returns_true_when_all_rules_pass(): void
    {
        $service = new UrlAccessService();
        $this->resetRules($service);

        $service->addRule($this->makeRule(true));
        $service->addRule($this->makeRule(true));

        $result = $service->canAccess($this->makeFakeUrlShortener());

        $this->assertTrue($result['accessible']);
        $this->assertNull($result['reason']);
    }

    public function test_can_access_short_circuits_on_first_failure(): void
    {
        $service = new UrlAccessService();
        $this->resetRules($service);

        $service->addRule($this->makeRule(false, 'expired', 1));
        $service->addRule($this->makeRule(false, 'should-not-run', 2));

        $result = $service->canAccess($this->makeFakeUrlShortener());

        $this->assertFalse($result['accessible']);
        $this->assertSame('expired', $result['reason']);
    }

    public function test_add_rule_sorts_by_priority(): void
    {
        $service = new UrlAccessService();
        $this->resetRules($service);

        $service->addRule($this->makeRule(true, null, 5));
        $service->addRule($this->makeRule(true, null, 1));
        $service->addRule($this->makeRule(true, null, 3));

        $rules = $this->getRegisteredRules($service);

        $this->assertSame(1, $rules[0]->getPriority());
        $this->assertSame(3, $rules[1]->getPriority());
        $this->assertSame(5, $rules[2]->getPriority());
    }

    public function test_add_rule_returns_self_for_chaining(): void
    {
        $service = new UrlAccessService();

        $result = $service->addRule($this->makeRule(true));

        $this->assertSame($service, $result);
    }

    protected function makeRule(bool $passed, ?string $reason = null, int $priority = 1): UrlAccessRuleInterface
    {
        return new class ($passed, $reason, $priority) implements UrlAccessRuleInterface {
            public function __construct(
                private bool $passed,
                private ?string $reason,
                private int $priority
            ) {
            }

            public function check(UrlShortener $urlShortener): array
            {
                return ['passed' => $this->passed, 'reason' => $this->reason];
            }

            public function getPriority(): int
            {
                return $this->priority;
            }
        };
    }

    protected function makeFakeUrlShortener(): UrlShortener
    {
        $model = new UrlShortener();
        $model->short_url = 'abc123';

        return $model;
    }

    protected function resetRules(UrlAccessService $service): void
    {
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);
        $property->setValue($service, []);
    }

    protected function getRegisteredRules(UrlAccessService $service): array
    {
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        return $property->getValue($service);
    }
}
