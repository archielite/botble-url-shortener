<?php

namespace ArchiElite\UrlShortener\Rules;

use ArchiElite\UrlShortener\Models\Analytics;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxClicksNotLessThanCurrent implements ValidationRule
{
    protected string $shortUrl;

    public function __construct(string $shortUrl)
    {
        $this->shortUrl = $shortUrl;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentClicks = Analytics::getClicks($this->shortUrl);

        if ($value < $currentClicks) {
            $fail(trans('plugins/url-shortener::url-shortener.max_clicks_min', [
                'attribute' => $attribute,
                'current' => $currentClicks,
            ]));
        }
    }
}
