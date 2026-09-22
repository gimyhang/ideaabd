<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $otpCode,
        public int $expireMinutes = 2
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = config('mail.from.address') ?: 'ideapbd@gmail.com';
        $fromName = config('mail.from.name') ?: 'Idea Prokashon';

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: 'আইডিয়া প্রকাশন — আপনার অ্যাকাউন্ট ভেরিফিকেশন কোড: ' . $this->otpCode,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-verification-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
