<?php

namespace App\Modules\Leads\Mail;

use App\Modules\Leads\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Lead $lead) {}

    public function envelope(): Envelope
    {
        $subject = $this->lead->subject ?: 'New website enquiry';

        return new Envelope(
            subject: '[M2026 Lead] '.$subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'leads::emails.new-lead',
            with: [
                'lead' => $this->lead,
                'sourceLabel' => config('leads.sources.'.$this->lead->source, $this->lead->source),
            ],
        );
    }

    /** @return array<int, mixed> */
    public function attachments(): array
    {
        return [];
    }
}
