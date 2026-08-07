<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | "local" এর পরিবর্তে প্রোডাকশনের জন্য ".env" থেকে ডাইনামিকভাবে লোড হবে।
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => [

        // প্রাইভেট ফাইল স্টোরেজ (যেমন: ইউজার ডকুমেন্টস, ইনভয়েস)
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false, // সিকিউরিটির জন্য সরাসরি ওয়েব দিয়ে প্রাইভেট ফাইল সার্ভ বন্ধ রাখা হয়েছে
            'throw' => env('FILESYSTEM_THROW_EXCEPTIONS', true), // আপলোড ব্যর্থ হলে এক্সেপশন থ্রো করবে
            'report' => true,
        ],

        // পাবলিক ফাইল স্টোরেজ (যেমন: প্রোফাইল পিকচার, প্রোডাক্ট ইমেজ)
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => env('FILESYSTEM_THROW_EXCEPTIONS', true),
            'report' => true,
        ],

        // AWS S3 বা Cloudflare R2 অবজেক্ট স্টোরেজ কনফিগারেশন
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => env('FILESYSTEM_THROW_EXCEPTIONS', true),
            'report' => true,
            'stream_reads' => true, // লার্জ ফাইল রিড অপটিমাইজেশন
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | `php artisan storage:link` কমান্ডের সময় তৈরি হওয়া সিম্বলিক লিংকসমূহ।
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];