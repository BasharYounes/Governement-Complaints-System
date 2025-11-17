<?php

namespace App\Services;

use App\Models\User;
use Cache;

final class CasheService 
{
    public function getCodeFromCashe(User $user)
    {
       return Cache::get('verification_code_' . $user->email);
    }

    public function forgetCodeFromCashe(User $user)
    {
        Cache::forget('verification_code_' . $user->email);
    }

    
}
