<?php

namespace Modules\Review\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}