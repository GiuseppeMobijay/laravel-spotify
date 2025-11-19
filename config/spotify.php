<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spotify API URL
    |--------------------------------------------------------------------------
    */
    'api_url' => env('SPOTIFY_API_URL', 'https://api.spotify.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Spotify API Credentials
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    ],

    'default_config' => [
        'country' => null,
        'locale' => null,
        'market' => null,
    ],
    /*
    |--------------------------------------------------------------------------
    | API Logging
    |--------------------------------------------------------------------------
    | Enable logging of API requests and responses
    */
    'logging' => [
        'enabled' => env('SPOTIFY_LOGGING_ENABLED', false),
        'channel' => env('SPOTIFY_LOG_CHANNEL', 'single'),
        'level' => env('SPOTIFY_LOG_LEVEL', 'info'),
        'log_request_body' => env('SPOTIFY_LOG_REQUEST_BODY', true),
        'log_response_body' => env('SPOTIFY_LOG_RESPONSE_BODY', true),
    ],

];
