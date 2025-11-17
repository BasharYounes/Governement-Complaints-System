<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;

class InvalidCodeException extends Exception
{
    use ApiResponse;

      public function render()
    {
        return $this->error('الكود غير صالح ',$this->getMessage(),500);
    }
}
