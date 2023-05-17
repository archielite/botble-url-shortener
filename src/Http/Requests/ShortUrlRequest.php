<?php

namespace ArchiElite\ShortUrl\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ShortUrlRequest extends Request
{
    public function rules(): array
    {
        return [
            'long_url' => 'required|max:255|url',
            'short_url' => 'nullable|min:4|max:15|regex:/^(?=[^ ])[A-Za-z0-9-_]+$/|unique:short_urls,short_url,' . $this->route('short_url'),
        ];
    }
}
