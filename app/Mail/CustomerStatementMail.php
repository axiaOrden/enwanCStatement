<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $statement, private readonly string $pdf) {}

    public function envelope(): Envelope
    {
        $name = $this->statement['customer']['customer_name'] ?: $this->statement['customer']['customer_code'];
        return new Envelope(subject: "Customer statement - {$name}");
    }

    public function content(): Content { return new Content(view: 'mail.customer-statement'); }

    public function attachments(): array
    {
        $customer = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->statement['customer']['customer_code']);
        $period = $this->statement['period'];
        return [Attachment::fromData(fn () => $this->pdf, "Statement_{$customer}_{$period['from']}_to_{$period['to']}.pdf")->withMime('application/pdf')];
    }
}
