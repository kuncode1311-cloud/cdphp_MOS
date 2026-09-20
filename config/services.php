<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'payos' => [
        'client_id' => env('PAYOS_CLIENT_ID', '8bfb42f3-2194-4060-bd73-2a7324c5e8f2'),
        'api_key' => env('PAYOS_API_KEY', 'b1a6aa76-06c1-41a7-b845-286a0503f9e7'),
        'checksum_key' => env('PAYOS_CHECKSUM_KEY', '5dbb3c6a481e5300eaca7bc99ba73bb2a652759e9ae6de73dcc0f3f6b7cad860'),
        'endpoint' => env('PAYOS_ENDPOINT', 'https://api-merchant.payos.vn'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID', ''),
    ],

];
