<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationEmail extends Mailable
{

    use Queueable, SerializesModels;

    public string $url;
    public string $code;

    public function __construct($url, $code)
    {
        $this->url = $url;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('mail.confirm');
    }
}
