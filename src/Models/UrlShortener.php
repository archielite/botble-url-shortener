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
        'expired_at',
        'max_clicks',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'expired_at' => 'datetime',
        'max_clicks' => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expired_at !== null
            && $this->expired_at->isPast();
    }
}
