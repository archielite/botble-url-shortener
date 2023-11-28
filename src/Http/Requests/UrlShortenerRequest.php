<?php

namespace ArchiElite\UrlShortener\Http\Requests;

use ArchiElite\UrlShortener\Models\UrlShortener;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UrlShortenerRequest extends Request
{
    public function rules(): array
    {
        return [
            'long_url' => ['required', 'url', 'max:255'],
            'short_url' => [
                'nullable',
                'min:4',
                'max:30',
                'regex:/^(?=[^ ])[A-Za-z0-9-_]+$/',
                Rule::unique(UrlShortener::class, 'short_url')->ignore($this->route('url_shortener')),
            ],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
