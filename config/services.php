<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

     /*
    |--------------------------------------------------------------------------
    | News API Keys
    |--------------------------------------------------------------------------
    |
    | These are the API keys used for various news services. Each service requires
    | its own API key for authentication. You can obtain these keys by registering
    | at their respective platforms:
    | - NewsAPI: https://newsapi.org
    | - Guardian: https://open-platform.theguardian.com
    | - NYT: https://developer.nytimes.com
    |
    */

    'newsapi' => [
        'key' => env('NEWSAPI_KEY'),
        'base_url' => 'https://newsapi.org/v2',
    ],

    'guardian' => [
        'key' => env('GUARDIAN_API_KEY'),
        'base_url' => 'https://content.guardianapis.com',
    ],

    'nyt' => [
        'key' => env('NYT_API_KEY'),
        'base_url' => 'https://api.nytimes.com/svc',
    ],

];
