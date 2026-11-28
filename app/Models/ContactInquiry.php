<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A message from the website's contact form (see App\Support\ContactInbox). */
class ContactInquiry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'subject', 'message', 'status', 'starred', 'spam_score', 'ip', 'user_agent', 'user_id',
        'note', 'read_at', 'replied_at', 'replied_by', 'legacy_id',
    ];

    protected $casts = [
        'starred'    => 'boolean',
        'spam_score' => 'integer',
        'read_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function replies(): HasMany
    {
        return $this->hasMany(ContactInquiryReply::class, 'inquiry_id')->orderBy('id');
    }

    /** 017… for BD numbers, the stored text otherwise. */
    public function localPhone(): string
    {
        $p = \App\Support\PhoneNormalizer::toLocal($this->phone);

        return $p !== '' ? $p : (string) $this->phone;
    }

    /** wa.me link (digits only), or null. */
    public function whatsappUrl(): ?string
    {
        $d = \App\Support\PhoneNormalizer::toE164($this->phone);

        return strlen($d) >= 10 ? 'https://wa.me/' . $d : null;
    }
}
