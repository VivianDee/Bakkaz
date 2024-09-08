<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $title;
    public $greeting;
    public $body;
    public $subject;


    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->title = $data['title'];
        $this->greeting = $data['greeting'];
        $this->body = $data['body'];
        $this->subject = $data['subject'];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.general',
            with: [
                'title' => $this->title,
                'name' => $this->name,
                'greeting' => $this->greeting,
                'body' => $this->body,
                'subject' => $this->subject,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
