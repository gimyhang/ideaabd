<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    protected $fillable = [
        'channel',
        'recipient',
        'trigger_event',
        'subject',
        'status',
        'error_message',
    ];
}
