<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.super_admin.email');
        $password = config('app.super_admin.password');

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('SUPER_ADMIN_EMAIL must be set to a valid email address.');
        }

        if (!is_string($password) || $password === '') {
            throw new InvalidArgumentException('SUPER_ADMIN_PASSWORD must be set before seeding the super admin.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
        $this->command->info('Super Admin created.');
    }
}