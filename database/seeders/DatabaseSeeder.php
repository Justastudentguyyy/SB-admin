<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminPassword = env('ADMIN_PASSWORD');

        if (! is_string($adminPassword) || $adminPassword === '') {
            throw new RuntimeException('Set ADMIN_PASSWORD before seeding the administrator account.');
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'email_verified_at' => now(),
            'status' => true,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'status' => true,
                'role' => 'admin',
            ],
        );
    }
}
