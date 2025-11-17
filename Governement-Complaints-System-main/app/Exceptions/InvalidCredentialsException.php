<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;

class InvalidCredentialsException extends Exception
{
    use ApiResponse;

      public function render()
    {
         return $this->error('بيانات غير صحيحة', null, 401);
    }
}
