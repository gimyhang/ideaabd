<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways (bKash & Nagad)
    |--------------------------------------------------------------------------
    */

    'bkash' => [
        'sandbox'     => env('BKASH_SANDBOX', true),
        'base_url'    => env('BKASH_SANDBOX', true)
            ? env('BKASH_SANDBOX_BASE_URL', 'https://tokenized.sandbox.bKash.com/v1.2.0-beta')
            : env('BKASH_LIVE_BASE_URL', 'https://tokenized.pay.bKash.com/v1.2.0-beta'),
        'app_key'     => env('BKASH_APP_KEY'),
        'app_secret'  => env('BKASH_APP_SECRET'),
        'username'    => env('BKASH_USERNAME'),
        'password'    => env('BKASH_PASSWORD'),
        'callback_url'=> env('BKASH_CALLBACK_URL'),
    ],

    'nagad' => [
        'sandbox'         => env('NAGAD_SANDBOX', true),
        'base_url'        => env('NAGAD_SANDBOX', true)
            ? env('NAGAD_SANDBOX_BASE_URL', 'https://sandbox.mynagad.com:20002/api/dfs')
            : env('NAGAD_LIVE_BASE_URL', 'https://api.mynagad.com/api/dfs'),
        'merchant_id'     => env('NAGAD_MERCHANT_ID'),
        'merchant_number' => env('NAGAD_MERCHANT_NUMBER'),
        'public_key'      => env('NAGAD_PUBLIC_KEY'),
        'private_key'     => env('NAGAD_PRIVATE_KEY'),
        'callback_url'    => env('NAGAD_CALLBACK_URL'),
    ],

];