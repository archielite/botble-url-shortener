<?php

return [
    [
        'name' => 'Short URL',
        'flag' => 'url_shortener.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'url_shortener.create',
        'parent_flag' => 'url_shortener.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'url_shortener.edit',
        'parent_flag' => 'url_shortener.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'url_shortener.destroy',
        'parent_flag' => 'url_shortener.index',
    ],
    [
        'name' => 'Analytics',
        'flag' => 'url_shortener.analytics',
        'parent_flag' => 'url_shortener.index',
    ],
];
