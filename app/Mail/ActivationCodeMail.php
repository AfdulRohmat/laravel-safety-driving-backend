<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $activationCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($activationCode)
    {
        $this->activationCode = $activationCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.activation_code')
            ->with([
                'activationCode' => $this->activationCode,
            ]);
    }
}
