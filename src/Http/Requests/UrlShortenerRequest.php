<?php

namespace ArchiElite\UrlShortener\Http\Requests;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Rules\MaxClicksNotLessThanCurrent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UrlShortenerRequest extends Request
{
    public function rules(): array
    {
        return [
            'long_url' => ['required', 'url', 'max:1000'],
            'short_url' => [
                'nullable',
                'min:4',
                'max:30',
                'regex:/^(?=[^ ])[A-Za-z0-9-_]+$/',
                Rule::unique(UrlShortener::class, 'short_url')->ignore($this->route('url_shortener')),
            ],
            'status' => Rule::in(BaseStatusEnum::values()),
            'expired_at' => ['nullable', 'date', 'after:now'],
            'max_clicks' => [
                'nullable',
                'integer',
                'min:1',
                new MaxClicksNotLessThanCurrent($this->route('url_shortener')->short_url),
            ],
        ];
    }
}
