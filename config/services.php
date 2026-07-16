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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'planning-center' => [
        'client_id'     => env('PLANNING_CENTER_CLIENT_ID'),
        'client_secret' => env('PLANNING_CENTER_CLIENT_SECRET'),
        'redirect'      => env('PLANNING_CENTER_REDIRECT_URI'),
    ],

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
        'channel_id' => env('YOUTUBE_CHANNEL_ID'),
        'webhook_secret' => env('YOUTUBE_WEBHOOK_SECRET'),
    ],

    'internal' => [
        'task_secret' => env('INTERNAL_TASK_SECRET'),
    ],

    'instagram' => [
        'client_id'     => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect'      => env('INSTAGRAM_REDIRECT_URI'),
        'cache_ttl'     => env('INSTAGRAM_FEED_CACHE_TTL', 1800),
    ],
];
