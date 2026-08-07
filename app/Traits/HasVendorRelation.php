<?php

namespace App\Traits;

use Modules\Vendor\Models\Vendor;

trait HasVendorRelation
{
    /**
     * Vendor Relationship
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}