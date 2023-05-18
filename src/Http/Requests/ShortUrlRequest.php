<?php

namespace ArchiElite\UrlShortener\Http\Requests;

use ArchiElite\UrlShortener\Models\ShortUrl;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShortUrlRequest extends Request
{
    public function rules(): array
    {
        return [
            'long_url' => ['required', 'url', 'max:255'],
            'short_url' => [
                'nullable',
                'min:4',
                'max:15',
                'regex:/^(?=[^ ])[A-Za-z0-9-_]+$/',
                Rule::unique(ShortUrl::class, 'short_url')->ignore($this->route('url_shortener')),
            ],
        ];
    }
}
