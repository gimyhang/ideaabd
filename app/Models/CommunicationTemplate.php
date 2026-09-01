<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    protected $fillable = [
        'name',
        'trigger_event',
        'channel',
        'subject',
        'content_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
