<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangePassword extends Mailable
{
    use Queueable, SerializesModels;

    public $link; 

    // Constructor لتمرير البيانات
    public function __construct($link)
    {
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('رابط التأكيد')
                    ->view('emails.PasswordChanging'); 
    }
}