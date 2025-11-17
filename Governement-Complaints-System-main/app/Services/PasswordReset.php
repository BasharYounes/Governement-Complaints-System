<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class PasswordReset 
{
    public function createPasswordToken(User $user,string $token)
    {
        PasswordResetToken::updateOrCreate(
            ['email' => $user->email],
            ['token' => $token]
        );
    }

    
}
