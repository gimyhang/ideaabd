<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, Argon2id is used as it is
    | the OWASP recommended password hashing algorithm.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', defined('PASSWORD_ARGON2ID') ? 'argon2id' : 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options for when the Bcrypt
    | algorithm is used. You may customize the cost factor (rounds)
    | used to generate the password hashes (OWASP recommends 12+).
    |
    */

    'bcrypt' => [
        'rounds' => (int) env('BCRYPT_ROUNDS', 12),
        'verify' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon2 Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options for when the Argon
    | algorithm is used. The memory, time, and threads parameters
    | are configured according to OWASP security guidelines.
    |
    */

    'argon' => [
        'memory'  => (int) env('ARGON_MEMORY', 65536), // 64 MB
        'threads' => (int) env('ARGON_THREADS', 2),
        'time'    => (int) env('ARGON_TIME', 4),
        'verify'  => false,
    ],

];
