<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;

class RegistrationFailedException extends Exception
{
    use ApiResponse;

     public function render()
    {
        return $this->error('فشل في التسجيل',$this->getMessage(),500);
    }
}
