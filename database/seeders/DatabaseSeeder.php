<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        $this->createAdminUser();

        // Optionally seed sample data for development
        if (app()->environment('local', 'development')) {
            $this->seedSampleData();
        }
    }

    /**
     * Create the default admin user.
     */
    private function createAdminUser(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ]
        );
    }

    /**
     * Seed sample data for development environment.
     */
    private function seedSampleData(): void
    {
        // Create a sample regular user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => UserRole::User,
            ]
        );
    }
}
