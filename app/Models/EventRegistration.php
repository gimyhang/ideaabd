<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_registrations';

    protected $fillable = [
        'event_campaign_id',
        'user_id',
        'registration_number',
        'name',
        'phone',
        'email',
        'address',
        'district',
        'thana',
        'institution_or_org',
        'designation_or_class',
        'amount_paid',
        'payment_method',
        'transaction_id',
        'payment_status',
        'form_data',
        'status',
        'admin_notes',
        'ip_address',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'form_data'   => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EventCampaign::class, 'event_campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Auto generate friendly registration number (Supports RSU sequential format RSU-2026-001)
     */
    public static function generateRegNumber($slugOrCampaign = '', ?int $campaignId = null): string
    {
        $slug = '';
        $type = '';
        if ($slugOrCampaign instanceof EventCampaign) {
            $slug = $slugOrCampaign->slug;
            $campaignId = $slugOrCampaign->id;
            $type = $slugOrCampaign->type ?? '';
        } else {
            $slug = (string) $slugOrCampaign;
        }

        $cleanSlug = strtolower(trim($slug));
        if (in_array($cleanSlug, ['rsu', 'rsutshab', 'rangpursutsab', 'writer']) || $type === 'writer') {
            $year = '2026';
            $prefix = 'RSU-' . $year . '-';

            $latest = static::where('registration_number', 'LIKE', $prefix . '%')
                ->orWhere(function($q) use ($campaignId) {
                    if ($campaignId) {
                        $q->where('event_campaign_id', $campaignId);
                    }
                })
                ->orderBy('id', 'desc')
                ->value('registration_number');

            $nextNum = 1;
            if ($latest && preg_match('/(?:RSU-\d+-|#RSU-\d+-)?(\d+)$/i', $latest, $matches)) {
                $nextNum = intval($matches[1]) + 1;
            } else {
                $count = static::where(function($q) use ($prefix, $campaignId) {
                    $q->where('registration_number', 'LIKE', $prefix . '%');
                    if ($campaignId) {
                        $q->orWhere('event_campaign_id', $campaignId);
                    }
                })->count();
                $nextNum = $count + 1;
            }

            return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $slug), 0, 4));
        if (empty($prefix)) {
            $prefix = 'EVT';
        }
        return $prefix . '-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }

    /**
     * Check if this registration belongs to a library grant campaign
     */
    public function isLibrary(): bool
    {
        return ($this->campaign && ($this->campaign->type === 'library' || $this->campaign->slug === 'pathagar' || !empty($this->campaign->form_settings['is_library_form'])))
            || !empty($this->form_data['library_name'])
            || !empty($this->form_data['is_library_grant']);
    }

    /**
     * Get allocated books count
     */
    public function getBooksAllocatedAttribute(): int
    {
        return intval($this->form_data['books_allocated'] ?? 0);
    }

    /**
     * Get dispatched date
     */
    public function getDispatchedDateAttribute(): ?string
    {
        return $this->form_data['dispatched_date'] ?? null;
    }

    /**
     * Get received books count
     */
    public function getReceivedBooksCountAttribute(): int
    {
        return intval($this->form_data['received_books_count'] ?? 0);
    }

    /**
     * Get received date
     */
    public function getReceivedDateAttribute(): ?string
    {
        return $this->form_data['received_date'] ?? null;
    }

    /**
     * Get receipt acknowledgment status
     */
    public function getAcknowledgmentStatusAttribute(): string
    {
        if (!empty($this->form_data['acknowledgment_status'])) {
            return $this->form_data['acknowledgment_status'];
        }
        if (!empty($this->form_data['received_date']) || !empty($this->form_data['received_books_count'])) {
            return 'acknowledged';
        }
        if (!empty($this->form_data['books_allocated']) || !empty($this->form_data['dispatched_date'])) {
            return 'dispatched';
        }
        return 'pending';
    }

    /**
     * Check if acknowledgment is completed
     */
    public function isAcknowledged(): bool
    {
        return $this->acknowledgment_status === 'acknowledged' || !empty($this->form_data['is_acknowledged']);
    }
}
