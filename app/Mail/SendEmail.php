<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    private array $emailPayload;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $emailPayload)
    {
        $this->emailPayload = $emailPayload;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = $this->subject($this->emailPayload['subject'])
            ->replyTo($this->emailPayload['sender_email']);

        if (isset($this->emailPayload['text_content'])) {
            $message->html($this->emailPayload['text_content']);
        }

        if (isset($this->emailPayload['html_content'])) {
            $message->html($this->emailPayload['html_content']);
        }

        if (isset($this->emailPayload['uploaded_attachments'])) {
            foreach ($this->emailPayload['uploaded_attachments'] as $attachment) {
                $message->attach($attachment['link'], [
                    'as' => $attachment['name'],
                    'mime' => $attachment['type'],
                ]);
            }
        }

        return $message;
    }
}
