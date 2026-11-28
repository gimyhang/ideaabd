<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** What the admin did with an inquiry: sms | email | call | whatsapp | note. */
class ContactInquiryReply extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['inquiry_id', 'channel', 'body', 'ok', 'error', 'admin_id', 'admin_name'];

    protected $casts = ['ok' => 'boolean'];
}
