<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl,
        public int $expireMinutes = 30,
        public ?string $otpCode = null
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = config('mail.from.address') ?: 'ad@ideaabd.com';
        $fromName = config('mail.from.name') ?: 'Idea Prokashon';

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: 'আইডিয়া প্রকাশন — পাসওয়ার্ড রিসেট কোড ও ওয়ান-টাইম লিংক (মেয়াদ ' . $this->expireMinutes . ' মিনিট)',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
