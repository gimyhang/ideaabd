<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Courier Provider
    |--------------------------------------------------------------------------
    */
    'default' => env('DEFAULT_COURIER', 'steadfast'),

    /*
    |--------------------------------------------------------------------------
    | Global Delivery Charges Configuration (Fallback)
    |--------------------------------------------------------------------------
    */
    'delivery_charges' => [
        'inside_dhaka'   => (int) env('SHIPPING_INSIDE_DHAKA', 60),
        'outside_dhaka'  => (int) env('SHIPPING_OUTSIDE_DHAKA', 120),
        'sub_dhaka'      => (int) env('SHIPPING_SUB_DHAKA', 100),
        'inside_rangpur' => (int) env('SHIPPING_INSIDE_RANGPUR', 40), // লোকালাইজড শিপিং
    ],

    /*
    |--------------------------------------------------------------------------
    | All Courier Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [

        // ১. Steadfast Courier (API Integrated)
        'steadfast' => [
            'name'       => 'Steadfast Courier',
            'api_key'    => env('STEADFAST_API_KEY'),
            'secret_key' => env('STEADFAST_SECRET_KEY'),
            'base_url'   => env('STEADFAST_BASE_URL', 'https://portal.steadfast.com.bd/api/v1'),
            'is_active'  => env('STEADFAST_ACTIVE', true),
            'has_api'    => true,
        ],

        // ২. Pathao Courier (API Integrated)
        'pathao' => [
            'name'          => 'Pathao Courier',
            'client_id'     => env('PATHAO_CLIENT_ID'),
            'client_secret' => env('PATHAO_CLIENT_SECRET'),
            'username'      => env('PATHAO_USERNAME'),
            'password'      => env('PATHAO_PASSWORD'),
            'store_id'      => env('PATHAO_STORE_ID'),
            'base_url'      => env('PATHAO_BASE_URL', 'https://api-hermes.pathao.com'),
            'is_active'     => env('PATHAO_ACTIVE', false),
            'has_api'       => true,
        ],

        // ৩. RedX Courier (API Integrated)
        'redx' => [
            'name'      => 'RedX Courier',
            'api_token' => env('REDX_API_TOKEN'),
            'base_url'  => env('REDX_BASE_URL', 'https://openapi.redx.com.bd/v1.0.0'),
            'is_active' => env('REDX_ACTIVE', false),
            'has_api'   => true,
        ],

        // ৪. Paperfly Courier (API Integrated)
        'paperfly' => [
            'name'      => 'Paperfly Courier',
            'username'  => env('PAPERFLY_USERNAME'),
            'password'  => env('PAPERFLY_PASSWORD'),
            'key'       => env('PAPERFLY_KEY'),
            'base_url'  => env('PAPERFLY_BASE_URL', 'https://api.paperfly.com.bd'),
            'is_active' => env('PAPERFLY_ACTIVE', false),
            'has_api'   => true,
        ],

        // ৫. eCourier (API Integrated)
        'ecourier' => [
            'name'       => 'eCourier',
            'user_id'    => env('ECOURIER_USER_ID'),
            'api_key'    => env('ECOURIER_API_KEY'),
            'api_secret' => env('ECOURIER_API_SECRET'),
            'base_url'   => env('ECOURIER_BASE_URL', 'https://staging.ecourier.com.bd/api'),
            'is_active'  => env('ECOURIER_ACTIVE', false),
            'has_api'    => true,
        ],

        // ৬. Sundarban Courier Service (Manual / API System)
        'sundarban' => [
            'name'      => 'Sundarban Courier Service',
            'api_key'   => env('SUNDARBAN_API_KEY'),
            'is_active' => env('SUNDARBAN_ACTIVE', true),
            'has_api'   => false, // ম্যানুয়াল ট্র্যাকিং ও বুকিং সাপোর্ট
        ],

        // ৭. SA Paribahan Courier (Manual Tracking System)
        'sa_paribahan' => [
            'name'      => 'SA Paribahan Courier Service',
            'is_active' => env('SA_PARIBAHAN_ACTIVE', true),
            'has_api'   => false,
        ],

        // ৮. Korotoa Courier Service (Manual Tracking System)
        'korotoa' => [
            'name'      => 'Korotoa Courier Service',
            'is_active' => env('KOROTOA_ACTIVE', true),
            'has_api'   => false,
        ],

        // ৯. ZapShift / Delivree (API Integrated)
        'zapshift' => [
            'name'      => 'ZapShift Courier',
            'api_key'   => env('ZAPSHIFT_API_KEY'),
            'base_url'  => env('ZAPSHIFT_BASE_URL', 'https://api.zapshift.com'),
            'is_active' => env('ZAPSHIFT_ACTIVE', false),
            'has_api'   => true,
        ],

        // ১০. Janani Express Courier (Manual Tracking System)
        'janani' => [
            'name'      => 'Janani Express Courier',
            'is_active' => env('JANANI_ACTIVE', false),
            'has_api'   => false,
        ],

        // ১১. Biddyut Courier (Manual System)
        'biddyut' => [
            'name'      => 'Biddyut Courier Service',
            'is_active' => env('BIDDYUT_ACTIVE', false),
            'has_api'   => false,
        ],

        // ১২. Continental Courier Service (Manual System)
        'continental' => [
            'name'      => 'Continental Courier Service',
            'is_active' => env('CONTINENTAL_ACTIVE', false),
            'has_api'   => false,
        ],

        // ১৩. Bangladesh Parcel & Post Office (SME Post)
        'post_office' => [
            'name'      => 'Bangladesh Post Office (GPO Parcel)',
            'is_active' => env('POST_OFFICE_ACTIVE', false),
            'has_api'   => false,
        ],
    ],

];