<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*','storage/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

     'allowed_origins' => [
        env('FRONTEND_URL'),
        'https://landing.sdmuhammadiyah3smd.com',
        'https://www.sdmuhammadiyah3smd.com',
        'https://sdmuhammadiyah3smd.com'
    ],
    

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-Access-Token',
        'Content-Disposition',
        'Content-Length'
    ],

    'max_age' => 600,

    'supports_credentials' => true,

];
