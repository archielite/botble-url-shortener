<?php

namespace ArchiElite\ShortenerUrl\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class ShortUrl extends BaseModel
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
