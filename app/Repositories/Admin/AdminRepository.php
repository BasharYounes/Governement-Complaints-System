<?php

namespace App\Repositories\Admin;

use App\Models\Admin;

class AdminRepository
{
    public function createAdmin(array $data): Admin
    {
        return Admin::create($data);
    }

    public function findByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->firstOrFail();
    }
}
