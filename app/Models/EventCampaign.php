<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_campaigns';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'badge_text',
        'short_description',
        'description',
        'banner_image',
        'theme_color',
        'has_fee_or_donation',
        'fee_amount',
        'is_donation_flexible',
        'min_donation',
        'payment_methods',
        'payment_instructions',
        'custom_fields',
        'form_settings',
        'table_settings',
        'starts_at',
        'ends_at',
        'max_participants',
        'is_active',
        'success_message',
        'redirect_url',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'has_fee_or_donation'  => 'boolean',
        'is_donation_flexible' => 'boolean',
        'fee_amount'           => 'decimal:2',
        'min_donation'         => 'decimal:2',
        'custom_fields'        => 'array',
        'form_settings'        => 'array',
        'table_settings'       => 'array',
        'is_active'            => 'boolean',
        'starts_at'            => 'datetime',
        'ends_at'              => 'datetime',
        'max_participants'     => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_campaign_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/' . ltrim($this->slug, '/'));
    }

    public function isExpired(): bool
    {
        if ($this->ends_at && now()->isAfter($this->ends_at)) {
            return true;
        }
        return false;
    }

    public function isFull(): bool
    {
        if ($this->max_participants && $this->registrations()->count() >= $this->max_participants) {
            return true;
        }
        return false;
    }

    /**
     * Header / logo settings of the scholarship form, with English-only text and safe defaults.
     * One source for the application page, its A4 preview, the print, the PDF and the success page.
     */
    public function scholarshipHeader(): array
    {
        $fs = is_array($this->form_settings) ? $this->form_settings : [];
        $en = fn ($v, string $default = '') => (is_string($v) && trim($v) !== '' && !preg_match('/\p{Bengali}/u', $v)) ? trim($v) : $default;
        $side = in_array($fs['sch_logo_side'] ?? 'left', ['left', 'center', 'hidden'], true) ? ($fs['sch_logo_side'] ?? 'left') : 'left';

        return [
            'title'      => $en($fs['sch_title'] ?? null, 'Joyee Shikkhabritti'),
            'heading'    => $en($fs['sch_heading'] ?? null, 'Scholarship'),
            'session'    => $en($fs['sch_session'] ?? null, 'Session 2027'),
            'org'        => $en($fs['form_org'] ?? null),
            'subhead'    => $en($fs['form_subhead'] ?? null),
            'venue'      => $en($fs['form_venue'] ?? null),
            'date'       => $en($fs['form_date'] ?? null),
            'copy_tag'   => $en($fs['form_copy_tag'] ?? null),
            'logo_side'  => $side,
            'logo_size'  => max(30, min(120, (int) ($fs['logo_size'] ?? 60))),
            'logo_image' => $fs['logo_image'] ?? null,
        ];
    }

    public function canAcceptRegistrations(): bool
    {
        return $this->is_active && !$this->isExpired() && !$this->isFull();
    }

    public const KIND_SCHOLARSHIP = 'scholarship';
    public const KIND_LIBRARY     = 'library';
    public const KIND_WRITER      = 'writer';
    public const KIND_EVENT       = 'event';

    /**
     * Old public addresses per kind. They only decide which campaign an old link opens; what a campaign IS
     * comes from its own type/flags (see kind()), so renaming a slug never turns one form into another.
     */
    public const LEGACY_SLUGS = [
        self::KIND_SCHOLARSHIP => ['jshikkhabritti', 'scholarship', 'shikkhabritti'],
        self::KIND_WRITER      => ['rsu', 'rsutshab', 'rangpursutsab'],
        self::KIND_LIBRARY     => ['pathagar', 'library', 'boi-bitoron', 'library-grant', 'pathagar-nibondhon'],
    ];

    /**
     * What this campaign is. Exactly one answer, decided in a fixed order from the campaign's own data:
     * scholarship, then library, then writer, otherwise a plain event. Campaigns saved before the type/flags
     * existed are recognised by their original slug.
     */
    public function kind(): string
    {
        $fs = is_array($this->form_settings) ? $this->form_settings : [];
        $slug = (string) $this->slug;

        if ($this->type === 'scholarship' || !empty($fs['is_scholarship_form']) || $slug === 'jshikkhabritti') {
            return self::KIND_SCHOLARSHIP;
        }
        if ($this->type === 'library' || !empty($fs['is_library_form']) || $slug === 'pathagar') {
            return self::KIND_LIBRARY;
        }
        if ($this->type === 'writer' || !empty($fs['is_writer_form']) || in_array($slug, self::LEGACY_SLUGS[self::KIND_WRITER], true)) {
            return self::KIND_WRITER;
        }

        return self::KIND_EVENT;
    }

    public function isScholarship(): bool { return $this->kind() === self::KIND_SCHOLARSHIP; }
    public function isLibrary(): bool     { return $this->kind() === self::KIND_LIBRARY; }
    public function isWriter(): bool      { return $this->kind() === self::KIND_WRITER; }

    /** Scholarship, library and writer applications are reviewed by an admin before a card or slip is issued. */
    public function requiresApproval(): bool
    {
        return $this->kind() !== self::KIND_EVENT || !empty($this->form_settings['requires_approval']);
    }

    /**
     * The campaign a public address belongs to: the campaign with exactly that slug, else (for an old address)
     * the first campaign of the kind that address stood for. Active campaigns win over closed ones.
     * With $create, an old address whose kind has no campaign at all gets a default one - unless the admin
     * deleted a campaign under that slug, which stays deleted.
     */
    public static function resolve(string $slug, bool $create = false): ?self
    {
        $exact = static::where('slug', $slug)->first();
        if ($exact) {
            return $exact;
        }

        $kind = collect(self::LEGACY_SLUGS)->search(fn ($slugs) => in_array($slug, $slugs, true));
        if ($kind === false) {
            return null;
        }

        $found = static::orderByDesc('is_active')->orderBy('id')->get()->first(fn (self $c) => $c->kind() === $kind);
        if ($found || !$create || static::onlyTrashed()->where('slug', $slug)->exists()) {
            return $found;
        }

        return static::create(static::defaultsFor($kind, $slug));
    }

    /** Starting campaign for an old public address that has no campaign yet. */
    protected static function defaultsFor(string $kind, string $slug): array
    {
        $base = ['slug' => $slug, 'has_fee_or_donation' => false, 'fee_amount' => 0.00, 'is_active' => true, 'custom_fields' => []];

        return $base + match ($kind) {
            self::KIND_SCHOLARSHIP => [
                'title'             => 'Joyee Shikkha Britti Application Form',
                'type'              => 'scholarship',
                'badge_text'        => 'Education Scholarship 2026',
                'short_description' => 'Higher Secondary & Bachelor\'s Education Scholarship Application Form',
                'description'       => 'Higher Secondary and Bachelor\'s students can apply for the Joyee Shikkha Britti. Please provide accurate academic details. Max 50 words reason for scholarship.',
                'theme_color'       => '#0f3a68',
                'success_message'   => 'Your scholarship application has been successfully submitted! Please print or download your application form below.',
                'form_settings'     => ['is_scholarship_form' => true],
            ],
            self::KIND_WRITER => [
                'title'             => 'রংপুর সাহিত্য উৎসব ও লেখক সমাবেশ ২০২৬',
                'type'              => 'writer',
                'badge_text'        => 'লেখক ও প্রতিনিধি নিবন্ধন',
                'short_description' => 'রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলা ২০২৬ এ লেখক নিবন্ধন ও আমন্ত্রণ কার্ড সংগ্রহ ফরম।',
                'description'       => 'উত্তরবঙ্গের সর্ববৃহৎ সাহিত্য মিলনমেলায় অংশ নিতে লেখকবৃন্দকে এই ফরম পূরণ করার জন্য আমন্ত্রণ জানানো হচ্ছে।',
                'theme_color'       => '#991b1b',
                'success_message'   => 'আপনার লেখক নিবন্ধন সফলভাবে জমা হয়েছে! এডমিন অনুমোদন দিলে মোবাইল নম্বর দিয়ে লগইন করে আমন্ত্রণ কার্ড ডাউনলোড করতে পারবেন।',
                'form_settings'     => ['is_writer_form' => true, 'requires_approval' => true],
            ],
            default => [
                'title'             => 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন ২০২৬',
                'type'              => 'library',
                'badge_text'        => 'পাঠাগার বই অনুদান ২০২৬',
                'short_description' => 'বিনামূল্যে বই বিতরণ কর্মসূচিতে অংশ নিয়ে পাঠাগার ও শিক্ষা প্রতিষ্ঠানের জন্য বই অনুদান প্রাপ্তির নিবন্ধন ফরম।',
                'description'       => 'আইডিয়া পাঠাগারের বিনামূল্যে বই বিতরণ কর্মসূচি, নিবন্ধন সম্পন্ন করুন।',
                'theme_color'       => '#047857',
                'success_message'   => 'আপনার পাঠাগারের নিবন্ধন সফলভাবে সম্পন্ন হয়েছে! বই অনুদানের তথ্য জানিয়ে দেওয়া হবে।',
                'form_settings'     => ['is_library_form' => true, 'requires_approval' => true],
            ],
        };
    }
}
