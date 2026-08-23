<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * দ্য ইউআরআই (URIs) যা CSRF যাচাইকরণ থেকে বাদ দেওয়া হবে।
     *
     * @var array<int, string>
     */
    protected $except = [
        'payment/bkash/callback',
        'payment/nagad/callback',
        'logout',
        '/logout',
    ];
}