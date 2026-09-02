<?php

namespace App\Mail;

use App\Models\IdeaInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $invoiceSettings;

    public function __construct(
        public IdeaInvoice $invoice,
        public ?string $customMessage = null,
        ?array $invoiceSettings = null
    ) {
        $resolved = $invoiceSettings;
        if (!is_array($resolved) || empty($resolved)) {
            $resolved = \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
        }
        $this->invoiceSettings = is_array($resolved) ? $resolved : [];
    }

    public function envelope(): Envelope
    {
        $settings = (is_array($this->invoiceSettings) && !empty($this->invoiceSettings))
            ? $this->invoiceSettings
            : \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();

        $bizName = $settings['business_name'] ?? 'আইডিয়া প্রকাশন';
        $typeLabel = $this->invoice->type_label ?? 'ইনভয়েস';
        $invNo = $this->invoice->invoice_no ?? '';
        $fromEmail = config('mail.from.address') ?: 'ad@ideaabd.com';
        $fromName = config('mail.from.name') ?: 'Idea Prokashon';
        $replyToEmail = !empty($settings['email']) ? $settings['email'] : config('mail.reply_to.address', 'ideapbd@gmail.com');

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: "{$bizName} — {$typeLabel} #{$invNo}",
            replyTo: filter_var($replyToEmail, FILTER_VALIDATE_EMAIL) ? [new Address($replyToEmail, $bizName)] : [new Address('ideapbd@gmail.com', $bizName)],
        );
    }

    public function content(): Content
    {
        $settings = (is_array($this->invoiceSettings) && !empty($this->invoiceSettings))
            ? $this->invoiceSettings
            : \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();

        return new Content(
            view: 'emails.customer-invoice',
            with: [
                'invoice' => $this->invoice,
                'customMessage' => $this->customMessage,
                'invoiceSettings' => $settings,
            ]
        );
    }

    public function attachments(): array
    {
        try {
            $settings = (is_array($this->invoiceSettings) && !empty($this->invoiceSettings))
                ? $this->invoiceSettings
                : \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();

            $pdf = Pdf::loadView('emails.invoice-pdf', [
                'invoice' => $this->invoice,
                'invoiceSettings' => $settings,
            ])->setPaper('a4', 'portrait');

            $filename = ($this->invoice->type ?: 'invoice') . '-' . ($this->invoice->invoice_no ?: time()) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not attach invoice PDF: " . $e->getMessage());
            return [];
        }
    }
}
