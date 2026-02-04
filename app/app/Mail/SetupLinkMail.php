<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetupLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $setupUrl;

    public function __construct(string $setupUrl)
    {
        $this->setupUrl = $setupUrl;
    }

    public function build()
    {
        return $this
            ->from('support@burracoup.net', 'BurracoUP')
            ->replyTo('support@burracoup.net', 'BurracoUP')
            ->subject('Invito a BurracoUP – link di accesso')
            ->view('emails.setup_link')
            ->text('emails.setup_link_plain');
    }
}