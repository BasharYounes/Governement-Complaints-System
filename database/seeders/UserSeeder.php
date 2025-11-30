<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Employee;
use App\Models\GovernmentEntities;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::firstOrCreate(
            ['email' => 'batoulsubuh@gmail.com'],
            [
                'name' => 'Batoul',
                'password' => Hash::make('12341234'),
            ]
        );
        $admin->assignRole('super_admin');

        $firstEntity = GovernmentEntities::first();
        if($firstEntity) {
            $employee = Employee::firstOrCreate(
                ['email' => 'batoul1@gmail.com'],
                [
                    'name' => 'BatoulSB',
                    'password' => Hash::make('1234512345'),
                    'government_entity_id' => $firstEntity->id
                ]
            );
            $employee->assignRole('employee');
        }

        // Citizen User
        $citizen = User::create([
            'name' => 'Batoul Subuh',
            'email' => 'baraaalali553@gmail.com',
            'password' => Hash::make('123456789'),
            'government_entity_id' => null,
        ]);

        $citizen->assignRole('citizen');

    }
}
