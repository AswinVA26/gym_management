<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The UI is served same-origin, so broad origins are NOT required.
    | Only traffic from the application's own origin is permitted.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_SCHEME).'://'.parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
