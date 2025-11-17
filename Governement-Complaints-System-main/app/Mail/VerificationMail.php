<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code; 

    // Constructor لتمرير البيانات
    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('رمز التحقق')
                    ->view('emails.verification'); 
    }
}