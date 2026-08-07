<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DRM Driver / Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "local", "vdocipher", "widevine", "custom"
    |
    */
    'default' => env('DRM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Content Protection & Watermarking Settings
    |--------------------------------------------------------------------------
    */
    'watermark' => [
        'enabled'      => env('DRM_WATERMARK_ENABLED', true),
        'text'         => env('DRM_WATERMARK_TEXT', env('APP_NAME', 'IdeaABD')),
        'show_user_id' => env('DRM_WATERMARK_SHOW_USER', true), // ডিসপ্লেতে ইউজারের ID/ইমেইল ওয়াটারমার্ক করা
        'opacity'      => env('DRM_WATERMARK_OPACITY', 0.3),
        'position'     => env('DRM_WATERMARK_POSITION', 'bottom-right'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Secure Signed URL & Access Expiry
    |--------------------------------------------------------------------------
    */
    'security' => [
        'signed_url'         => true,
        'link_expiration'    => env('DRM_LINK_EXPIRATION_MINUTES', 60), // ভিডিও/ফাইল ডাউনলোড লিংকের মেয়াদ (মিনিট)
        'prevent_download'   => env('DRM_PREVENT_DOWNLOAD', true),     // ব্রাউজার ডাউনলোড ব্লক করা
        'max_device_limit'   => env('DRM_MAX_DEVICES', 2),             // প্রতি ইউজার একসাথে কতটি ডিভাইসে দেখতে পারবে
    ],

    /*
    |--------------------------------------------------------------------------
    | DRM Providers Credentials & Endpoint Settings
    |--------------------------------------------------------------------------
    */
    'providers' => [

        // ১. Local Custom Encryption System
        'local' => [
            'secret_key'     => env('DRM_LOCAL_SECRET', env('APP_KEY')),
            'cipher_alg'     => 'AES-256-CBC',
        ],

        // ২. VdoCipher (Advanced Video DRM)
        'vdocipher' => [
            'api_secret'     => env('VDOCIPHER_API_SECRET'),
            'base_url'       => 'https://dev.vdocipher.com/api',
            'is_active'      => env('VDOCIPHER_ACTIVE', false),
        ],

        // ৩. Google Widevine DRM Integration
        'widevine' => [
            'license_url'    => env('WIDEVINE_LICENSE_URL'),
            'merchant_name'  => env('WIDEVINE_MERCHANT_NAME'),
            'is_active'      => env('WIDEVINE_ACTIVE', false),
        ],
    ],

];