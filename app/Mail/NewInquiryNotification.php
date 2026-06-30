<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewInquiryNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inquirerName;
    public $inquirerEmail;
    public $inquirerPhone;
    public $inquiryMessage;
    public $submittedAt;

    public function __construct($inquirerName, $inquirerEmail, $inquirerPhone, $inquiryMessage, $submittedAt)
    {
        $this->inquirerName = $inquirerName;
        $this->inquirerEmail = $inquirerEmail;
        $this->inquirerPhone = $inquirerPhone;
        $this->inquiryMessage = $inquiryMessage;
        $this->submittedAt = $submittedAt;
    }

    public function build()
    {
        return $this->subject('New Inquiry Received - AIM Website')
                    ->view('emails.new-inquiry-notification')
                    ->with([
                        'inquirerName' => $this->inquirerName,
                        'inquirerEmail' => $this->inquirerEmail,
                        'inquirerPhone' => $this->inquirerPhone,
                        'inquiryMessage' => $this->inquiryMessage,
                        'submittedAt' => $this->submittedAt,
                    ]);
    }
}
