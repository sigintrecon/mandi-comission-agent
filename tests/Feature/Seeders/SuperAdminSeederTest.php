<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_super_admin_using_configured_credentials(): void
    {
        config([
            'app.super_admin.email' => 'owner@example.test',
            'app.super_admin.password' => 'a-strong-test-password',
        ]);

        $this->seed(\Database\Seeders\SuperAdminSeeder::class);

        $user = User::where('email', 'owner@example.test')->firstOrFail();

        $this->assertSame('super_admin', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('a-strong-test-password', $user->password));
    }
}
