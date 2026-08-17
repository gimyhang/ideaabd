<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * Shared approve / reject workflow for user-submittable content.
 *
 * The backing columns come from the add_moderation_to_content_tables migration.
 * Every query helper first checks the column exists so a deployment that has not
 * run the migration yet keeps rendering instead of throwing a QueryException.
 */
trait Moderatable
{
    public const MOD_PENDING  = 'pending';
    public const MOD_APPROVED = 'approved';
    public const MOD_REJECTED = 'rejected';

    /** @return array<string, string> status => Bangla label */
    public static function moderationLabels(): array
    {
        return [
            self::MOD_PENDING  => 'অপেক্ষমাণ',
            self::MOD_APPROVED => 'অনুমোদিত',
            self::MOD_REJECTED => 'বাতিল',
        ];
    }

    public function hasModerationColumns(): bool
    {
        return Schema::hasColumn($this->getTable(), 'mod_status');
    }

    /** Only rows an ordinary visitor is allowed to see. */
    public function scopeApproved(Builder $query): Builder
    {
        if (! $this->hasModerationColumns()) {
            return $query;
        }

        return $query->where($this->getTable() . '.mod_status', self::MOD_APPROVED);
    }

    public function scopeAwaitingReview(Builder $query): Builder
    {
        if (! $this->hasModerationColumns()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable() . '.mod_status', self::MOD_PENDING);
    }

    public function scopeModerationStatus(Builder $query, ?string $status): Builder
    {
        if (! $status || ! $this->hasModerationColumns()) {
            return $query;
        }

        return $query->where($this->getTable() . '.mod_status', $status);
    }

    public function isPendingReview(): bool
    {
        return $this->mod_status === self::MOD_PENDING;
    }

    public function isApprovedContent(): bool
    {
        // Rows created before moderation existed have no status and stay visible.
        return in_array($this->mod_status, [null, '', self::MOD_APPROVED], true);
    }

    public function isRejectedContent(): bool
    {
        return $this->mod_status === self::MOD_REJECTED;
    }

    public function markApproved(?int $reviewerId = null): bool
    {
        return $this->forceFill([
            'mod_status'       => self::MOD_APPROVED,
            'reviewed_by'      => $reviewerId,
            'reviewed_at'      => now(),
            'rejection_reason' => null,
        ])->save();
    }

    public function markRejected(?string $reason = null, ?int $reviewerId = null): bool
    {
        return $this->forceFill([
            'mod_status'       => self::MOD_REJECTED,
            'reviewed_by'      => $reviewerId,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ])->save();
    }

    /**
     * Who this entry belongs to in the real world.
     *
     * For an offline contributor the admin types their name into `owner_name`
     * and there is no user account to point at, so fall back to that.
     */
    public function creditedTo(): string
    {
        return $this->owner_name
            ?: ($this->submitter?->name ?? '—');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
