<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Formatter\JsonFormatter;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    'channels' => [

        // ১. মাল্টি-চ্যানেল স্ট্যাক (লগ ফাইল + ইনস্ট্যান্ট অ্যালার্ট)
        'stack' => [
            'driver' => 'stack',
            'channels' => array_filter(explode(',', (string) env('LOG_STACK', 'daily,discord'))),
            'ignore_exceptions' => false,
        ],

        // ২. সিকিউরিটি ও অডিট চ্যানেল (ইউজার অ্যাক্টিভিটি, পেমেন্ট ও সেনসিটিভ চেঞ্জ ট্র্যাকিং)
        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'),
            'level' => 'info',
            'days' => env('LOG_AUDIT_DAYS', 90), // ৯০ দিন অডিট ডেটা সংরক্ষণ করবে
            'permission' => 0640,
        ],

        // ৩. সেন্ট্রালাইজড এনালিটিক্স ও JSON লগার
        'json' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel-json.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => 14,
            'formatter' => JsonFormatter::class,
            'permission' => 0640,
        ],

        // ৪. রিয়েল-টাইম ক্র্যাশ অ্যালার্ট (Discord Webhook Integration)
        'discord' => [
            'driver' => 'slack',
            'url' => env('LOG_DISCORD_WEBHOOK_URL'),
            'username' => env('APP_NAME', 'IdeaABD').' Emergency Bot',
            'emoji' => ':warning:',
            'level' => 'critical', // শুধুমাত্র ক্রিটিক্যাল ইমার্জেন্সি হলে নোটিফিকেশন যাবে
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'permission' => 0640,
            'replace_placeholders' => true,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'permission' => 0640,
            'replace_placeholders' => true,
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'error'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];