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
        'has_previous_books',
        'previous_books_year',
        'previous_books_count',
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
        'amount_paid'          => 'decimal:2',
        'form_data'            => 'array',
        'previous_books_count' => 'integer',
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

    /**
     * Check if participant is a Little Magazine Editor (লিটিলম্যাগ সম্পাদক)
     */
    public function isLittleMagEditor(): bool
    {
        if (!empty($this->form_data['magazine_name'])) {
            return true;
        }

        $cats = (array) ($this->form_data['author_categories'] ?? []);
        foreach ($cats as $cat) {
            $catStr = (string) $cat;
            if (mb_strpos($catStr, 'লিটিলম্যাগ') !== false || mb_strpos($catStr, 'সম্পাদক') !== false) {
                return true;
            }
        }

        $singleCat = (string) ($this->form_data['author_category'] ?? '');
        if (mb_strpos($singleCat, 'লিটিলম্যাগ') !== false) {
            return true;
        }

        $desig = (string) ($this->designation_or_class ?? '');
        if (mb_strpos($desig, 'লিটিলম্যাগ') !== false || mb_strpos($desig, 'সম্পাদক') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get Little Magazine name
     */
    public function getMagazineNameAttribute(): ?string
    {
        return !empty($this->form_data['magazine_name']) ? trim($this->form_data['magazine_name']) : null;
    }

    /**
     * Get Little Magazine issue/volume count
     */
    public function getMagazineIssueCountAttribute(): ?string
    {
        return !empty($this->form_data['magazine_issue_count']) ? trim($this->form_data['magazine_issue_count']) : null;
    }

    /**
     * Get resolved District
     */
    public function getResolvedDistrictAttribute(): string
    {
        $dist = trim($this->district ?? '');
        if (empty($dist)) {
            $dist = trim($this->form_data['district'] ?? $this->form_data['perm_district'] ?? '');
        }
        return !empty($dist) ? $dist : 'অনির্ধারিত জেলা';
    }

    /**
     * Get resolved Division
     */
    public function getResolvedDivisionAttribute(): string
    {
        $div = trim($this->form_data['perm_division'] ?? $this->form_data['division'] ?? '');
        if (!empty($div)) {
            return $div;
        }

        $dist = $this->resolved_district;
        if ($dist && $dist !== 'অনির্ধারিত জেলা') {
            $map = [
                'খুলনা' => ['কুষ্টিয়া', 'খুলনা', 'চুয়াডাঙ্গা', 'ঝিনাইদহ', 'নড়াইল', 'বাগেরহাট', 'মাগুরা', 'মেহেরপুর', 'যশোর', 'সাতক্ষীরা'],
                'চট্টগ্রাম' => ['কক্সবাজার', 'কুমিল্লা', 'খাগড়াছড়ি', 'চট্টগ্রাম', 'চাঁদপুর', 'নোয়াখালী', 'বান্দরবান', 'ব্রাহ্মণবাড়িয়া', 'লক্ষ্মীপুর', 'রাঙ্গামাটি', 'ফেনী'],
                'ঢাকা' => ['কিশোরগঞ্জ', 'গাজীপুর', 'গোপালগঞ্জ', 'টাঙ্গাইল', 'ঢাকা', 'নরসিংদী', 'নারায়ণগঞ্জ', 'ফরিদপুর', 'মাদারীপুর', 'মানিকগঞ্জ', 'মুন্সীগঞ্জ', 'রাজবাড়ী', 'শরীয়তপুর'],
                'বরিশাল' => ['ঝালকাঠি', 'পটুয়াখালী', 'পিরোজপুর', 'বরগুনা', 'বরিশাল', 'ভোলা'],
                'ময়মনসিংহ' => ['জামালপুর', 'নেত্রকোণা', 'ময়মনসিংহ', 'শেরপুর'],
                'রংপুর' => ['কুড়িগ্রাম', 'গাইবান্ধা', 'ঠাকুরগাঁও', 'দিনাজপুর', 'নীলফামারী', 'পঞ্চগড়', 'রংপুর', 'লালমনিরহাট'],
                'রাজশাহী' => ['চাঁপাইনবাবগঞ্জ', 'জয়পুরহাট', 'নওগাঁ', 'নাটোর', 'পাবনা', 'বগুড়া', 'রাজশাহী', 'সিরাজগঞ্জ'],
                'সিলেট' => ['মৌলভীবাজার', 'সুনামগঞ্জ', 'সিলেট', 'হবিগঞ্জ']
            ];

            foreach ($map as $dName => $dists) {
                foreach ($dists as $d) {
                    if (mb_strpos($dist, $d) !== false || mb_strpos($d, $dist) !== false) {
                        return $dName;
                    }
                }
            }
        }

        return 'অনির্ধারিত বিভাগ';
    }

    /**
     * Get resolved Thana / Upazila
     */
    public function getResolvedThanaAttribute(): ?string
    {
        $thana = trim($this->thana ?? '');
        if (empty($thana)) {
            $thana = trim($this->form_data['thana'] ?? $this->form_data['perm_thana'] ?? '');
        }
        return !empty($thana) ? $thana : null;
    }
}
