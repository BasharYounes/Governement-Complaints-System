<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;

class CodeSendingException extends Exception
{
    use ApiResponse;

    public function __construct(string $message = "'فشل في إرسال الكود'")
    {
        $this->message = $message;
    }

     public function render()
    {
        return $this->error($this->message,$this->getMessage(),500);
    }
}
