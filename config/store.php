<?php

return [
    'apple' => [
        'host' => env('STORE_APPLE_HOST'),
        'path' => [
            'verification' => env('STORE_APPLE_PATH_VERIFICATION'),
        ],
    ],
    'google' => [
        'host' => env('STORE_GOOGLE_HOST'),
        'path' => [
            'verification' => env('STORE_GOOGLE_PATH_VERIFICATION'),
        ],
    ],
];