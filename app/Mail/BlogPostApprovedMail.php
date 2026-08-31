<?php

namespace App\Mail;

use Modules\Blog\Models\BlogPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BlogPostApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BlogPost $post,
        public ?User $author = null
    ) {
        if (!$this->author && $post->author_id) {
            $this->author = User::find($post->author_id);
        }
    }

    public function envelope(): Envelope
    {
        $fromEmail = config('mail.from.address') ?: 'ad@ideaabd.com';
        $fromName = config('mail.from.name') ?: 'Idea Prokashon';

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: 'আইডিয়া প্রকাশন — আপনার লেখা “' . $this->post->title . '” অনুমোদিত ও প্রকাশিত হয়েছে',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.blog-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
