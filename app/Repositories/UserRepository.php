<?php

namespace App\Repositories;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Storage;

class UserRepository {
    use ApiResponse;


    public function findByEmail($email) {
        return User::where('email', $email)->firstOrFail();
    }

    public function update(User $user, $request) {
        // Accept either an array of attributes or a Request/FormRequest instance
        if (is_array($request)) {
            $data = $request;
        } else {
            $data = $request->only(['name', 'email', 'phone', 'email_verified_at', 'password']);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $filename = time() . '.' . $request->file('image')->extension();
                $path = Storage::disk('public')->putFileAs(
                    'users',
                    $request->file('image'),
                    $filename,
                    ['visibility' => 'public']
                );
                $data['image'] = $path;
            }
        }

        $user->update($data);

        return $user->fresh();
    }



    public function deleteUserToken($user)
    {
        if ($user->tokens()->exists()) {
            $user->tokens()->delete();
        }
    }

     public function createToken($user)
    {
        return $user->createToken('user_token', ['user'])->plainTextToken;
    }

    public function findById($id)
    {
        return User::findOrFail($id);
    }
}
