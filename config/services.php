<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    'whatsapp' => [

        /*
        |--------------------------------------------------------------------------
        | WhatsApp Cloud API
        |--------------------------------------------------------------------------
        */

        'api_version' => env(
            'WHATSAPP_API_VERSION',
            'v21.0'
        ),

        'phone_number_id' => env(
            'WHATSAPP_PHONE_NUMBER_ID'
        ),

        'access_token' => env(
            'WHATSAPP_ACCESS_TOKEN'
        ),

        'verify_token' => env(
            'WHATSAPP_VERIFY_TOKEN'
        ),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

];
