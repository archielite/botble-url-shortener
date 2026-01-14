<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for QR code generation using
    | the goqr.me API (https://goqr.me/api/doc/create-qr-code/)
    |
    */

    'qr-code' => [
        /*
        |--------------------------------------------------------------------------
        | Default QR Code Size
        |--------------------------------------------------------------------------
        |
        | The default size of the QR code in pixels (format: widthxheight)
        | Valid range: 10x10 to 1000x1000 for raster formats (png, gif, jpeg)
        | Valid range: 10x10 to 1000000x1000000 for vector formats (svg, eps)
        |
        */
        'size' => env('URL_SHORTENER_QR_SIZE', '300x300'),

        /*
        |--------------------------------------------------------------------------
        | Error Correction Level (ECC)
        |--------------------------------------------------------------------------
        |
        | Determines the degree of data redundancy in the QR code.
        | Options:
        | - L: Low (~7% destroyed data may be corrected) - Recommended for most uses
        | - M: Medium (~15% destroyed data may be corrected)
        | - Q: Quality (~25% destroyed data may be corrected)
        | - H: High (~30% destroyed data may be corrected)
        |
        */
        'ecc' => env('URL_SHORTENER_QR_ECC', 'L'),

        /*
        |--------------------------------------------------------------------------
        | Default Output Format
        |--------------------------------------------------------------------------
        |
        | The default file format for generated QR codes.
        | Options: png, gif, jpeg, jpg, svg, eps
        |
        */
        'format' => env('URL_SHORTENER_QR_FORMAT', 'png'),

        /*
        |--------------------------------------------------------------------------
        | Foreground Color (QR Code Color)
        |--------------------------------------------------------------------------
        |
        | Color of the QR code modules as RGB value.
        | Format: decimal (0-255)-(0-255)-(0-255) or hex (e.g., '000000' for black)
        |
        */
        'color' => env('URL_SHORTENER_QR_COLOR', '0-0-0'),

        /*
        |--------------------------------------------------------------------------
        | Background Color
        |--------------------------------------------------------------------------
        |
        | Color of the background as RGB value.
        | Format: decimal (0-255)-(0-255)-(0-255) or hex (e.g., 'FFFFFF' for white)
        |
        */
        'bgcolor' => env('URL_SHORTENER_QR_BGCOLOR', '255-255-255'),

        /*
        |--------------------------------------------------------------------------
        | Quiet Zone
        |--------------------------------------------------------------------------
        |
        | Thickness of the margin (quiet zone) in modules.
        | The QR code standard recommends a quiet zone of at least 4.
        | Valid range: 0 to 100
        |
        */
        'qzone' => env('URL_SHORTENER_QR_QZONE', 1),

        /*
        |--------------------------------------------------------------------------
        | Pixel Margin
        |--------------------------------------------------------------------------
        |
        | Thickness of an additional margin in pixels.
        | This margin is added outside the quiet zone.
        | Valid range: 0 to 50
        |
        */
        'margin' => env('URL_SHORTENER_QR_MARGIN', 1),

        /*
        |--------------------------------------------------------------------------
        | Cache Settings
        |--------------------------------------------------------------------------
        |
        | Enable/disable caching of generated QR codes
        | and set the cache TTL (Time To Live) in seconds
        |
        */
        'cache_enabled' => env('URL_SHORTENER_QR_CACHE_ENABLED', true),
        'cache_ttl' => env('URL_SHORTENER_QR_CACHE_TTL', 86400), // 24 hours
    ],
];
