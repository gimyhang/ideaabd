<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A material the press buys and keeps (paper, plates, ink…) or a service it pays for (printing, binding bills). */
class RawMaterial extends Model
{
    protected $fillable = ['name', 'group', 'unit', 'spec', 'reorder_level', 'notes', 'is_active', 'key'];

    protected $casts = [
        'reorder_level' => 'decimal:3',
        'is_active'     => 'boolean',
    ];

    /** Groups: key => [label, is kept in stock]. */
    public const GROUPS = [
        'paper'      => ['কাগজ', true],
        'board'      => ['বোর্ড / আর্টকার্ড', true],
        'plate'      => ['প্লেট', true],
        'ink'        => ['কালি', true],
        'binding'    => ['বাঁধাই উপকরণ', true],
        'lamination' => ['ল্যামিনেশন উপকরণ', true],
        'packaging'  => ['প্যাকেজিং', true],
        'other'      => ['অন্যান্য', true],
        'service'    => ['সেবা / বিল (মজুদ নয়)', false],
    ];

    public const UNITS = ['রিম', 'শিট', 'পিস', 'কেজি', 'লিটার', 'রোল', 'প্যাকেট', 'সেট', 'বান্ডিল', 'ফর্মা', 'কপি'];

    public function moves(): HasMany
    {
        return $this->hasMany(RawMaterialMove::class);
    }

    public function isStocked(): bool
    {
        return self::GROUPS[$this->group][1] ?? true;
    }

    public function groupLabel(): string
    {
        return self::GROUPS[$this->group][0] ?? $this->group;
    }

    public function label(): string
    {
        return trim($this->name . ($this->spec ? ' — ' . $this->spec : ''));
    }
}
