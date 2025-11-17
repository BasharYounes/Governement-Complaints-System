<?php

namespace App\Services;


use Cache;

class GenerateCode {
    public function generateCode($user) {
        $code = rand(100000, 999999);

        Cache::put(
            'verification_code_' . $user->email, 
            $code, 
            now()->addMinutes(10)
        );
        return $code;
    }

}