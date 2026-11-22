<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    public const RUNNING   = 'running';
    public const PAUSED    = 'paused';
    public const DONE      = 'done';
    public const CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::RUNNING   => 'Running',
        self::PAUSED    => 'Paused',
        self::DONE      => 'Done',
        self::CANCELLED => 'Cancelled',
    ];

    protected $guarded = ['id'];

    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'locked_until' => 'datetime',
            'last_beat_at' => 'datetime',
            'finished_at'  => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::RUNNING, self::PAUSED], true);
    }

    /** What the progress card shows. */
    public function summary(): array
    {
        $handled = $this->sent + $this->failed + $this->refused + $this->unknown;

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'mode'        => $this->mode,
            'status'      => $this->status,
            'status_bn'   => self::STATUS_LABELS[$this->status] ?? $this->status,
            'total'       => $this->total,
            'position'    => $this->position,
            'sent'        => $this->sent,
            'failed'      => $this->failed,
            'refused'     => $this->refused,
            'unknown'     => $this->unknown,
            'segments'    => $this->segments,
            'percent'     => $this->total > 0 ? (int) floor(min($this->position, $this->total) * 100 / $this->total) : 100,
            'handled'     => $handled,
            'last_error'  => $this->last_error,
            'stalled'     => $this->status === self::RUNNING && $this->last_beat_at && $this->last_beat_at->lt(now()->subMinutes(3)),
            'started_at'  => $this->created_at?->format('d M, h:i A'),
            'finished_at' => $this->finished_at?->format('d M, h:i A'),
            'admin_name'  => $this->admin_name,
        ];
    }
}
