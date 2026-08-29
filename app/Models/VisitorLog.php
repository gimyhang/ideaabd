<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'url',
        'page_title',
        'route_name',
        'device',
        'browser',
        'os',
        'country',
        'country_code',
        'city',
        'referer',
        'traffic_source',
        'utm_source',
        'user_id',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get Emoji flag from country code.
     */
    public function getCountryFlagAttribute(): string
    {
        $code = strtoupper((string) ($this->country_code ?: 'BD'));
        if (strlen($code) !== 2) {
            return '🌐';
        }
        $flag = '';
        foreach (str_split($code) as $char) {
            $flag .= mb_chr(ord($char) + 127397, 'UTF-8');
        }
        return $flag;
    }
}
