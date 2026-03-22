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
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'erp_api' => [
        'key'    => env('ERP_API_KEY'),
        'secret' => env('ERP_API_SECRET'),
    ],
    'whatsapp' => [
        'token'      => env('WHATSAPP_TOKEN'),
        'phone_id'   => env('WHATSAPP_PHONE_ID'),
        'app_id'     => env('WHATSAPP_APP_ID'),
        'account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    ],
    'firebase' => [
        'credentials' => env('FIREBASE_CREDS'),
    ],
    'exchange_rate' => [
        'base_url' => env('EXCHANGE_RATE_BASE_URL'),
        'api_key'  => env('EXCHANGE_RATE_API_KEY'),
    ],
    'posthog' => [
        'api_key' => env('POSTHOG_API_KEY'),
        'host'    => env('POSTHOG_HOST', 'https://app.posthog.com'),
    ],
    'google' => [
        'routes_api_key'  => env('GOOGLE_ROUTES_API_KEY'),
        'routes_endpoint' => 'https://routes.googleapis.com',
    ],

];
