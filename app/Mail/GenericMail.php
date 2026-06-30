<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenericMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $name;
    public $content;
    public $buttonText;
    public $buttonUrl;
    public $unsubscribeUrl;

    public function __construct($name, $content, $buttonText, $buttonUrl, $unsubscribeUrl = null)
    {
        $this->name = $name;
        $this->content = $content;
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
        $this->unsubscribeUrl = $unsubscribeUrl;
    }

    public function build()
    {
        return $this->subject('AIM Notification')
                    ->view('emails.aim-email-template')
                    ->with([
                        'name' => $this->name,
                        'content' => $this->content,
                        'buttonText' => $this->buttonText,
                        'buttonUrl' => $this->buttonUrl,
                        'unsubscribeUrl' => $this->unsubscribeUrl,
                    ]);
    }
}
