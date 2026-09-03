<?php

namespace Modules\Author\Models;

use App\Models\Concerns\Moderatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Blog\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Author extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'name',
        'name_bn',
        'name_en',
        'slug',
        'bio',
        'avatar',
        'author_image',
        'email',
        'phone',
        'website',
        'social_links',
        'is_verified',
        'is_active',
        'user_id',
        'royalty_percentage',
        'wallet_balance',
        'total_payout_withdrawn',
        'payout_account_type',
        'payout_account_details',
        'owner_name',
        'owner_phone',
        'submitted_by',
        'mod_status',
    ];

    protected $casts = [
        'social_links'           => 'json',
        'is_verified'            => 'boolean',
        'is_active'              => 'boolean',
        'royalty_percentage'     => 'decimal:2',
        'wallet_balance'         => 'decimal:2',
        'total_payout_withdrawn' => 'decimal:2',
    ];

    protected $appends = [
        'avatar_url',
        'initials',
        'avatar_bg_color',
    ];

    /**
     * UNIFIED AUTHOR FIND OR CREATE / SYNC
     * Ensures only a SINGLE author record exists regardless of where it was initiated:
     * (Admin Directory, Book Entry, Publisher Portal, or User Registration)
     */
    public static function findOrCreateUnified(array $data): self
    {
        $name = trim($data['name'] ?? '');
        $email = !empty($data['email']) ? trim(strtolower((string) $data['email'])) : null;
        $phone = !empty($data['phone']) ? trim((string) $data['phone']) : null;
        $slug = !empty($data['slug']) ? trim(Str::slug((string) $data['slug'])) : null;
        $avatar = $data['avatar'] ?? $data['author_image'] ?? null;

        $author = null;

        // 1. Search by exact or normalized Name
        if (!empty($name)) {
            $author = self::where('name', $name)
                ->orWhere(DB::raw('TRIM(name)'), $name)
                ->first();
        }

        // 2. Search by Phone (if provided and valid)
        if (!$author && !empty($phone)) {
            $author = self::where('phone', $phone)->first();
        }

        // 3. Search by Email (if provided and valid)
        if (!$author && !empty($email)) {
            $author = self::where('email', $email)->first();
        }

        // 4. Search by Slug (if provided)
        if (!$author && !empty($slug)) {
            $author = self::where('slug', $slug)->first();
        }

        // If author already exists, enrich empty fields without overwriting valid data
        if ($author) {
            $updates = [];
            if (empty($author->email) && $email) {
                $updates['email'] = $email;
            }
            if (empty($author->phone) && $phone) {
                $updates['phone'] = $phone;
            }
            if (!empty($data['user_id']) && $author->user_id !== $data['user_id']) {
                $updates['user_id'] = $data['user_id'];
            }
            if (!empty($data['bio'])) {
                $updates['bio'] = $data['bio'];
            }
            if (!empty($avatar)) {
                $updates['avatar'] = $avatar;
            }
            if (!empty($data['website'])) {
                $updates['website'] = $data['website'];
            }
            if (!empty($data['is_verified']) && !$author->is_verified) {
                $updates['is_verified'] = true;
            }
            if (isset($data['is_active']) && $data['is_active'] && !$author->is_active) {
                $updates['is_active'] = true;
            }

            if (!empty($updates)) {
                $author->update($updates);
            }

            return $author;
        }

        // If author does NOT exist, generate unique slug and create cleanly
        if (empty($slug)) {
            $slug = self::generateUniqueSlug($name);
        }

        return self::create([
            'name'        => $name,
            'slug'        => $slug,
            'email'       => $email,
            'phone'       => $phone,
            'bio'         => $data['bio'] ?? null,
            'avatar'      => $avatar,
            'website'     => $data['website'] ?? null,
            'user_id'     => $data['user_id'] ?? null,
            'is_verified' => !empty($data['is_verified']),
            'is_active'   => $data['is_active'] ?? true,
        ]);
    }

    protected static function booted()
    {
        static::saving(function (self $author) {
            if (empty($author->slug) || preg_match('/[^\x20-\x7e]/', (string) $author->slug)) {
                $author->slug = self::generateUniqueSlug($author->name ?: 'author', $author->id);
            }
        });
    }

    /**
     * Generate a unique clean English slug for author (Bengali -> Phonetic English conversion)
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $text = trim($name);
        
        // Direct known word / name dictionary for highly natural English transliterations
        $knownWords = [
            'হুমায়ূন' => 'humayun',
            'হুমায়ূন' => 'humayun',
            'আহমেদ' => 'ahmed',
            'মুহম্মদ' => 'muhammad',
            'জাফর' => 'jafor',
            'ইকবাল' => 'iqbal',
            'কাজী' => 'kazi',
            'নজরুল' => 'nazrul',
            'ইসলাম' => 'islam',
            'রবীন্দ্রনাথ' => 'rabindranath',
            'ঠাকুর' => 'tagore',
            'শরৎচন্দ্র' => 'sharat-chandra',
            'চট্টোপাধ্যায়' => 'chattopadhyay',
            'সাকিল' => 'sakil',
            'মাসুদ' => 'masud',
            'মনির' => 'monir',
            'হোসেন' => 'hossain',
            'আব্দুর' => 'abdur',
            'রাজ্জাক' => 'rajjak',
            'আল-আমিন' => 'al-amin',
            'জাকির' => 'jakir',
            'সোহান' => 'sohan',
            'সিগমুন্ড' => 'sigmund',
            'ফ্রয়েড' => 'freud',
            'লেখক' => 'author',
            'পরীক্ষামূলক' => 'test',
            'নতুন' => 'new',
            'দ্বিতীয়' => 'second',
            'এক' => 'one',
            'দুই' => 'two',
        ];

        foreach ($knownWords as $bn => $en) {
            $text = preg_replace('/\b' . preg_quote($bn, '/') . '\b/u', $en, $text);
        }

        $bengali = [
            'ক্ষ','জ্ঞ','ঞ্চ','ঞ্জ','ঙ্ক','ঙ্গ','চ্ছ','জ্জ','ত্ত','দ্দ','ন্ত','ন্দ','ম্প','ম্ব','ম্ভ','শ্র','ত্র','গ্র','প্র','ব্র','দ্র','ক্র','ট্র','ড্র','ফ্র',
            'অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ',
            'ক','খ','গ','ঘ','ঙ','চ','ছ','জ','ঝ','ঞ','ট','ঠ','ড','ঢ','ণ','ত','থ','দ','ধ','ন','প','ফ','ব','ভ','ম','য','র','ল','শ','ষ','স','হ','ড়','ঢ়','য়','ৎ','ং','ঃ','ঁ',
            'া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ','্','্য','্র','্ব'
        ];
        $english = [
            'kkh','gya','nch','nj','nk','ng','cch','jj','tt','dd','nt','nd','mp','mb','mbh','shr','tr','gr','pr','br','dr','kr','tr','dr','fr',
            'a','a','i','i','u','u','ri','e','oi','o','ou',
            'k','kh','g','gh','ng','ch','chh','j','jh','n','t','th','d','dh','n','t','th','d','dh','n','p','f','b','bh','m','z','r','l','sh','sh','s','h','r','rh','y','t','ng','h','',
            'a','i','ee','u','oo','ri','e','oi','o','ou','','y','r','b'
        ];
        
        $converted = str_replace($bengali, $english, $text);
        $base = Str::slug($converted) ?: Str::slug(Str::random(8));
        $slug = $base;
        $count = 1;

        while (
            self::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$count);
        }

        return $slug;
    }

    /**
     * Dynamic Avatar URL with multiple fallbacks, robust path normalization & protocol safety
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->avatar 
            ?? $this->author_image 
            ?? $this->photo 
            ?? $this->image 
            ?? null;

        if (empty($avatar)) {
            return null;
        }

        $avatar = trim((string) $avatar);
        $avatar = str_replace('\\', '/', $avatar);

        // If it's inline data URI
        if (str_starts_with($avatar, 'data:image')) {
            return $avatar;
        }

        // If it's an absolute URL (http/https), check if it points to local/site storage
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            $parsed = parse_url($avatar);
            $host = $parsed['host'] ?? '';
            $path = $parsed['path'] ?? '';

            // If it belongs to local dev host (127.0.0.1/localhost) or contains /storage/, resolve dynamically
            if (in_array($host, ['127.0.0.1', 'localhost', 'ideaabd.com', 'www.ideaabd.com'], true) || str_contains($path, '/storage/')) {
                $cleanPath = ltrim($path, '/');
                if (str_starts_with($cleanPath, 'storage/')) {
                    return asset($cleanPath);
                }
                return asset('storage/' . $cleanPath);
            }

            // External third-party CDN / Gravatar / image link
            return $avatar;
        }

        // Clean leading slashes
        $cleanPath = ltrim($avatar, '/');

        // Check if path starts with storage/
        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        // Check if path starts with public/
        if (str_starts_with($cleanPath, 'public/')) {
            return asset('storage/' . substr($cleanPath, 7));
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Unicode-safe Author initials (Bengali & English)
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? '');
        if (empty($name)) {
            return 'লে';
        }

        $words = preg_split('/\s+/u', $name);
        if (count($words) >= 2) {
            return mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
        }

        return mb_substr($name, 0, 1);
    }

    /**
     * Deterministic pleasant gradient background for avatar based on ID / Name
     */
    public function getAvatarBgColorAttribute(): string
    {
        $colors = [
            'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
            'linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%)',
            'linear-gradient(135deg, #059669 0%, #10b981 100%)',
            'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
            'linear-gradient(135deg, #db2777 0%, #ec4899 100%)',
            'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)',
            'linear-gradient(135deg, #0d9488 0%, #14b8a6 100%)',
            'linear-gradient(135deg, #e11d48 0%, #f43f5e 100%)',
            'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)',
            'linear-gradient(135deg, #475569 0%, #64748b 100%)',
        ];

        $idx = abs(crc32((string) ($this->id ?? $this->name ?? '1'))) % count($colors);
        return $colors[$idx];
    }

    /**
     * Clean bio excerpt
     */
    public function getBioExcerptAttribute(): string
    {
        if (empty($this->bio)) {
            return '';
        }
        return Str::limit(strip_tags($this->bio), 120);
    }

    /**
     * Scopes for easy querying
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('bio', 'like', "%{$term}%");
        });
    }

    /**
     * Fetch ALL Blog / Ideapatra posts connected to this Author
     * Checks: direct author_id, associated user_id, submitted_by, owner_name, and owner_phone
     */
    public function getBlogPostsQuery()
    {
        $name = trim($this->name ?? '');
        $email = trim($this->email ?? '');
        $phone = trim($this->phone ?? '');
        $authorId = $this->id;

        // Find linked user IDs
        $userIds = [];
        if ($email || $phone || $name) {
            $userIds = User::where(function ($q) use ($email, $phone, $name) {
                if ($email) $q->orWhere('email', $email);
                if ($phone) $q->orWhere('phone', $phone);
                if ($name)  $q->orWhere('name', $name);
            })->pluck('id')->all();
        }

        return BlogPost::query()
            ->where('status', 'published')
            ->where(function ($q) use ($authorId, $userIds, $name, $phone) {
                $q->where('author_id', $authorId);
                if (!empty($userIds)) {
                    $q->orWhereIn('author_id', $userIds)
                      ->orWhereIn('submitted_by', $userIds);
                }
                if (!empty($name)) {
                    $q->orWhere('owner_name', $name);
                }
                if (!empty($phone)) {
                    $q->orWhere('owner_phone', $phone);
                }
            })
            ->latest('published_at')
            ->latest('id');
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    /** Printed/both-format books, linked through the book_author pivot. */
    public function books()
    {
        return $this->belongsToMany(\Modules\Book\Models\Book::class, 'book_author', 'author_id', 'book_id');
    }

    public function ebooks()
    {
        return $this->hasMany(\Modules\Ebook\Models\Ebook::class, 'author_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function royalties()
    {
        return $this->hasMany(\App\Models\AuthorRoyalty::class, 'author_id');
    }

    public function payoutRequests()
    {
        return $this->hasMany(\App\Models\AuthorPayoutRequest::class, 'author_id');
    }

    public function submissions()
    {
        return $this->hasMany(AuthorSubmission::class, 'author_id');
    }

    public function honorariums()
    {
        return $this->hasMany(\App\Models\AuthorHonorarium::class, 'author_id');
    }

    public function getPenNameAttribute(): ?string
    {
        return $this->user?->reg_data['pen_name'] ?? null;
    }

    public function getNameEnAttribute(): ?string
    {
        return $this->user?->reg_data['name_en'] ?? ($this->user?->reg_data['name_english'] ?? null);
    }

    public function getGenresListAttribute(): array
    {
        $raw = $this->user?->reg_data['genres'] ?? ($this->user?->reg_data['genre'] ?? null);
        if (is_array($raw)) return array_filter($raw);
        if (is_string($raw)) return array_filter(array_map('trim', explode(',', $raw)));
        return [];
    }
}
