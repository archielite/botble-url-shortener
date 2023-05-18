<?php

namespace ArchiElite\UrlShortener\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class UrlShortener extends BaseModel
{
    protected $table = 'short_urls';

    protected $fillable = [
        'long_url',
        'short_url',
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
}
