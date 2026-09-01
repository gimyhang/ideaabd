<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTranslation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'text_bn',
        'text_en',
        'text_ar',
    ];
}
