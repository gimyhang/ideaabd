<?php

namespace App\Mail;

use App\Models\IdeaInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
        $bizEmail = $this->invoiceSettings['email'] ?? config('mail.from.address', 'info@ideaabd.com');

        return new Envelope(
            subject: "{$bizName} — {$typeLabel} #{$invNo}",
            replyTo: filter_var($bizEmail, FILTER_VALIDATE_EMAIL) ? [$bizEmail] : [],
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
