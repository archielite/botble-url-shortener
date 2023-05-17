<?php

return [
    [
        'name' => 'Short Url',
        'flag' => 'short_url.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'short_url.create',
        'parent_flag' => 'short_url.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'short_url.edit',
        'parent_flag' => 'short_url.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'short_url.destroy',
        'parent_flag' => 'short_url.index',
    ],
];
