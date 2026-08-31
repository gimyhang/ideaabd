<?php

namespace App\Mail;

use App\Models\IdeaInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public IdeaInvoice $invoice,
        public ?string $customMessage = null,
        public ?array $invoiceSettings = null
    ) {
        $this->invoiceSettings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    }

    public function envelope(): Envelope
    {
        $bizName = $this->invoiceSettings['business_name'] ?? 'আইডিয়া প্রকাশন';
        $typeLabel = $this->invoice->type_label;
        $invNo = $this->invoice->invoice_no;
        $fromEmail = config('mail.from.address', 'ideapbd@gmail.com');
        $fromName = config('mail.from.name', 'Idea Prokashon');
        $replyToEmail = $this->invoiceSettings['email'] ?? $fromEmail;

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: "{$bizName} — {$typeLabel} #{$invNo}",
            replyTo: filter_var($replyToEmail, FILTER_VALIDATE_EMAIL) ? [new Address($replyToEmail, $bizName)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-invoice',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
