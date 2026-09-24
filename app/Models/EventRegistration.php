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
     * Auto generate friendly registration number
     */
    public static function generateRegNumber(string $slug = ''): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $slug), 0, 4));
        if (empty($prefix)) {
            $prefix = 'EVT';
        }
        return $prefix . '-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }
}
