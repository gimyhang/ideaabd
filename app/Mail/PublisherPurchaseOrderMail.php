<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Publisher\Models\Publisher;

class PublisherPurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Publisher $publisher,
        public array $orderData,
        public ?string $customMessage = null,
        public ?array $senderSettings = null
    ) {
        $this->senderSettings = $senderSettings ?? [
            'business_name' => 'আইডিয়া প্রকাশন (Idea Prakashon)',
            'address'       => 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ',
            'phone'         => '01558712870',
            'email'         => config('mail.from.address', 'info@ideaabd.com'),
        ];
    }

    public function envelope(): Envelope
    {
        $bizName = $this->senderSettings['business_name'] ?? 'আইডিয়া প্রকাশন';
        $poNumber = $this->orderData['po_number'] ?? 'PO-' . date('Ymd');
        $subject = !empty($this->orderData['subject']) 
            ? $this->orderData['subject'] 
            : "{$bizName} — ক্রয় আদেশ (Purchase Order) #{$poNumber}";

        $fromEmail = config('mail.from.address') ?: 'ad@ideaabd.com';
        $fromName = config('mail.from.name') ?: 'Idea Prokashon';
        $replyEmail = $this->senderSettings['email'] ?? $fromEmail;

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: $subject,
            replyTo: filter_var($replyEmail, FILTER_VALIDATE_EMAIL) ? [new Address($replyEmail, $bizName)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.publisher-purchase-order',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
