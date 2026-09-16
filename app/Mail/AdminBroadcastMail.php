<?php

namespace App\Mail;

use App\Support\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $bodyContent,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public ?string $recipientName = null,
        public ?string $preheader = null
    ) {}

    public function envelope(): Envelope
    {
        $smtp = SiteSetting::get('smtp_settings', []);
        $fromEmail = !empty($smtp['from_address']) ? $smtp['from_address'] : (config('mail.from.address') ?: 'info@ideaabd.com');
        $fromName = !empty($smtp['from_name']) ? $smtp['from_name'] : (SiteSetting::name() ?: config('mail.from.name', 'আইডিয়া প্রকাশন'));

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-broadcast',
            with: [
                'mailSubject'   => $this->mailSubject,
                'bodyContent'   => $this->bodyContent,
                'actionText'    => $this->actionText,
                'actionUrl'     => $this->actionUrl,
                'recipientName' => $this->recipientName,
                'preheader'     => $this->preheader,
                'siteName'      => SiteSetting::name() ?: 'আইডিয়া প্রকাশন',
                'siteTagline'   => SiteSetting::tagline() ?: 'অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম',
                'siteUrl'       => url('/'),
            ]
        );
    }
}
