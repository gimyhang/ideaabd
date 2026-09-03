<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = config('mail.from.address') ?: 'ad@ideaabd.com';
        $fromName = config('mail.from.name') ?: 'Idea Prokashon';

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            replyTo: [new Address($fromEmail, $fromName)],
            subject: 'আইডিয়া প্রকাশন — আপনার অ্যাকাউন্ট সফলভাবে অনুমোদিত হয়েছে',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
